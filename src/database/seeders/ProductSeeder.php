<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'sku' => 'SKU-001',
                'name' => 'Laptop',
                'description' => 'Business laptop',
            ],
            [
                'sku' => 'SKU-002',
                'name' => 'Monitor',
                'description' => '27-inch monitor',
            ],
            [
                'sku' => 'SKU-003',
                'name' => 'Keyboard',
                'description' => 'Mechanical keyboard',
            ],
            [
                'sku' => 'SKU-004',
                'name' => 'Mouse',
                'description' => 'Wireless mouse',
            ],
            [
                'sku' => 'SKU-005',
                'name' => 'Headset',
                'description' => 'USB headset',
            ],
            [
                'sku' => 'SKU-006',
                'name' => 'Docking Station',
                'description' => 'USB-C docking station',
            ],
            [
                'sku' => 'SKU-007',
                'name' => 'Webcam',
                'description' => 'Full HD webcam',
            ],
            [
                'sku' => 'SKU-008',
                'name' => 'Printer',
                'description' => 'Laser printer',
            ],
            [
                'sku' => 'SKU-009',
                'name' => 'SSD Drive',
                'description' => '1TB SSD',
            ],
            [
                'sku' => 'SKU-010',
                'name' => 'Router',
                'description' => 'Wi-Fi router',
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}
