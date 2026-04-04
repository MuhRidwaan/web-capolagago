<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductCategorySeeder::class,
            ActivityTagSeeder::class,
            PaymentMethodSeeder::class,
            CommissionTierSeeder::class,
            PromoTypeSeeder::class,
            MitraSeeder::class,
            ProductSeeder::class,
            ProductActivityTagSeeder::class,
        ]);
    }
}
