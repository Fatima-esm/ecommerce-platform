<?php

namespace App\Modules\Products\Controllers;

use App\Modules\Products\Models\Product;
use App\Support\ApiErrors;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // عرض المنتجات مع Pagination + Filter
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->paginate(20);

        return response()->json($products);
    }

    // عرض منتج واحد
    public function show(Product $product)
    {
        $payload = $product->toArray();

        try {
            $stock = app(\App\Services\InventoryGrpcClient::class)
                ->getStock($product->id);

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

        return response()->json(['message' => 'Product deleted successfully']);
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