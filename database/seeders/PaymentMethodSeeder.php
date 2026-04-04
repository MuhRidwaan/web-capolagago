<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('payment_methods')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('payment_methods')->insert([
            ['id' => 1,  'name' => 'QRIS',                'code' => 'QRIS',        'provider' => 'midtrans', 'type' => 'qris',    'fee_flat' => 0.00,    'fee_percent' => 0.700, 'min_amount' => 10000.00, 'max_amount' => 10000000.00, 'is_active' => true,  'sort_order' => 1],
            ['id' => 2,  'name' => 'GoPay',               'code' => 'GOPAY',       'provider' => 'midtrans', 'type' => 'ewallet', 'fee_flat' => 0.00,    'fee_percent' => 2.000, 'min_amount' => 10000.00, 'max_amount' => 10000000.00, 'is_active' => true,  'sort_order' => 2],
            ['id' => 3,  'name' => 'OVO',                 'code' => 'OVO',         'provider' => 'midtrans', 'type' => 'ewallet', 'fee_flat' => 0.00,    'fee_percent' => 2.000, 'min_amount' => 10000.00, 'max_amount' => 10000000.00, 'is_active' => true,  'sort_order' => 3],
            ['id' => 4,  'name' => 'Dana',                'code' => 'DANA',        'provider' => 'midtrans', 'type' => 'ewallet', 'fee_flat' => 0.00,    'fee_percent' => 2.000, 'min_amount' => 10000.00, 'max_amount' => 10000000.00, 'is_active' => true,  'sort_order' => 4],
            ['id' => 5,  'name' => 'ShopeePay',           'code' => 'SHOPEEPAY',   'provider' => 'midtrans', 'type' => 'ewallet', 'fee_flat' => 0.00,    'fee_percent' => 2.000, 'min_amount' => 10000.00, 'max_amount' => 10000000.00, 'is_active' => true,  'sort_order' => 5],
            ['id' => 6,  'name' => 'BCA Virtual Account', 'code' => 'BCA_VA',      'provider' => 'midtrans', 'type' => 'va',      'fee_flat' => 4000.00, 'fee_percent' => 0.000, 'min_amount' => 10000.00, 'max_amount' => null,        'is_active' => true,  'sort_order' => 6],
            ['id' => 7,  'name' => 'BNI Virtual Account', 'code' => 'BNI_VA',      'provider' => 'midtrans', 'type' => 'va',      'fee_flat' => 4000.00, 'fee_percent' => 0.000, 'min_amount' => 10000.00, 'max_amount' => null,        'is_active' => true,  'sort_order' => 7],
            ['id' => 8,  'name' => 'BRI Virtual Account', 'code' => 'BRI_VA',      'provider' => 'midtrans', 'type' => 'va',      'fee_flat' => 4000.00, 'fee_percent' => 0.000, 'min_amount' => 10000.00, 'max_amount' => null,        'is_active' => true,  'sort_order' => 8],
            ['id' => 9,  'name' => 'Mandiri Virtual Acc', 'code' => 'MANDIRI_VA',  'provider' => 'midtrans', 'type' => 'va',      'fee_flat' => 4000.00, 'fee_percent' => 0.000, 'min_amount' => 10000.00, 'max_amount' => null,        'is_active' => true,  'sort_order' => 9],
            ['id' => 10, 'name' => 'Permata VA',          'code' => 'PERMATA_VA',  'provider' => 'midtrans', 'type' => 'va',      'fee_flat' => 4000.00, 'fee_percent' => 0.000, 'min_amount' => 10000.00, 'max_amount' => null,        'is_active' => true,  'sort_order' => 10],
            ['id' => 11, 'name' => 'Kartu Kredit/Debit',  'code' => 'CREDIT_CARD', 'provider' => 'midtrans', 'type' => 'cc',      'fee_flat' => 0.00,    'fee_percent' => 2.900, 'min_amount' => 10000.00, 'max_amount' => null,        'is_active' => true,  'sort_order' => 11],
            ['id' => 12, 'name' => 'Indomaret',           'code' => 'INDOMARET',   'provider' => 'midtrans', 'type' => 'cstore',  'fee_flat' => 5000.00, 'fee_percent' => 0.000, 'min_amount' => 10000.00, 'max_amount' => 5000000.00,  'is_active' => true,  'sort_order' => 12],
            ['id' => 13, 'name' => 'Alfamart',            'code' => 'ALFAMART',    'provider' => 'midtrans', 'type' => 'cstore',  'fee_flat' => 5000.00, 'fee_percent' => 0.000, 'min_amount' => 10000.00, 'max_amount' => 5000000.00,  'is_active' => true,  'sort_order' => 13],
            ['id' => 14, 'name' => 'Transfer Manual',     'code' => 'MANUAL_TF',   'provider' => 'manual',   'type' => 'manual',  'fee_flat' => 0.00,    'fee_percent' => 0.000, 'min_amount' => 10000.00, 'max_amount' => null,        'is_active' => false, 'sort_order' => 14],
        ]);
    }
}
