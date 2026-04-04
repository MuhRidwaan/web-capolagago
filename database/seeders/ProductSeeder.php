<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_images')->delete();
        DB::table('product_activity_tags')->delete();
        DB::table('products')->delete();

        // Ambil mitra_id berdasarkan slug (agar tidak hardcode ID)
        $mitraIds = DB::table('mitra_profiles')
            ->pluck('id', 'slug');

        $sariAter  = $mitraIds['sari-ater-hot-spring']      ?? null;
        $rafting   = $mitraIds['ciater-rafting-adventure']  ?? null;
        $paralayang = $mitraIds['paralayang-santiong']      ?? null;
        $atv       = $mitraIds['atv-upas-hill']             ?? null;
        $canyon    = $mitraIds['canyoneering-dayang-sumbi'] ?? null;

        $now = now();

        DB::table('products')->insert([
            // ── INTERNAL: Glamping ──────────────────────────────────
            [
                'mitra_id' => null, 'category_id' => 2,
                'name' => 'Glamping Riverside Luxury', 'slug' => 'glamping-riverside-luxury',
                'short_desc' => 'Tenda glamping mewah dengan pemandangan langsung ke sungai Capolaga.',
                'description' => 'Nikmati pengalaman glamping eksklusif di tepi sungai Capolaga. Tenda berukuran besar dilengkapi kasur double, dekorasi fairy light, sarapan pagi, dan akses ke area api unggun.',
                'price' => 850000.00, 'price_label' => '/malam',
                'min_pax' => 1, 'max_pax' => 2, 'max_capacity' => 5,
                'duration_hours' => null, 'is_featured' => true, 'is_active' => true,
                'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'mitra_id' => null, 'category_id' => 2,
                'name' => 'Glamping Forest View', 'slug' => 'glamping-forest-view',
                'short_desc' => 'Glamping premium di tengah hutan pinus dengan view alam yang memukau.',
                'description' => 'Paket glamping dengan pemandangan hutan pinus. Termasuk breakfast, hammock area, dan tour singkat kebun teh.',
                'price' => 750000.00, 'price_label' => '/malam',
                'min_pax' => 1, 'max_pax' => 2, 'max_capacity' => 4,
                'duration_hours' => null, 'is_featured' => true, 'is_active' => true,
                'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now,
            ],
            // ── INTERNAL: Camping ───────────────────────────────────
            [
                'mitra_id' => null, 'category_id' => 1,
                'name' => 'Standard Camping Ground', 'slug' => 'standard-camping-ground',
                'short_desc' => 'Area camping luas di bawah hutan pinus dengan fasilitas toilet bersih.',
                'description' => 'Camping di alam terbuka dengan fasilitas lengkap: area parkir, toilet & kamar mandi bersih, area api unggun bersama, dan air bersih.',
                'price' => 150000.00, 'price_label' => '/malam',
                'min_pax' => 1, 'max_pax' => 8, 'max_capacity' => 20,
                'duration_hours' => null, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'mitra_id' => null, 'category_id' => 1,
                'name' => 'Family Camping Package', 'slug' => 'family-camping-package',
                'short_desc' => 'Paket camping keluarga lengkap dengan tenda, matras, dan sleeping bag.',
                'description' => 'Semua sudah tersedia: tenda untuk 4 orang, matras, sleeping bag, lantern, dan satu sesi bakar jagung bersama.',
                'price' => 350000.00, 'price_label' => '/unit',
                'min_pax' => 2, 'max_pax' => 4, 'max_capacity' => 10,
                'duration_hours' => null, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now,
            ],
            // ── INTERNAL: Homestay ──────────────────────────────────
            [
                'mitra_id' => null, 'category_id' => 3,
                'name' => 'Homestay Forest View', 'slug' => 'homestay-forest-view',
                'short_desc' => 'Rumah kayu estetik yang nyaman untuk keluarga besar.',
                'description' => 'Homestay 3 kamar tidur dengan living room luas, dapur bersama, teras dengan view hutan, dan kapasitas hingga 8 orang.',
                'price' => 1300000.00, 'price_label' => '/malam',
                'min_pax' => 2, 'max_pax' => 8, 'max_capacity' => 3,
                'duration_hours' => null, 'is_featured' => true, 'is_active' => true,
                'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'mitra_id' => null, 'category_id' => 3,
                'name' => 'Homestay Riverside', 'slug' => 'homestay-riverside',
                'short_desc' => 'Homestay dengan akses langsung ke tepi sungai, cocok untuk keluarga.',
                'description' => '2 kamar tidur, living room, dapur lengkap, teras tepi sungai. Kapasitas maksimal 6 orang.',
                'price' => 950000.00, 'price_label' => '/malam',
                'min_pax' => 2, 'max_pax' => 6, 'max_capacity' => 2,
                'duration_hours' => null, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now,
            ],
            // ── INTERNAL: Outbound ──────────────────────────────────
            [
                'mitra_id' => null, 'category_id' => 4,
                'name' => 'Outbound Corporate Package', 'slug' => 'outbound-corporate-package',
                'short_desc' => 'Paket outbound team building untuk perusahaan, minimal 20 orang.',
                'description' => 'Full-day outbound dengan 8 game challenges, fasilitator profesional, makan siang, snack, sertifikat, dan dokumentasi foto & video.',
                'price' => 250000.00, 'price_label' => '/orang',
                'min_pax' => 20, 'max_pax' => 100, 'max_capacity' => 2,
                'duration_hours' => 8.0, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 7, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'mitra_id' => null, 'category_id' => 4,
                'name' => 'Outbound School Trip', 'slug' => 'outbound-school-trip',
                'short_desc' => 'Paket outbound edukatif untuk pelajar SD–SMA, minimal 30 orang.',
                'description' => 'Program outbound edukatif dengan permainan yang menyenangkan dan edukatif, cocok untuk school trip dan study tour.',
                'price' => 175000.00, 'price_label' => '/orang',
                'min_pax' => 30, 'max_pax' => 200, 'max_capacity' => 1,
                'duration_hours' => 6.0, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 8, 'created_at' => $now, 'updated_at' => $now,
            ],
            // ── INTERNAL: Catering ──────────────────────────────────
            [
                'mitra_id' => null, 'category_id' => 5,
                'name' => 'Paket Catering BBQ', 'slug' => 'paket-catering-bbq',
                'short_desc' => 'Paket bakar-bakaran malam hari lengkap dengan daging, sayuran, dan minuman.',
                'description' => 'Termasuk: daging ayam, sapi, jagung, ubi, sambal, lalapan, nasi, dan minuman teh/air mineral. Peralatan BBQ disediakan.',
                'price' => 85000.00, 'price_label' => '/orang',
                'min_pax' => 10, 'max_pax' => 200, 'max_capacity' => 999,
                'duration_hours' => null, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 9, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'mitra_id' => null, 'category_id' => 5,
                'name' => 'Paket Catering Nasi Box', 'slug' => 'paket-catering-nasi-box',
                'short_desc' => 'Nasi box pilihan menu masakan Sunda untuk makan siang/malam.',
                'description' => 'Menu: nasi putih, ayam goreng/bakar, lalapan, sambal, tempe, tahu, dan air mineral. Tersedia pilihan menu vegetarian.',
                'price' => 45000.00, 'price_label' => '/orang',
                'min_pax' => 20, 'max_pax' => 500, 'max_capacity' => 999,
                'duration_hours' => null, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now,
            ],
            // ── ADD-ON: Rafting ─────────────────────────────────────
            [
                'mitra_id' => $rafting, 'category_id' => 6,
                'name' => 'Rafting Ciater Adventure', 'slug' => 'rafting-ciater-adventure',
                'short_desc' => 'Paket rafting seru di aliran sungai Ciater dekat kawasan Capolaga.',
                'description' => 'Arung jeram sepanjang 8 km di Sungai Ciater dengan jeram kelas II-III. Termasuk: helm, pelampung, paddle, pemandu, dan asuransi jiwa.',
                'price' => 200000.00, 'price_label' => '/orang',
                'min_pax' => 4, 'max_pax' => 20, 'max_capacity' => 10,
                'duration_hours' => 2.5, 'is_featured' => true, 'is_active' => true,
                'sort_order' => 11, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'mitra_id' => $rafting, 'category_id' => 6,
                'name' => 'Rafting Ciater Full Day', 'slug' => 'rafting-ciater-full-day',
                'short_desc' => 'Paket rafting full day dengan makan siang dan dokumentasi.',
                'description' => 'Rafting 12 km + makan siang di pinggir sungai + foto profesional. Durasi total sekitar 5 jam termasuk briefing dan perjalanan.',
                'price' => 350000.00, 'price_label' => '/orang',
                'min_pax' => 4, 'max_pax' => 16, 'max_capacity' => 6,
                'duration_hours' => 5.0, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 12, 'created_at' => $now, 'updated_at' => $now,
            ],
            // ── ADD-ON: Sari Ater ───────────────────────────────────
            [
                'mitra_id' => $sariAter, 'category_id' => 7,
                'name' => 'Tiket Pemandian Sari Ater', 'slug' => 'tiket-sari-ater',
                'short_desc' => 'Tiket masuk pemandian air panas alam Sari Ater untuk satu hari.',
                'description' => 'Akses ke seluruh fasilitas kolam renang air panas alam, area bermain anak, dan taman wisata Sari Ater.',
                'price' => 100000.00, 'price_label' => '/orang',
                'min_pax' => 1, 'max_pax' => 50, 'max_capacity' => 200,
                'duration_hours' => null, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 13, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'mitra_id' => $sariAter, 'category_id' => 7,
                'name' => 'Paket Rendam & Relax Sari Ater', 'slug' => 'paket-rendam-relax-sari-ater',
                'short_desc' => 'Paket premium: tiket masuk + sewa kolam privat 2 jam + snack.',
                'description' => 'Sewa kolam privat 2 jam untuk rombongan 4–8 orang + tiket masuk + paket snack tradisional.',
                'price' => 250000.00, 'price_label' => '/orang',
                'min_pax' => 4, 'max_pax' => 8, 'max_capacity' => 20,
                'duration_hours' => 2.0, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 14, 'created_at' => $now, 'updated_at' => $now,
            ],
            // ── ADD-ON: Paralayang ──────────────────────────────────
            [
                'mitra_id' => $paralayang, 'category_id' => 8,
                'name' => 'Paralayang Tandem Santiong', 'slug' => 'paralayang-tandem-santiong',
                'short_desc' => 'Terbang paralayang tandem bersama pilot berpengalaman di Bukit Santiong.',
                'description' => 'Pengalaman terbang paralayang tandem dari ketinggian 1.200 mdpl dengan pemandangan Subang 360°. Pilot bersertifikat FASI. Termasuk asuransi.',
                'price' => 350000.00, 'price_label' => '/orang',
                'min_pax' => 1, 'max_pax' => 1, 'max_capacity' => 8,
                'duration_hours' => 0.5, 'is_featured' => true, 'is_active' => true,
                'sort_order' => 15, 'created_at' => $now, 'updated_at' => $now,
            ],
            // ── ADD-ON: ATV ─────────────────────────────────────────
            [
                'mitra_id' => $atv, 'category_id' => 9,
                'name' => 'ATV Adventure Upas Hill', 'slug' => 'atv-adventure-upas-hill',
                'short_desc' => 'Berkendara ATV melewati jalur off-road di kebun teh Upas Hill.',
                'description' => 'Jalur ATV 3 km melewati perkebunan teh dan hutan pinus. Tersedia ATV untuk 1 orang dan 2 orang. Pemandu & helm disediakan.',
                'price' => 150000.00, 'price_label' => '/orang',
                'min_pax' => 1, 'max_pax' => 2, 'max_capacity' => 8,
                'duration_hours' => 1.0, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 16, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'mitra_id' => $atv, 'category_id' => 9,
                'name' => 'Landy Tour Upas Hill', 'slug' => 'landy-tour-upas-hill',
                'short_desc' => 'Tour keliling perkebunan teh menggunakan Land Rover klasik.',
                'description' => 'Jelajahi keindahan perkebunan teh Upas Hill dengan Land Rover klasik bersama pemandu wisata. Kapasitas 6 orang per unit.',
                'price' => 120000.00, 'price_label' => '/orang',
                'min_pax' => 4, 'max_pax' => 6, 'max_capacity' => 4,
                'duration_hours' => 1.5, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 17, 'created_at' => $now, 'updated_at' => $now,
            ],
            // ── ADD-ON: Canyoneering ────────────────────────────────
            [
                'mitra_id' => $canyon, 'category_id' => 10,
                'name' => 'Canyoneering Dayang Sumbi', 'slug' => 'canyoneering-dayang-sumbi',
                'short_desc' => 'Penelusuran canyon dan terjun ke air terjun Dayang Sumbi.',
                'description' => 'Menyusuri aliran sungai, melewati tebing, dan menikmati air terjun Dayang Sumbi bersama pemandu profesional. Semua peralatan disediakan.',
                'price' => 225000.00, 'price_label' => '/orang',
                'min_pax' => 4, 'max_pax' => 12, 'max_capacity' => 4,
                'duration_hours' => 4.0, 'is_featured' => false, 'is_active' => true,
                'sort_order' => 18, 'created_at' => $now, 'updated_at' => $now,
            ],
        ]);

        // Insert product_images
        $products = DB::table('products')->pluck('id', 'slug');

        $images = [
            ['glamping-riverside-luxury',    'products/glamping-riverside-luxury-1.jpg',  'Glamping Riverside Luxury tampak depan', true,  1],
            ['glamping-riverside-luxury',    'products/glamping-riverside-luxury-2.jpg',  'Interior tenda glamping riverside',      false, 2],
            ['glamping-forest-view',         'products/glamping-forest-view-1.jpg',       'Glamping Forest View di antara pinus',   true,  1],
            ['standard-camping-ground',      'products/standard-camping-ground-1.jpg',    'Area camping ground Capolaga',           true,  1],
            ['family-camping-package',       'products/family-camping-package-1.jpg',     'Paket tenda keluarga',                   true,  1],
            ['homestay-forest-view',         'products/homestay-forest-view-1.jpg',       'Homestay kayu Forest View',              true,  1],
            ['homestay-forest-view',         'products/homestay-forest-view-2.jpg',       'Kamar tidur homestay',                   false, 2],
            ['homestay-riverside',           'products/homestay-riverside-1.jpg',         'Homestay tepi sungai',                   true,  1],
            ['outbound-corporate-package',   'products/outbound-corporate-1.jpg',         'Sesi outbound corporate',                true,  1],
            ['outbound-school-trip',         'products/outbound-school-1.jpg',            'Outbound school trip',                   true,  1],
            ['paket-catering-bbq',           'products/catering-bbq-1.jpg',               'Paket BBQ malam',                        true,  1],
            ['paket-catering-nasi-box',      'products/catering-nasi-box-1.jpg',          'Nasi box masakan Sunda',                 true,  1],
            ['rafting-ciater-adventure',     'products/rafting-ciater-1.jpg',             'Arung jeram Sungai Ciater',              true,  1],
            ['rafting-ciater-adventure',     'products/rafting-ciater-2.jpg',             'Tim rafting di jeram',                   false, 2],
            ['rafting-ciater-full-day',      'products/rafting-fullday-1.jpg',            'Rafting full day dengan makan siang',    true,  1],
            ['tiket-sari-ater',              'products/sari-ater-kolam-1.jpg',            'Kolam renang air panas Sari Ater',       true,  1],
            ['paket-rendam-relax-sari-ater', 'products/sari-ater-privat-1.jpg',           'Kolam privat Sari Ater',                 true,  1],
            ['paralayang-tandem-santiong',   'products/paralayang-santiong-1.jpg',        'Paralayang tandem Bukit Santiong',       true,  1],
            ['atv-adventure-upas-hill',      'products/atv-upas-hill-1.jpg',              'ATV di perkebunan teh Upas Hill',        true,  1],
            ['landy-tour-upas-hill',         'products/landy-upas-hill-1.jpg',            'Landy tour Upas Hill',                   true,  1],
            ['canyoneering-dayang-sumbi',    'products/canyoneering-dayang-1.jpg',        'Canyoneering air terjun Dayang Sumbi',   true,  1],
        ];

        $imageRows = [];
        foreach ($images as [$slug, $path, $alt, $isPrimary, $sort]) {
            if (isset($products[$slug])) {
                $imageRows[] = [
                    'product_id' => $products[$slug],
                    'image_path' => $path,
                    'alt_text'   => $alt,
                    'is_primary' => $isPrimary,
                    'sort_order' => $sort,
                ];
            }
        }

        DB::table('product_images')->insert($imageRows);
    }
}
