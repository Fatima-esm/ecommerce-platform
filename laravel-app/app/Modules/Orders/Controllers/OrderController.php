<?php

namespace App\Modules\Orders\Controllers;

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Products\Services\ProductCatalog;
use App\Services\InventoryGrpcClient;
use App\Support\ApiErrors;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Event;

class OrderController extends Controller
{
    public function __construct(
        private InventoryGrpcClient $inventory,
        private ProductCatalog $catalog,
    ) {
    }

    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return ApiResponse::success($orders, 'Orders retrieved successfully');
    }

    public function adminIndex(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return ApiErrors::json(403, 'FORBIDDEN', 'Unauthorized action.');
        }

        $query = Order::with(['user', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20);

        return ApiResponse::success($orders, 'All orders retrieved successfully');
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::with('items.product')->find($id);

        if (!$order) {
            return ApiErrors::json(404, 'NOT_FOUND', 'Order not found');
        }

        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return ApiErrors::json(403, 'FORBIDDEN', 'Unauthorized to view this order');
        }

        return ApiResponse::success($order, 'Order retrieved successfully');
    }

    public function store(Request $request)
    {
        // ملاحظة أداء: أزلنا قاعدة exists:products,id لأنها تنفّذ استعلاماً لكل صنف (N+1)،
        // وبدّلناها باستعلام واحد whereIn عبر وحدة المنتجات.
        $data = $request->validate([
            'items'              => 'required|array|min:1|max:100',
            'items.*.product_id' => 'required|integer|min:1',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $ids = collect($data['items'])->pluck('product_id')->unique()->values()->all();
        $products = $this->catalog->findManyForOrder($ids);

        $missing = array_values(array_diff($ids, $products->keys()->all()));
        if ($missing) {
            return ApiErrors::json(422, 'VALIDATION_FAILED', 'The given data was invalid.', [
                'items' => ['One or more products do not exist.'],
                'missing_product_ids' => $missing,
            ]);
        }

        $orderRef = (string) Str::uuid();
        $reserveItems = [];
        $lines = [];
        $total = 0;

        foreach ($data['items'] as $row) {
            $product = $products[(int) $row['product_id']];
            $qty = (int) $row['quantity'];
            $total += $product->price * $qty;

            $reserveItems[] = [
                'product_id' => (int) $product->id,
                'quantity'   => $qty,
            ];

            $lines[] = [
                'product_id' => $product->id,
                'quantity'   => $qty,
                'price'      => $product->price,
            ];
        }

        try {
            $reserve = $this->inventory->reserve($orderRef, $reserveItems);
        } catch (\Throwable $e) {
            return ApiErrors::json(503, 'INVENTORY_UNAVAILABLE', 'Inventory service unavailable');
        }

        if (empty($reserve['success'])) {
            return ApiErrors::json(
                409, 
                'INSUFFICIENT_STOCK', 
                $reserve['message'] ?? 'Insufficient stock', 
                $reserve['failed_items'] ?? []
            );
        }

        try {
            $order = DB::transaction(function () use ($request, $orderRef, $total, $lines) {
                $order = Order::create([
                    'user_id'      => $request->user()->id,
                    'order_ref'    => $orderRef,
                    'total_amount' => $total,
                    'status'       => 'pending',
                ]);

                // إدراج واحد لكل الأصناف بدل إدراج لكل صنف
                $now = now();
                OrderItem::insert(array_map(fn ($line) => [
                    'order_id'   => $order->id,
                    'product_id' => $line['product_id'],
                    'quantity'   => $line['quantity'],
                    'price'      => $line['price'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $lines));

                return $order->load('items.product');
            });
        } catch (\Throwable $e) {
            try {
                $this->inventory->release($orderRef);
            } catch (\Throwable $ignored) {
            }

            return ApiErrors::json(500, 'INTERNAL_ERROR', 'Failed to save order, stock released');
        }
        Event::dispatch(new OrderCreated(
            userId: (int) $order->user_id,
            orderId: (int) $order->id,
            totalAmount: (float) $order->total_amount,
        ));

        return ApiResponse::success($order, 'Order placed successfully', 201);
    }

    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::find($id);

        if (!$order) {
            return ApiErrors::json(404, 'NOT_FOUND', 'Order not found');
        }

        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return ApiErrors::json(403, 'FORBIDDEN', 'Unauthorized to cancel this order');
        }

        if ($order->status === 'cancelled') {
            return ApiErrors::json(409, 'CONFLICT', 'Already cancelled');
        }

        try {
            $this->inventory->release($order->order_ref);
        } catch (\Throwable $e) {
            return ApiErrors::json(503, 'INVENTORY_UNAVAILABLE', 'Failed to release stock');
        }

        $order->update(['status' => 'cancelled']);

        return ApiResponse::success($order, 'Order cancelled and stock restored');
    }

    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();

        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $order = Order::find($id);
        if (!$order) {
            return ApiErrors::json(404, 'NOT_FOUND', 'Order not found');
        }

        if (!$user->isAdmin()) {
            return ApiErrors::json(403, 'FORBIDDEN', 'Only admin can change order status');
        }
        $currentStatus = $order->status;
        $newStatus = $data['status'];

        $allowedTransitions = [
            'pending'   => ['confirmed', 'cancelled'],
            'confirmed' => ['cancelled'],
            'cancelled' => [], 
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            return ApiErrors::json(
                422, 
                'INVALID_TRANSITION', 
                "Cannot transition order status from '{$currentStatus}' to '{$newStatus}'"
            );
        }

        if ($newStatus === 'cancelled' && $currentStatus !== 'cancelled') {
            try {
                $this->inventory->release($order->order_ref);
            } catch (\Throwable $e) {
                return ApiErrors::json(503, 'INVENTORY_UNAVAILABLE', 'Failed to release stock');
            }
        }

        $order->update(['status' => $newStatus]);

        Event::dispatch(new OrderStatusChanged(
            userId: (int) $order->user_id,
            orderId: (int) $order->id,
            from: $currentStatus,
            to: $newStatus,
        ));

        return ApiResponse::success($order->fresh('items.product'), 'Status updated successfully');
    }
    }