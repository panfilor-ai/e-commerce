<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Wireless Mouse', 'description' => 'Ergonomic wireless mouse with silent clicks and long battery life.', 'price' => 24.99, 'stock' => 50],
            ['name' => 'Mechanical Keyboard', 'description' => 'RGB mechanical keyboard with hot-swappable switches.', 'price' => 89.99, 'stock' => 30],
            ['name' => 'USB-C Hub', 'description' => '7-in-1 USB-C hub with HDMI, SD card reader and PD charging.', 'price' => 39.99, 'stock' => 75],
            ['name' => 'Noise-Cancelling Headphones', 'description' => 'Over-ear headphones with active noise cancellation.', 'price' => 149.99, 'stock' => 20],
            ['name' => 'Webcam 1080p', 'description' => 'Full HD webcam with built-in microphone for streaming and calls.', 'price' => 49.99, 'stock' => 40],
            ['name' => 'Laptop Stand', 'description' => 'Adjustable aluminum laptop stand for better ergonomics.', 'price' => 29.99, 'stock' => 60],
            ['name' => 'Portable SSD 1TB', 'description' => 'Fast 1TB portable SSD with USB 3.2 Gen 2.', 'price' => 99.99, 'stock' => 25],
            ['name' => 'Desk Lamp', 'description' => 'LED desk lamp with adjustable brightness and color temperature.', 'price' => 34.99, 'stock' => 45],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['name' => $product['name']], $product);
        }
    }
}
