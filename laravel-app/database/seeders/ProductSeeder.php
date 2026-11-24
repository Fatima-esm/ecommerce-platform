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
                'image_path'   => 'assets/images/a1.png',

            ],
            [
                'name'        => 'Mechanical Gaming Keyboard',
                'description' => 'RGB backlit mechanical keyboard with blue switches.',
                'price'       => 119.50,
                'category'    => 'electronics',
                'stock'       => 30,
                'image_path'   => 'assets/images/c4.png',
            ],
            [
                'name'        => 'Stainless Steel fridge',
                'description' => '1 liter insulated bottle, keeps drinks cold for 24 hours.',
                'price'       => 24.99,
                'category'    => 'home',
                'stock'       => 100,
                'image_path'   => 'assets/images/pr6.png',
            ],
            [
                'name'        => 'Organic fiber bath towel',
                'description' => 'Unisex soft cotton t-shirt available in multiple sizes.',
                'price'       => 19.90,
                'category'    => 'clothing',
                'stock'       => 80,
                'image_path'   => 'assets/images/pr4.png',
            ],
            [
                'name'        => 'LED Desk Lamp',
                'description' => 'Adjustable LED lamp with 3 brightness levels and USB charging port.',
                'price'       => 34.00,
                'category'    => 'home',
                'stock'       => 45,
                'image_path'   => 'assets/images/pr5.png',
            ],
            [
                'name'        => 'Product 1',
                'description' => 'Description for product 1',
                'price'       => 79.99,
                'category'    => 'electronics',
                'stock'       => 50,
                'image_path'   => 'assets/images/pr1.png',
            ],
            [
                'name'        => 'Product 2',
                'description' => 'Description for product 2',
                'price'       => 119.50,
                'category'    => 'electronics',
                'stock'       => 30,
                'image_path'   => 'assets/images/pr2.png',
            ],
            [
                'name'        => 'Product 3',
                'description' => 'Description for product 3',
                'price'       => 24.99,
                'category'    => 'home',
                'stock'       => 100,
                'image_path'   => 'assets/images/pr3.png',
            ],
            [
                'name'        => 'Product 4',
                'description' => 'Description for product 4',
                'price'       => 19.90,
                'category'    => 'clothing',
                'stock'       => 80,
                'image_path'   => 'assets/images/pr4.png',
            ],
            [
                'name'        => 'Product 5',
                'description' => 'Description for product 5',
                'price'       => 34.00,
                'category'    => 'home',
                'stock'       => 45,
                'image_path'   => 'assets/images/pr5.png',
            ],
            [
                'name'        => 'Product 6',
                'description' => 'Description for product 6',
                'price'       => 49.99,
                'category'    => 'electronics',
                'stock'       => 60,
                'image_path'   => 'assets/images/pr6.png',
            ],
            [
                'name'        => 'Product 7',
                'description' => 'Description for product 7',
                'price'       => 89.00,
                'category'    => 'electronics',
                'stock'       => 25,
                'image_path'   => 'assets/images/pr7.png',
            ],
            [
                'name'        => 'Product 8',
                'description' => 'Description for product 8',
                'price'       => 15.50,
                'category'    => 'home',
                'stock'       => 120,
                'image_path'   => 'assets/images/pr8.png',
            ],
            [
                'name'        => 'Product 9',
                'description' => 'Description for product 9',
                'price'       => 29.99,
                'category'    => 'clothing',
                'stock'       => 70,
                'image_path'   => 'assets/images/pr9.png',
            ],
            [
                'name'        => 'Product 10',
                'description' => 'Description for product 10',
                'price'       => 59.99,
                'category'    => 'home',
                'stock'       => 40,
                'image_path'   => 'assets/images/pr10.png',
            ],
            [
                'name'        => 'Product 11',
                'description' => 'Description for product 11',
                'price'       => 129.99,
                'category'    => 'electronics',
                'stock'       => 20,
                'image_path'   => 'assets/images/pr11.png',
            ],
            [
                'name'        => 'Product 12',
                'description' => 'Description for product 12',
                'price'       => 45.00,
                'category'    => 'clothing',
                'stock'       => 55,
                'image_path'   => 'assets/images/pr12.png',
            ],
            [
                'name'        => 'Product 13',
                'description' => 'Description for product 13',
                'price'       => 22.50,
                'category'    => 'home',
                'stock'       => 90,
                'image_path'   => 'assets/images/phone1.png',
            ],
            [
                'name'        => 'Product 14',
                'description' => 'Description for product 14',
                'price'       => 99.99,
                'category'    => 'electronics',
                'stock'       => 35,
                'image_path'   => 'assets/images/c1.png',
            ],
            [
                'name'        => 'Product 15',
                'description' => 'Description for product 15',
                'price'       => 39.99,
                'category'    => 'clothing',
                'stock'       => 65,
                'image_path'   => 'assets/images/c2.png',
            ],
            [
                'name'        => 'Product 16',
                'description' => 'Description for product 16',
                'price'       => 27.99,
                'category'    => 'home',
                'stock'       => 75,
                'image_path'   => 'assets/images/c3.png',
            ],
            [
                'name'        => 'Product 17',
                'description' => 'Description for product 17',
                'price'       => 149.99,
                'category'    => 'electronics',
                'stock'       => 15,
                'image_path'   => 'assets/images/c4.png',
            ],
            [
                'name'        => 'Product 18',
                'description' => 'Description for product 18',
                'price'       => 55.00,
                'category'    => 'clothing',
                'stock'       => 50,
                'image_path'   => 'assets/images/c5.png',
            ],
            [
                'name'        => 'Product 19',
                'description' => 'Description for product 19',
                'price'       => 31.99,
                'category'    => 'home',
                'stock'       => 85,
                'image_path'   => 'assets/images/t1.png',
            ],
            [
                'name'        => 'Product 20',
                'description' => 'Description for product 20',
                'price'       => 89.99,
                'category'    => 'electronics',
                'stock'       => 40,
                'image_path'   => 'assets/images/w1.png',
            ],
        ];

        $inventory = app(InventoryGrpcClient::class);

        foreach ($products as $data) {
            $stock = $data['stock'];
            unset($data['stock']);

            $product = Product::updateOrCreate(
                ['name' => $data['name']],
                $data
            );

            try {
                $inventory->setStock($product->id, $stock);
            } catch (\Throwable $e) {
                $this->command?->warn("Product {$product->id} synced/created but stock sync failed");
            }
        }}
        
    }