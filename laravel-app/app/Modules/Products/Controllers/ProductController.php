<?php

namespace App\Modules\Products\Controllers;

use App\Modules\Products\Models\Product;
use App\Modules\Products\Services\ProductCache;
use App\Support\ApiErrors;
use App\Support\ReadYourWrites;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct(private ProductCache $cache)
    {
    }

    // عرض المنتجات مع Pagination + Filter  (عام، مخزَّن في Redis)
    public function index(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $filters = $request->only(['category', 'search']);
        $key = $this->cache->listKey($filters, $page);

        // بعد تعديل حديث نقرأ من الأساسية كي لا نخزّن بيانات قديمة من النسخة المتأخرة
        $primary = ReadYourWrites::productsRecentlyWritten();

        $payload = $this->cache->remember(
            $key,
            (int) config('performance.list_ttl'),
            function () use ($request, $primary) {
                $query = Product::query();

                if ($primary) {
                    $query->useWritePdo();
                }

                if ($request->filled('category')) {
                    $query->where('category', $request->category);
                }

                if ($request->filled('search')) {
                    // ILIKE + فهرس trigram؛ نهرّب % و _ حتى لا يعاملها كرموز بحث
                    $term = addcslashes((string) $request->search, '\\%_');
                    $query->where('name', 'ilike', '%' . $term . '%');
                }

                // id كمعيار ثانٍ يجعل ترتيب الصفحات ثابتاً عند تساوي created_at
                return $query->latest()->orderByDesc('id')->paginate(20)->toArray();
            },
            $state
        );

        $request->attributes->set('x_cache', $state);

        return response()->json($payload);
    }

    // عرض منتج واحد: بيانات المنتج من Redis، والكمية حيّة من المستودع دائماً
    public function show(Request $request, int $product)
    {
        $primary = ReadYourWrites::productsRecentlyWritten();

        $payload = $this->cache->remember(
            $this->cache->detailKey($product),
            (int) config('performance.product_ttl'),
            function () use ($product, $primary) {
                $query = Product::query();
                if ($primary) {
                    $query->useWritePdo();
                }

                return $query->find($product)?->toArray();
            },
            $state
        );

        $request->attributes->set('x_cache', $state);

        if ($payload === null) {
            return ApiErrors::json(404, 'NOT_FOUND', 'Product not found');
        }

        try {
            $stock = app(\App\Services\InventoryGrpcClient::class)
                ->getStock($payload['id']);

            $payload['stock'] = $stock['found'] ?? false
                ? ($stock['available'] ?? 0)
                : 0;
            $payload['stockStatus'] = 'OK';
        } catch (\Throwable $e) {
            $payload['stock'] = null;
            $payload['stockStatus'] = 'UNKNOWN';
        }

        return response()->json($payload);    
    }

    // إضافة منتج مع صورة (Admin فقط)
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'category'    => 'nullable|string|max:100',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'stock'       => 'nullable|integer|min:0', // المخزون الابتدائي
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        unset($data['image']);

        $product = Product::create($data);
        $this->cache->afterWrite($product->id);

        $initialStock = (int) $request->input('stock', 0);

        if ($initialStock > 0) {
            try {
                app(\App\Services\InventoryGrpcClient::class)
                    ->setStock($product->id, $initialStock);
            } catch (\Throwable $e) {
                return ApiErrors::json(201, 'STOCK_SYNC_FAILED', 'Product created but stock sync failed', ['product' => $product]);
            }
        }    

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }

    // تحديث منتج (Admin)
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'category'    => 'nullable|string|max:100',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        unset($data['image']);
        $product->update($data);
        $this->cache->afterWrite($product->id);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->fresh(),
        ]);
    }

    // حذف منتج (Admin)
    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();
        $this->cache->afterWrite($product->id);

        return response()->json(['message' => 'Product deleted successfully']);
    }

    // تعديل الكمية في المستودع (Admin) — إدارة المخزون عبر خدمة المستودع
    public function updateStock(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        try {
            $reply = app(\App\Services\InventoryGrpcClient::class)
                ->setStock($product->id, (int) $data['quantity']);
        } catch (\Throwable $e) {
            return ApiErrors::json(503, 'INVENTORY_UNAVAILABLE', 'Inventory service unavailable');
        }

        if (empty($reply['success'])) {
            return ApiErrors::json(422, 'VALIDATION_FAILED', 'Stock update rejected');
        }

        return response()->json([
            'message' => 'Stock updated successfully',
            'product_id' => $product->id,
            'stock' => (int) $data['quantity'],
        ]);
    }

    public function image(Product $product)
    {
        if (!$product->image_path || !Storage::disk('public')->exists($product->image_path)) {
            return ApiErrors::json(404, 'NOT_FOUND', 'Product image not found');
        }

        $path = Storage::disk('public')->path($product->image_path);
        
        return response()->file($path);
    }
}