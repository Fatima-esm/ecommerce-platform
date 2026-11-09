package com.ecommerce.inventory.service;

import com.ecommerce.inventory.entity.Reservation;
import com.ecommerce.inventory.entity.Stock;
import com.ecommerce.inventory.grpc.FailedItem;
import com.ecommerce.inventory.grpc.GetStockReply;
import com.ecommerce.inventory.grpc.GetStockRequest;
import com.ecommerce.inventory.grpc.InventoryServiceGrpc;
import com.ecommerce.inventory.grpc.ReleaseReply;
import com.ecommerce.inventory.grpc.ReleaseRequest;
import com.ecommerce.inventory.grpc.ReserveReply;
import com.ecommerce.inventory.grpc.ReserveRequest;
import com.ecommerce.inventory.grpc.SetStockReply;
import com.ecommerce.inventory.grpc.SetStockRequest;
import com.ecommerce.inventory.grpc.StockItem;
import com.ecommerce.inventory.repository.ReservationRepository;
import com.ecommerce.inventory.repository.StockRepository;
import io.grpc.stub.StreamObserver;
import net.devh.boot.grpc.server.service.GrpcService;
import org.springframework.transaction.annotation.Transactional;

import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;

@GrpcService
public class InventoryGrpcService extends InventoryServiceGrpc.InventoryServiceImplBase {

    private final StockRepository stockRepository;
    private final ReservationRepository reservationRepository;

    public InventoryGrpcService(StockRepository stockRepository,
                                ReservationRepository reservationRepository) {
        this.stockRepository = stockRepository;
        this.reservationRepository = reservationRepository;
    }

    // ==================== GetStock ====================
    @Override
    @Transactional(readOnly = true)
    public void getStock(GetStockRequest request, StreamObserver<GetStockReply> responseObserver) {
        Optional<Stock> stock = stockRepository.findByProductId(request.getProductId());

        GetStockReply reply = stock
                .map(s -> GetStockReply.newBuilder()
                        .setFound(true)
                        .setAvailable(s.getQuantity())
                        .build())
                .orElseGet(() -> GetStockReply.newBuilder()
                        .setFound(false)
                        .setAvailable(0)
                        .build());

        responseObserver.onNext(reply);
        responseObserver.onCompleted();
    }

    // ==================== Reserve ====================
    @Override
    @Transactional
    public void reserve(ReserveRequest request, StreamObserver<ReserveReply> responseObserver) {
        String orderRef = request.getOrderRef().trim();
        if (orderRef.isEmpty()) {
            complete(responseObserver, failure("order_ref is required"));
            return;
        }

        // Idempotency: إذا كان الحجز موجود مسبقاً
        List<Reservation> existing = reservationRepository.findByOrderRefOrderByProductIdAsc(orderRef);
        if (!existing.isEmpty()) {
            boolean active = existing.stream()
                    .allMatch(r -> r.getStatus() == Reservation.Status.ACTIVE);

            complete(responseObserver, ReserveReply.newBuilder()
                    .setSuccess(active)
                    .setMessage(active ? "Reservation already exists" : "Reservation was already released")
                    .build());
            return;
        }

        // تجميع الكميات المطلوبة
        Map<Long, Integer> requested = new LinkedHashMap<>();
        List<FailedItem> invalid = new ArrayList<>();

        for (StockItem item : request.getItemsList()) {
            if (item.getQuantity() <= 0) {
                invalid.add(failedItem(item.getProductId(), item.getQuantity(), 0, "INVALID_QUANTITY"));
                continue;
            }
            requested.merge(item.getProductId(), item.getQuantity(), Math::addExact);
        }

        if (requested.isEmpty()) {
            complete(responseObserver, failure("No items to reserve", invalid));
            return;
        }
        if (!invalid.isEmpty()) {
            complete(responseObserver, failure("Invalid quantities", invalid));
            return;
        }

        // ★ أخذ القفل التشاؤمي على كل المنتجات (مرتبة لمنع Deadlock)
        List<Long> productIds = requested.keySet().stream().sorted().toList();
        Map<Long, Stock> stocks = new LinkedHashMap<>();
        for (Stock stock : stockRepository.findByProductIdInForUpdate(productIds)) {
            stocks.put(stock.getProductId(), stock);
        }

        // التحقق من الكميات وأثناء القفل
        List<FailedItem> failed = new ArrayList<>();
        for (Long productId : productIds) {
            int quantity = requested.get(productId);
            Stock stock = stocks.get(productId);

            if (stock == null) {
                failed.add(failedItem(productId, quantity, 0, "UNKNOWN_PRODUCT"));
            } else if (stock.getQuantity() < quantity) {
                failed.add(failedItem(productId, quantity, stock.getQuantity(), "INSUFFICIENT_STOCK"));
            }
        }

        if (!failed.isEmpty()) {
            complete(responseObserver, failure("Reservation rejected", failed));
            return;
        }

        // الخصم + تسجيل الحجز (القفل لا يزال مأخوذاً)
        for (Long productId : productIds) {
            int quantity = requested.get(productId);
            Stock stock = stocks.get(productId);
            stock.setQuantity(stock.getQuantity() - quantity);
            reservationRepository.save(new Reservation(orderRef, productId, quantity));
        }

        complete(responseObserver, ReserveReply.newBuilder()
                .setSuccess(true)
                .setMessage("Reserved")
                .build());
    }

