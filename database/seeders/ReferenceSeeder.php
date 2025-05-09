<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('order_items')->delete();
        DB::table('orders')->delete();
        DB::table('stocks')->delete();
        DB::table('products')->delete();
        DB::table('warehouses')->delete();

        $warehouses = Warehouse::factory()->count(3)->create();

        $products = Product::factory()->count(10)->create();

        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                Stock::query()->create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'stock' => rand(5, 50),
                ]);
            }
        }
    }
}
