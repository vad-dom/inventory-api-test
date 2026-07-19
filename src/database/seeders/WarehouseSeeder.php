<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'code' => 'WH-MSK',
                'name' => 'Moscow Warehouse',
            ],
            [
                'code' => 'WH-SPB',
                'name' => 'Saint Petersburg Warehouse',
            ],
            [
                'code' => 'WH-KZN',
                'name' => 'Kazan Warehouse',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::query()->updateOrCreate(
                ['code' => $warehouse['code']],
                $warehouse
            );
        }
    }
}