    // ==================== Release ====================
    @Override
    @Transactional
    public void release(ReleaseRequest request, StreamObserver<ReleaseReply> responseObserver) {
        String orderRef = request.getOrderRef().trim();
        if (orderRef.isEmpty()) {
            complete(responseObserver, ReleaseReply.newBuilder()
                    .setSuccess(false)
                    .setMessage("order_ref is required")
                    .build());
            return;
        }

        List<Reservation> reservations =
                reservationRepository.findByOrderRefOrderByProductIdAsc(orderRef);

        if (reservations.isEmpty()) {
            complete(responseObserver, ReleaseReply.newBuilder()
                    .setSuccess(false)
                    .setMessage("Reservation not found")
                    .build());
            return;
        }

        List<Reservation> active = reservations.stream()
                .filter(r -> r.getStatus() == Reservation.Status.ACTIVE)
                .toList();

        if (active.isEmpty()) {
            complete(responseObserver, ReleaseReply.newBuilder()
                    .setSuccess(true)
                    .setMessage("Already released")
                    .build());
            return;
        }

        // إعادة الكميات مع قفل تشاؤمي
        for (Reservation reservation : active) {
            Stock stock = stockRepository.findByProductIdForUpdate(reservation.getProductId())
                    .orElseThrow(() -> new IllegalStateException(
                            "Missing stock for product " + reservation.getProductId()));

            stock.setQuantity(Math.addExact(stock.getQuantity(), reservation.getQuantity()));
            reservation.setStatus(Reservation.Status.RELEASED);
        }

        complete(responseObserver, ReleaseReply.newBuilder()
                .setSuccess(true)
                .setMessage("Released")
                .build());
    }

    // ==================== SetStock ====================
    @Override
    @Transactional
    public void setStock(SetStockRequest request, StreamObserver<SetStockReply> responseObserver) {
        if (request.getQuantity() < 0) {
            complete(responseObserver, SetStockReply.newBuilder().setSuccess(false).build());
            return;
        }

        Stock stock = stockRepository.findByProductIdForUpdate(request.getProductId())
                .orElseGet(() -> stockRepository.save(
                        new Stock(request.getProductId(), request.getQuantity())));

        stock.setQuantity(request.getQuantity());

        complete(responseObserver, SetStockReply.newBuilder().setSuccess(true).build());
    }

    // ==================== Helpers ====================
    private static FailedItem failedItem(long productId, int requested, int available, String reason) {
        return FailedItem.newBuilder()
                .setProductId(productId)
                .setRequested(requested)
                .setAvailable(available)
                .setReason(reason)
                .build();
    }

    private static ReserveReply failure(String message) {
        return failure(message, List.of());
    }

    private static ReserveReply failure(String message, List<FailedItem> failedItems) {
        return ReserveReply.newBuilder()
                .setSuccess(false)
                .setMessage(message)
                .addAllFailedItems(failedItems)
                .build();
    }

    private static <T> void complete(StreamObserver<T> responseObserver, T reply) {
        responseObserver.onNext(reply);
        responseObserver.onCompleted();
    }
}