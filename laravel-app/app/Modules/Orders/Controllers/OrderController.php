<?php

namespace App\Modules\Orders\Controllers;

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Products\Models\Product;
use App\Services\InventoryGrpcClient;
use App\Support\ApiErrors;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(private InventoryGrpcClient $inventory)
    {
    }

    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function show(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->with('items.product')
            ->find($id);

        if (!$order) {
            return ApiErrors::json(404, 'NOT_FOUND', 'Order not found');
        }

        return response()->json($order);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $orderRef = (string) Str::uuid();
        $reserveItems = [];
        $lines = [];
        $total = 0;

        foreach ($data['items'] as $row) {
            $product = Product::findOrFail($row['product_id']);
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

        // 1) حجز المخزون أولًا
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

        // 2) حفظ الطلب
        try {
            $order = DB::transaction(function () use ($request, $orderRef, $total, $lines) {
                $order = Order::create([
                    'user_id'      => $request->user()->id,
                    'order_ref'    => $orderRef,
                    'total_amount' => $total,
                    'status'       => 'pending',
                ]);

                foreach ($lines as $line) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $line['product_id'],
                        'quantity'   => $line['quantity'],
                        'price'      => $line['price'],
                    ]);
                }

                return $order->load('items.product');
            });
        } catch (\Throwable $e) {
            // 3) تعويض: تحرير الحجز
            try {
                $this->inventory->release($orderRef);
            } catch (\Throwable $ignored) {
            }

            return ApiErrors::json(500, 'INTERNAL_ERROR', 'Failed to save order, stock released');
        }

        return response()->json([
            'message' => 'Order placed successfully',
            'order'   => $order,
        ], 201);
    }

    public function cancel(Request $request, $id)
    {
        $user = $request->user();

        $order = $user->isAdmin()
            ? Order::find($id)
            : $user->orders()->find($id);

        if (!$order) {
            return ApiErrors::json(404, 'NOT_FOUND', 'Order not found');
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

        return response()->json(['message' => 'Order cancelled and stock restored']);
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $order = Order::find($id);
        if (!$order) {
            return ApiErrors::json(404, 'NOT_FOUND', 'Order not found');
        }

        if ($data['status'] === 'cancelled' && $order->status !== 'cancelled') {
            try {
                $this->inventory->release($order->order_ref);
            } catch (\Throwable $e) {
                return ApiErrors::json(503, 'INVENTORY_UNAVAILABLE', 'Failed to release stock');
            }
        }

        $order->update(['status' => $data['status']]);

        return response()->json([
            'message' => 'Status updated',
            'order'   => $order->fresh('items.product'),
        ]);
    }
}