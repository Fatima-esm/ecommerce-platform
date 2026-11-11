<?php

namespace Database\Seeders;

use App\Modules\Products\Models\Product;
use App\Services\InventoryGrpcClient;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name'        => 'Wireless Bluetooth Headphones',
                'description' => 'Over-ear headphones with noise cancellation and 30-hour battery life.',
                'price'       => 79.99,
                'category'    => 'electronics',
                'stock'       => 50,
            ],
            [
                'name'        => 'Mechanical Gaming Keyboard',
                'description' => 'RGB backlit mechanical keyboard with blue switches.',
                'price'       => 119.50,
                'category'    => 'electronics',
                'stock'       => 30,
            ],
            [
                'name'        => 'Stainless Steel Water Bottle',
                'description' => '1 liter insulated bottle, keeps drinks cold for 24 hours.',
                'price'       => 24.99,
                'category'    => 'home',
                'stock'       => 100,
            ],
            [
                'name'        => 'Organic Cotton T-Shirt',
                'description' => 'Unisex soft cotton t-shirt available in multiple sizes.',
                'price'       => 19.90,
                'category'    => 'clothing',
                'stock'       => 80,
            ],
            [
                'name'        => 'LED Desk Lamp',
                'description' => 'Adjustable LED lamp with 3 brightness levels and USB charging port.',
                'price'       => 34.00,
                'category'    => 'home',
                'stock'       => 45,
            ],
        ];

        $inventory = app(InventoryGrpcClient::class);

        foreach ($products as $data) {
            $stock = $data['stock'];
            unset($data['stock']);

            $product = Product::create($data);

            // ضبط الكمية في خدمة المستودع
            try {
                $inventory->setStock($product->id, $stock);
            } catch (\Throwable $e) {
                $this->command?->warn("Product {$product->id} created but stock sync failed");
            }
        }

        $this->command?->info('5 products seeded successfully.');
    }
}