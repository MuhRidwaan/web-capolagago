<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('product_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('product_categories')->insert([
            // Internal Capolaga
            ['id' => 1,  'name' => 'camping',      'slug' => 'camping',      'label' => 'Camping',             'icon' => 'tent',        'color_hex' => '#1A7A4A', 'type' => 'internal', 'sort_order' => 1,  'is_active' => true],
            ['id' => 2,  'name' => 'glamping',      'slug' => 'glamping',     'label' => 'Glamping',            'icon' => 'home-modern', 'color_hex' => '#0F5C8A', 'type' => 'internal', 'sort_order' => 2,  'is_active' => true],
            ['id' => 3,  'name' => 'homestay',      'slug' => 'homestay',     'label' => 'Homestay',            'icon' => 'building-2',  'color_hex' => '#7B4FA6', 'type' => 'internal', 'sort_order' => 3,  'is_active' => true],
            ['id' => 4,  'name' => 'outbound',      'slug' => 'outbound',     'label' => 'Outbound & Team',     'icon' => 'users-group', 'color_hex' => '#C45E1A', 'type' => 'internal', 'sort_order' => 4,  'is_active' => true],
            ['id' => 5,  'name' => 'catering',      'slug' => 'catering',     'label' => 'Catering & F&B',      'icon' => 'fork-knife',  'color_hex' => '#8A6C1A', 'type' => 'internal', 'sort_order' => 5,  'is_active' => true],
            // Add-on Mitra
            ['id' => 6,  'name' => 'rafting',       'slug' => 'rafting',      'label' => 'Rafting',             'icon' => 'waves',       'color_hex' => '#1A5FA6', 'type' => 'addon',    'sort_order' => 6,  'is_active' => true],
            ['id' => 7,  'name' => 'hot_spring',    'slug' => 'hot-spring',   'label' => 'Pemandian Air Panas', 'icon' => 'droplet',     'color_hex' => '#A61A1A', 'type' => 'addon',    'sort_order' => 7,  'is_active' => true],
            ['id' => 8,  'name' => 'paragliding',   'slug' => 'paragliding',  'label' => 'Paralayang',          'icon' => 'wind',        'color_hex' => '#1A6E8A', 'type' => 'addon',    'sort_order' => 8,  'is_active' => true],
            ['id' => 9,  'name' => 'atv',           'slug' => 'atv',          'label' => 'ATV & Off-Road',      'icon' => 'truck',       'color_hex' => '#6B4A1A', 'type' => 'addon',    'sort_order' => 9,  'is_active' => true],
            ['id' => 10, 'name' => 'canyoneering',  'slug' => 'canyoneering', 'label' => 'Canyoneering',        'icon' => 'mountain',    'color_hex' => '#2A6A3A', 'type' => 'addon',    'sort_order' => 10, 'is_active' => true],
        ]);
    }
}
