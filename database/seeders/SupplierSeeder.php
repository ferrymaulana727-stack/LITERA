<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT Gramedia Pustaka Utama',
                'phone' => '021-53650110',
                'email' => 'kontak@gpu.id',
            ],
            [
                'name' => 'Penerbit Erlangga & Elex Media',
                'phone' => '021-8717006',
                'email' => 'info@erlangga.co.id',
            ],
            [
                'name' => 'PT Faber-Castell & Joyko Stationary',
                'phone' => '021-4601234',
                'email' => 'supply@stationary.co.id',
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
