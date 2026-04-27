<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Perros', 'slug' => 'perros'],
            ['name' => 'Gatos', 'slug' => 'gatos'],
            ['name' => 'Aves', 'slug' => 'aves'],
        ];

        foreach ($categories as $cat) {
            $category = \App\Models\Category::create($cat);

            for ($i = 1; $i <= 5; $i++) {
                $product = \App\Models\Product::create([
                    'category_id' => $category->id,
                    'name' => "Producto {$cat['name']} {$i}",
                    'slug' => \Illuminate\Support\Str::slug("Producto {$cat['name']} {$i}"),
                    'brand' => 'PetBrand',
                    'description' => "Esta es una descripción premium para el producto {$i} de la categoría {$cat['name']}.",
                ]);

                \App\Models\ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => "SKU-{$cat['name']}-{$i}",
                    'price' => rand(10, 100),
                    'stock' => rand(5, 50),
                ]);
            }
        }
    }
}
