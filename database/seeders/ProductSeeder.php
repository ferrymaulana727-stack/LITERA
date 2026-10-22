<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy('name');

        $products = [
            [
                'sku' => 'BKU-NVL-01',
                'name' => 'Laut Bercerita - Leila S. Chudori',
                'image' => 'images/products/bku-nvl-01.svg',
                'category_name' => 'Novel & Sastra',
                'purchase_price' => 75000,
                'selling_price' => 115000,
                'stock' => 20,
                'minimum_stock' => 5,
            ],
            [
                'sku' => 'BKU-NVL-02',
                'name' => 'Bumi Manusia - Pramoedya A. Toer',
                'image' => 'images/products/bku-nvl-02.svg',
                'category_name' => 'Novel & Sastra',
                'purchase_price' => 85000,
                'selling_price' => 135000,
                'stock' => 12,
                'minimum_stock' => 5,
            ],
            [
                'sku' => 'BKU-TEK-01',
                'name' => 'Mastering Laravel 11 & Eloquent ORM',
                'image' => 'images/products/bku-tek-01.svg',
                'category_name' => 'Buku Komputer & Pemrograman',
                'purchase_price' => 90000,
                'selling_price' => 145000,
                'stock' => 3,
                'minimum_stock' => 5,
            ],
            [
                'sku' => 'BKU-SLF-01',
                'name' => 'Atomic Habits (Edisi Bahasa Indonesia)',
                'image' => 'images/products/bku-slf-01.svg',
                'category_name' => 'Pengembangan Diri (Self-Improvement)',
                'purchase_price' => 70000,
                'selling_price' => 108000,
                'stock' => 18,
                'minimum_stock' => 5,
            ],
            [
                'sku' => 'ATK-KRT-01',
                'name' => 'Buku Catatan Loose Leaf B5 Campus',
                'image' => 'images/products/atk-krt-01.svg',
                'category_name' => 'Alat Tulis & Perlengkapan Kantor (ATK)',
                'purchase_price' => 15000,
                'selling_price' => 28000,
                'stock' => 40,
                'minimum_stock' => 10,
            ],
            [
                'sku' => 'ATK-PEN-01',
                'name' => 'Paket Pulpen Gel Sarasa Clip 0.5 Hitam',
                'image' => 'images/products/atk-pen-01.svg',
                'category_name' => 'Alat Tulis & Perlengkapan Kantor (ATK)',
                'purchase_price' => 55000,
                'selling_price' => 85000,
                'stock' => 4,
                'minimum_stock' => 6,
            ],
        ];

        foreach ($products as $item) {
            $category = $categories->get($item['category_name'])
                ?? Category::firstOrCreate(['name' => $item['category_name']]);

            Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'image' => $item['image'],
                    'purchase_price' => $item['purchase_price'],
                    'selling_price' => $item['selling_price'],
                    'stock' => $item['stock'],
                    'minimum_stock' => $item['minimum_stock'],
                ]
            );
        }
    }
}
