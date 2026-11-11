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
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;
import java.util.TreeMap;



@GrpcService
public class InventoryGrpcService extends InventoryServiceGrpc.InventoryServiceImplBase {

    private final StockRepository stockRepository;
    private final ReservationRepository reservationRepository;

    public InventoryGrpcService(StockRepository stockRepository,
                                ReservationRepository reservationRepository) {
        this.stockRepository = stockRepository;
        this.reservationRepository = reservationRepository;
    }

    //      GetStock: get the available stock quantity for a product  
    @Override
    @Transactional(readOnly = true)
    public void getStock(GetStockRequest request, StreamObserver<GetStockReply> responseObserver) {
        Optional<Stock> stock = stockRepository.findByProductId(request.getProductId());

        GetStockReply.Builder reply = GetStockReply.newBuilder();
        if (stock.isPresent()) {
            reply.setFound(true);
            reply.setAvailable(stock.get().getQuantity());
        } else {
            reply.setFound(false);
            reply.setAvailable(0);
        }

        send(responseObserver, reply.build());
    }

    //      Reserve: reserve quantities for an order (all or nothing)
    @Override
    @Transactional //all or nothing
    public void reserve(ReserveRequest request, StreamObserver<ReserveReply> responseObserver) {

        String orderRef = request.getOrderRef().trim();
        if (orderRef.isEmpty()) {
            send(responseObserver, reserveFailure("order_ref is required", new ArrayList<>()));
            return;
        }
        if (request.getItemsList().isEmpty()) {
            send(responseObserver, reserveFailure("No items to reserve", new ArrayList<>()));
            return;
        }

        // 2) Idempotency: if the same order_ref is sent again, we check if it was already reserved or released.
        List<Reservation> existing = reservationRepository.findByOrderRefOrderByProductIdAsc(orderRef);
        if (!existing.isEmpty()) {
            boolean allActive = true;
            for (Reservation r : existing) {
                if (r.getStatus() != Reservation.Status.ACTIVE) {
                    allActive = false;
                }
            }

            ReserveReply.Builder reply = ReserveReply.newBuilder();
            reply.setSuccess(allActive);
            if (allActive) {
                reply.setMessage("Reservation already exists");
            } else {
                reply.setMessage("Reservation was already released");
            }
            send(responseObserver, reply.build());
            return;
        }

        //   if customers send the same order_ref again, we will check if it was already reserved or released.
        Map<Long, Integer> requested = new TreeMap<>();
        List<FailedItem> invalidItems = new ArrayList<>();

        for (StockItem item : request.getItemsList()) {
            if (item.getQuantity() <= 0) {
                invalidItems.add(buildFailedItem(item.getProductId(), item.getQuantity(), 0, "INVALID_QUANTITY"));
            } else if (requested.containsKey(item.getProductId())) {
                int oldQuantity = requested.get(item.getProductId());
                requested.put(item.getProductId(), Math.addExact(oldQuantity, item.getQuantity()));
            } else {
                requested.put(item.getProductId(), item.getQuantity());
            }
        }

        if (!invalidItems.isEmpty()) {
            send(responseObserver, reserveFailure("Invalid quantities", invalidItems));
            return;
        }

        // lock all the products we want to reserve (pessimistic lock)
        List<Long> productIds = new ArrayList<>(requested.keySet());
        List<Stock> lockedStocks = stockRepository.findByProductIdInForUpdate(productIds);

        Map<Long, Stock> stockById = new HashMap<>();
        for (Stock stock : lockedStocks) {
            stockById.put(stock.getProductId(), stock);
        }

        //  Check if all requested quantities are available.. all or nothing.
        List<FailedItem> failedItems = new ArrayList<>();
        for (Long productId : productIds) {
            int wanted = requested.get(productId);
            Stock stock = stockById.get(productId);

            if (stock == null) {
                failedItems.add(buildFailedItem(productId, wanted, 0, "UNKNOWN_PRODUCT"));
            } else if (stock.getQuantity() < wanted) {
                failedItems.add(buildFailedItem(productId, wanted, stock.getQuantity(), "INSUFFICIENT_STOCK"));
            }
        }

        if (!failedItems.isEmpty()) {
            send(responseObserver, reserveFailure("Reservation rejected", failedItems));
            return;
        }

        for (Long productId : productIds) {
            int wanted = requested.get(productId);
            Stock stock = stockById.get(productId);

            stock.setQuantity(stock.getQuantity() - wanted);
            reservationRepository.save(new Reservation(orderRef, productId, wanted));
        }

        ReserveReply ok = ReserveReply.newBuilder()
                .setSuccess(true)
                .setMessage("Reserved")
                .build();
        send(responseObserver, ok);
    }

