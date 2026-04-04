<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromoTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('promo_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('promo_types')->insert([
            ['id' => 1, 'name' => 'Diskon Persentase', 'code' => 'PERCENT',    'description' => 'Potongan harga dalam persen dari total',            'discount_type' => 'percent',    'is_active' => true],
            ['id' => 2, 'name' => 'Diskon Nominal',    'code' => 'FIXED',      'description' => 'Potongan harga langsung dalam rupiah',              'discount_type' => 'fixed',      'is_active' => true],
            ['id' => 3, 'name' => 'Gratis Add-On',     'code' => 'FREE_ADDON', 'description' => 'Mendapatkan satu add-on activity secara gratis',    'discount_type' => 'free_addon', 'is_active' => true],
            ['id' => 4, 'name' => 'Early Bird',        'code' => 'EARLY_BIRD', 'description' => 'Diskon khusus untuk pemesanan jauh hari (H-30+)',   'discount_type' => 'early_bird', 'is_active' => true],
            ['id' => 5, 'name' => 'Paket Bundling',    'code' => 'BUNDLE',     'description' => 'Diskon saat memesan 2 atau lebih produk sekaligus', 'discount_type' => 'bundle',     'is_active' => true],
        ]);
    }
}
