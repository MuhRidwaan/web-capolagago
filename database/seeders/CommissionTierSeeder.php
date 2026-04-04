<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommissionTierSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('commission_tiers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('commission_tiers')->insert([
            ['id' => 1, 'name' => 'Starter',  'min_monthly_revenue' => 0.00,        'commission_rate' => 10.00, 'subscription_discount' => 0.00,  'description' => 'Mitra baru, omzet s.d. Rp 5 juta/bulan'],
            ['id' => 2, 'name' => 'Silver',   'min_monthly_revenue' => 5000000.00,  'commission_rate' => 8.00,  'subscription_discount' => 10.00, 'description' => 'Omzet Rp 5–20 juta/bulan'],
            ['id' => 3, 'name' => 'Gold',     'min_monthly_revenue' => 20000000.00, 'commission_rate' => 6.00,  'subscription_discount' => 20.00, 'description' => 'Omzet Rp 20–50 juta/bulan'],
            ['id' => 4, 'name' => 'Platinum', 'min_monthly_revenue' => 50000000.00, 'commission_rate' => 5.00,  'subscription_discount' => 30.00, 'description' => 'Omzet di atas Rp 50 juta/bulan'],
        ]);
    }
}