    //      Release: release reserved quantities for an order
    @Override
    @Transactional
    public void release(ReleaseRequest request, StreamObserver<ReleaseReply> responseObserver) {
        String orderRef = request.getOrderRef().trim();
        if (orderRef.isEmpty()) {
            send(responseObserver, releaseReply(false, "order_ref is required"));
            return;
        }

        List<Reservation> reservations = reservationRepository.findByOrderRefOrderByProductIdAsc(orderRef);
        if (reservations.isEmpty()) {
            send(responseObserver, releaseReply(false, "Reservation not found"));
            return;
        }

        List<Reservation> activeReservations = new ArrayList<>();
        for (Reservation r : reservations) {
            if (r.getStatus() == Reservation.Status.ACTIVE) {
                activeReservations.add(r);
            }
        }

        if (activeReservations.isEmpty()) {
            send(responseObserver, releaseReply(true, "Already released"));
            return;
        }

        for (Reservation reservation : activeReservations) {
            // lock the stock row for update to prevent concurrent modifications
            Stock stock = stockRepository.findByProductIdForUpdate(reservation.getProductId()).orElse(null);
            if (stock == null) {
                throw new IllegalStateException("Missing stock for product " + reservation.getProductId());
            }

            stock.setQuantity(stock.getQuantity() + reservation.getQuantity());
            reservation.setStatus(Reservation.Status.RELEASED);
        }

        send(responseObserver, releaseReply(true, "Released"));
    }

    //      SetStock: set the stock quantity for a product (create or update)
    @Override
    @Transactional
    public void setStock(SetStockRequest request, StreamObserver<SetStockReply> responseObserver) {
        if (request.getQuantity() < 0) {
            send(responseObserver, SetStockReply.newBuilder().setSuccess(false).build());
            return;
        }

        //  lock the stock row for update to prevent concurrent modifications
        Stock stock = stockRepository.findByProductIdForUpdate(request.getProductId()).orElse(null);

        if (stock == null) {
            stockRepository.save(new Stock(request.getProductId(), request.getQuantity()));
        } else {
            stock.setQuantity(request.getQuantity());
        }

        send(responseObserver, SetStockReply.newBuilder().setSuccess(true).build());
    }


    // Sending the response to the client and completing the call (used with all types)
    private static <T> void send(StreamObserver<T> responseObserver, T reply) {
        responseObserver.onNext(reply);
        responseObserver.onCompleted();
    }

    private static FailedItem buildFailedItem(long productId, int requested, int available, String reason) {
        return FailedItem.newBuilder()
                .setProductId(productId)
                .setRequested(requested)
                .setAvailable(available)
                .setReason(reason)
                .build();
    }

    private static ReserveReply reserveFailure(String message, List<FailedItem> failedItems) {
        return ReserveReply.newBuilder()
                .setSuccess(false)
                .setMessage(message)
                .addAllFailedItems(failedItems)
                .build();
    }

    private static ReleaseReply releaseReply(boolean success, String message) {
        return ReleaseReply.newBuilder()
                .setSuccess(success)
                .setMessage(message)
                .build();
    }
}