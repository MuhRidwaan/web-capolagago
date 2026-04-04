<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductActivityTagSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_activity_tags')->delete();

        // Map slug produk ke tag IDs
        // Tag IDs: 1=Keluarga, 2=Pasangan, 3=Grup, 4=Corporate, 5=Pelajar, 6=Solo
        //          7=Mudah, 8=Menengah, 9=Menantang
        //          10=Sarapan, 11=Makan, 12=Parkir, 13=Pet, 14=WiFi, 15=Kolam
        //          16=Alam, 17=Relaksasi, 18=Adrenaline, 19=Edukasi, 20=Romantis, 21=Instagramable, 22=Eco
        $map = [
            'glamping-riverside-luxury'    => [1, 2, 10, 16, 17, 20, 21],
            'glamping-forest-view'         => [1, 2, 10, 16, 17, 21],
            'standard-camping-ground'      => [1, 3, 5, 7, 12, 16, 22],
            'family-camping-package'       => [1, 5, 7, 12, 16, 22],
            'homestay-forest-view'         => [1, 12, 16, 21],
            'homestay-riverside'           => [1, 2, 12, 16, 17],
            'outbound-corporate-package'   => [4, 8, 16, 19],
            'outbound-school-trip'         => [5, 7, 16, 19],
            'paket-catering-bbq'           => [1, 3, 4],
            'paket-catering-nasi-box'      => [1, 3, 4, 5],
            'rafting-ciater-adventure'     => [3, 6, 8, 16, 18],
            'rafting-ciater-full-day'      => [3, 6, 11, 16, 18],
            'tiket-sari-ater'              => [1, 7, 15, 17],
            'paket-rendam-relax-sari-ater' => [1, 2, 7, 15, 17, 20],
            'paralayang-tandem-santiong'   => [2, 3, 9, 16, 18, 21],
            'atv-adventure-upas-hill'      => [3, 8, 16, 18],
            'landy-tour-upas-hill'         => [1, 2, 7, 16, 21],
            'canyoneering-dayang-sumbi'    => [3, 6, 9, 16, 18],
        ];

        $products = DB::table('products')->pluck('id', 'slug');

        $rows = [];
        foreach ($map as $slug => $tagIds) {
            if (!isset($products[$slug])) {
                continue;
            }
            foreach ($tagIds as $tagId) {
                $rows[] = [
                    'product_id' => $products[$slug],
                    'tag_id'     => $tagId,
                ];
            }
        }

        DB::table('product_activity_tags')->insert($rows);
    }
}
