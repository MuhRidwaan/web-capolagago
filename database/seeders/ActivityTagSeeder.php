<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivityTagSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('activity_tags')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('activity_tags')->insert([
            // Audience
            ['id' => 1,  'name' => 'Keluarga',           'slug' => 'keluarga',        'group_name' => 'audience'],
            ['id' => 2,  'name' => 'Pasangan',            'slug' => 'pasangan',        'group_name' => 'audience'],
            ['id' => 3,  'name' => 'Grup Pertemanan',     'slug' => 'grup-pertemanan', 'group_name' => 'audience'],
            ['id' => 4,  'name' => 'Corporate / Team',    'slug' => 'corporate-team',  'group_name' => 'audience'],
            ['id' => 5,  'name' => 'Pelajar & Mahasiswa', 'slug' => 'pelajar',         'group_name' => 'audience'],
            ['id' => 6,  'name' => 'Solo Traveler',       'slug' => 'solo-traveler',   'group_name' => 'audience'],
            // Difficulty
            ['id' => 7,  'name' => 'Mudah',               'slug' => 'mudah',           'group_name' => 'difficulty'],
            ['id' => 8,  'name' => 'Menengah',             'slug' => 'menengah',        'group_name' => 'difficulty'],
            ['id' => 9,  'name' => 'Menantang',            'slug' => 'menantang',       'group_name' => 'difficulty'],
            // Facility
            ['id' => 10, 'name' => 'Inklusif Sarapan',    'slug' => 'inklusif-sarapan','group_name' => 'facility'],
            ['id' => 11, 'name' => 'Inklusif Makan',      'slug' => 'inklusif-makan',  'group_name' => 'facility'],
            ['id' => 12, 'name' => 'Parkir Luas',         'slug' => 'parkir-luas',     'group_name' => 'facility'],
            ['id' => 13, 'name' => 'Pet Friendly',        'slug' => 'pet-friendly',    'group_name' => 'facility'],
            ['id' => 14, 'name' => 'WiFi Tersedia',       'slug' => 'wifi',            'group_name' => 'facility'],
            ['id' => 15, 'name' => 'Kolam Renang',        'slug' => 'kolam-renang',    'group_name' => 'facility'],
            // Theme
            ['id' => 16, 'name' => 'Alam & Petualangan',  'slug' => 'alam-petualangan','group_name' => 'theme'],
            ['id' => 17, 'name' => 'Relaksasi',           'slug' => 'relaksasi',       'group_name' => 'theme'],
            ['id' => 18, 'name' => 'Adrenaline Rush',     'slug' => 'adrenaline',      'group_name' => 'theme'],
            ['id' => 19, 'name' => 'Edukasi',             'slug' => 'edukasi',         'group_name' => 'theme'],
            ['id' => 20, 'name' => 'Romantis',            'slug' => 'romantis',        'group_name' => 'theme'],
            ['id' => 21, 'name' => 'Instagramable',       'slug' => 'instagramable',   'group_name' => 'theme'],
            ['id' => 22, 'name' => 'Eco Tourism',         'slug' => 'eco-tourism',     'group_name' => 'theme'],
        ]);
    }
}
