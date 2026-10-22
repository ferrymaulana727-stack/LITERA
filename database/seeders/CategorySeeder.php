<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Novel & Sastra',
            'Buku Komputer & Pemrograman',
            'Pengembangan Diri (Self-Improvement)',
            'Alat Tulis & Perlengkapan Kantor (ATK)',
        ];

        foreach ($categories as $categoryName) {
            Category::firstOrCreate(['name' => $categoryName]);
        }
    }
}
