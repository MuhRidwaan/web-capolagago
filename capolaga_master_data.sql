-- =============================================================
--  CAPOLAGA ADVENTURE ECOSYSTEM — MASTER DATA
--  Platform: CapolagaGo (Laravel + MySQL)
--  Versi    : 1.0.0
--  Dibuat   : 2025
-- =============================================================
--  URUTAN INSERT (ikuti urutan ini agar FK tidak error):
--  1. roles
--  2. users (admin + mitra)
--  3. mitra_profiles
--  4. product_categories
--  5. products
--  6. product_images
--  7. payment_methods
--  8. commission_tiers
--  9. promo_types
--  10. activity_tags
--  11. product_activity_tags
-- =============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- 1. ROLES
--    Mendefinisikan peran pengguna dalam sistem
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50)  NOT NULL UNIQUE,
    label       VARCHAR(100) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (id, name, label, description) VALUES
(1, 'superadmin', 'Super Administrator', 'Akses penuh ke seluruh sistem platform'),
(2, 'admin',      'Administrator',       'Pengelola operasional platform Capolaga'),
(3, 'mitra',      'Mitra Lokal',         'Penyedia layanan/atraksi wisata sekitar'),
(4, 'buyer',      'Wisatawan',           'Pengguna akhir yang melakukan pemesanan');


-- -------------------------------------------------------------
-- 2. USERS — Akun default sistem
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id           TINYINT UNSIGNED NOT NULL DEFAULT 4,
    name              VARCHAR(150) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    phone             VARCHAR(20),
    password          VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token    VARCHAR(100),
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password semua akun default: "password123" (bcrypt hash)
INSERT INTO users (id, role_id, name, email, phone, password, email_verified_at) VALUES
(1, 1, 'Super Admin Capolaga',  'superadmin@capolaga.com',   '082100000001',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrIhv43.', NOW()),
(2, 2, 'Admin Operasional',     'admin@capolaga.com',        '082100000002',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrIhv43.', NOW()),
(3, 3, 'Sari Ater Hot Spring',  'mitra.sariater@email.com',  '082111111001',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrIhv43.', NOW()),
(4, 3, 'Ciater Rafting Center', 'mitra.rafting@email.com',   '082111111002',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrIhv43.', NOW()),
(5, 3, 'Paralayang Santiong',   'mitra.paralayang@email.com','082111111003',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrIhv43.', NOW()),
(6, 3, 'ATV Upas Hill',         'mitra.atv@email.com',       '082111111004',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrIhv43.', NOW()),
(7, 3, 'Canyoneering Dayang',   'mitra.canyon@email.com',    '082111111005',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.ucrIhv43.', NOW());


-- -------------------------------------------------------------
-- 3. MITRA_PROFILES — Profil detail penyedia layanan
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mitra_profiles (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          BIGINT UNSIGNED NOT NULL UNIQUE,
    business_name    VARCHAR(200) NOT NULL,
    slug             VARCHAR(200) NOT NULL UNIQUE,
    description      TEXT,
    address          TEXT,
    latitude         DECIMAL(10,7),
    longitude        DECIMAL(10,7),
    contact_person   VARCHAR(150),
    whatsapp         VARCHAR(20),
    website          VARCHAR(255),
    logo_path        VARCHAR(255),
    bank_name        VARCHAR(100),
    bank_account_no  VARCHAR(50),
    bank_account_name VARCHAR(150),
    commission_rate  DECIMAL(5,2) NOT NULL DEFAULT 10.00
                     COMMENT 'Persentase komisi platform (%)',
    subscription_type ENUM('free','basic','premium') NOT NULL DEFAULT 'free',
    status           ENUM('pending','active','inactive','suspended') NOT NULL DEFAULT 'pending',
    joined_at        DATE,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mitra_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO mitra_profiles
    (id, user_id, business_name, slug, description, address,
     latitude, longitude, contact_person, whatsapp,
     commission_rate, subscription_type, status, joined_at)
VALUES
(1, 3, 'Sari Ater Hot Spring Resort',
 'sari-ater-hot-spring',
 'Pemandian air panas alam terbesar di Subang dengan kolam renang dan villa.',
 'Jl. Raya Ciater, Ciater, Subang, Jawa Barat',
 -6.7234567, 107.7654321, 'Budi Santoso', '081211110001',
 8.00, 'premium', 'active', '2024-01-15'),

(2, 4, 'Ciater Rafting Adventure',
 'ciater-rafting-adventure',
 'Paket arung jeram di Sungai Ciater dengan pemandu berpengalaman dan peralatan safety lengkap.',
 'Jl. Sungai Ciater, Subang, Jawa Barat',
 -6.7312345, 107.7543210, 'Agus Rahayu', '081211110002',
 10.00, 'basic', 'active', '2024-02-01'),

(3, 5, 'Paralayang Santiong',
 'paralayang-santiong',
 'Olahraga paralayang dengan pemandangan alam Subang dari ketinggian 1.200 mdpl.',
 'Bukit Santiong, Subang, Jawa Barat',
 -6.7198765, 107.7812345, 'Dedi Wijaya', '081211110003',
 10.00, 'basic', 'active', '2024-03-10'),

(4, 6, 'ATV & Landy Upas Hill',
 'atv-upas-hill',
 'Petualangan off-road dengan ATV dan Landy di kawasan perkebunan teh Upas Hill.',
 'Kawasan Upas Hill, Subang, Jawa Barat',
 -6.7456789, 107.7234567, 'Rudi Hermawan', '081211110004',
 10.00, 'basic', 'active', '2024-04-05'),

(5, 7, 'Canyoneering Dayang Sumbi',
 'canyoneering-dayang-sumbi',
 'Penelusuran canyon dan air terjun Dayang Sumbi dengan pemandu profesional.',
 'Kawasan Dayang Sumbi, Subang, Jawa Barat',
 -6.7567890, 107.7345678, 'Eko Prasetyo', '081211110005',
 10.00, 'free', 'active', '2024-05-20');


-- -------------------------------------------------------------
-- 4. PRODUCT_CATEGORIES — Kategori produk/layanan
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_categories (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    label       VARCHAR(100) NOT NULL
                COMMENT 'Label tampilan di UI',
    icon        VARCHAR(50)  COMMENT 'Nama ikon (heroicons/lucide)',
    color_hex   VARCHAR(7)   COMMENT 'Warna badge kategori',
    type        ENUM('internal','addon') NOT NULL DEFAULT 'internal'
                COMMENT 'internal=produk Capolaga, addon=mitra lokal',
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   BOOLEAN NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO product_categories
    (id, name, slug, label, icon, color_hex, type, sort_order) VALUES
-- Produk internal Capolaga
(1,  'camping',       'camping',        'Camping',            'tent',          '#1A7A4A', 'internal', 1),
(2,  'glamping',      'glamping',       'Glamping',           'home-modern',   '#0F5C8A', 'internal', 2),
(3,  'homestay',      'homestay',       'Homestay',           'building-2',    '#7B4FA6', 'internal', 3),
(4,  'outbound',      'outbound',       'Outbound & Team',    'users-group',   '#C45E1A', 'internal', 4),
(5,  'catering',      'catering',       'Catering & F&B',     'fork-knife',    '#8A6C1A', 'internal', 5),
-- Add-on dari mitra lokal
(6,  'rafting',       'rafting',        'Rafting',            'waves',         '#1A5FA6', 'addon',    6),
(7,  'hot_spring',    'hot-spring',     'Pemandian Air Panas', 'droplet',      '#A61A1A', 'addon',    7),
(8,  'paragliding',   'paragliding',    'Paralayang',         'wind',          '#1A6E8A', 'addon',    8),
(9,  'atv',           'atv',            'ATV & Off-Road',     'truck',         '#6B4A1A', 'addon',    9),
(10, 'canyoneering',  'canyoneering',   'Canyoneering',       'mountain',      '#2A6A3A', 'addon',   10);


-- -------------------------------------------------------------
-- 5. PRODUCTS — Katalog produk lengkap
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mitra_id        BIGINT UNSIGNED NULL
                    COMMENT 'NULL = produk milik Capolaga sendiri',
    category_id     TINYINT UNSIGNED NOT NULL,
    name            VARCHAR(200) NOT NULL,
    slug            VARCHAR(200) NOT NULL UNIQUE,
    short_desc      VARCHAR(300),
    description     TEXT,
    price           DECIMAL(12,2) NOT NULL,
    price_label     VARCHAR(50) NOT NULL DEFAULT '/malam'
                    COMMENT 'Satuan harga: /malam, /orang, /sesi, /unit',
    min_pax         TINYINT UNSIGNED NOT NULL DEFAULT 1,
    max_pax         SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    max_capacity    SMALLINT UNSIGNED NOT NULL DEFAULT 1
                    COMMENT 'Jumlah unit/slot tersedia per hari',
    duration_hours  DECIMAL(4,1) NULL
                    COMMENT 'Durasi aktivitas dalam jam (untuk add-on)',
    is_featured     BOOLEAN NOT NULL DEFAULT FALSE,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    rating_avg      DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    review_count    INT UNSIGNED NOT NULL DEFAULT 0,
    sort_order      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    meta_title      VARCHAR(200),
    meta_desc       VARCHAR(300),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_mitra    FOREIGN KEY (mitra_id)    REFERENCES mitra_profiles(id),
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES product_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO products
    (id, mitra_id, category_id, name, slug, short_desc, description,
     price, price_label, min_pax, max_pax, max_capacity,
     duration_hours, is_featured, is_active, sort_order)
VALUES
-- ── INTERNAL: Glamping ─────────────────────────────────────
(1, NULL, 2,
 'Glamping Riverside Luxury',
 'glamping-riverside-luxury',
 'Tenda glamping mewah dengan pemandangan langsung ke sungai Capolaga.',
 'Nikmati pengalaman glamping eksklusif di tepi sungai Capolaga. '
 'Tenda berukuran besar dilengkapi kasur double, dekorasi fairy light, '
 'sarapan pagi, dan akses ke area api unggun.',
 850000.00, '/malam', 1, 2, 5,
 NULL, TRUE, TRUE, 1),

(2, NULL, 2,
 'Glamping Forest View',
 'glamping-forest-view',
 'Glamping premium di tengah hutan pinus dengan view alam yang memukau.',
 'Paket glamping dengan pemandangan hutan pinus. Termasuk breakfast, '
 'hammock area, dan tour singkat kebun teh.',
 750000.00, '/malam', 1, 2, 4,
 NULL, TRUE, TRUE, 2),

-- ── INTERNAL: Camping ──────────────────────────────────────
(3, NULL, 1,
 'Standard Camping Ground',
 'standard-camping-ground',
 'Area camping luas di bawah hutan pinus dengan fasilitas toilet bersih.',
 'Camping di alam terbuka dengan fasilitas lengkap: area parkir, '
 'toilet & kamar mandi bersih, area api unggun bersama, dan air bersih.',
 150000.00, '/malam', 1, 8, 20,
 NULL, FALSE, TRUE, 3),

(4, NULL, 1,
 'Family Camping Package',
 'family-camping-package',
 'Paket camping keluarga lengkap dengan tenda, matras, dan sleeping bag.',
 'Semua sudah tersedia: tenda untuk 4 orang, matras, sleeping bag, '
 'lantern, dan satu sesi bakar jagung bersama.',
 350000.00, '/unit', 2, 4, 10,
 NULL, FALSE, TRUE, 4),

-- ── INTERNAL: Homestay ─────────────────────────────────────
(5, NULL, 3,
 'Homestay Forest View',
 'homestay-forest-view',
 'Rumah kayu estetik yang nyaman untuk keluarga besar.',
 'Homestay 3 kamar tidur dengan living room luas, dapur bersama, '
 'teras dengan view hutan, dan kapasitas hingga 8 orang.',
 1300000.00, '/malam', 2, 8, 3,
 NULL, TRUE, TRUE, 5),

(6, NULL, 3,
 'Homestay Riverside',
 'homestay-riverside',
 'Homestay dengan akses langsung ke tepi sungai, cocok untuk keluarga.',
 '2 kamar tidur, living room, dapur lengkap, teras tepi sungai. '
 'Kapasitas maksimal 6 orang.',
 950000.00, '/malam', 2, 6, 2,
 NULL, FALSE, TRUE, 6),

-- ── INTERNAL: Outbound ─────────────────────────────────────
(7, NULL, 4,
 'Outbound Corporate Package',
 'outbound-corporate-package',
 'Paket outbound team building untuk perusahaan, minimal 20 orang.',
 'Full-day outbound dengan 8 game challenges, fasilitator profesional, '
 'makan siang, snack, sertifikat, dan dokumentasi foto & video.',
 250000.00, '/orang', 20, 100, 2,
 8.0, FALSE, TRUE, 7),

(8, NULL, 4,
 'Outbound School Trip',
 'outbound-school-trip',
 'Paket outbound edukatif untuk pelajar SD–SMA, minimal 30 orang.',
 'Program outbound edukatif dengan permainan yang menyenangkan dan '
 'edukatif, cocok untuk school trip dan study tour.',
 175000.00, '/orang', 30, 200, 1,
 6.0, FALSE, TRUE, 8),

-- ── INTERNAL: Catering ─────────────────────────────────────
(9, NULL, 5,
 'Paket Catering BBQ',
 'paket-catering-bbq',
 'Paket bakar-bakaran malam hari lengkap dengan daging, sayuran, dan minuman.',
 'Termasuk: daging ayam, sapi, jagung, ubi, sambal, lalapan, nasi, '
 'dan minuman teh/air mineral. Peralatan BBQ disediakan.',
 85000.00, '/orang', 10, 200, 999,
 NULL, FALSE, TRUE, 9),

(10, NULL, 5,
 'Paket Catering Nasi Box',
 'paket-catering-nasi-box',
 'Nasi box pilihan menu masakan Sunda untuk makan siang/malam.',
 'Menu: nasi putih, ayam goreng/bakar, lalapan, sambal, tempe, '
 'tahu, dan air mineral. Tersedia pilihan menu vegetarian.',
 45000.00, '/orang', 20, 500, 999,
 NULL, FALSE, TRUE, 10),

-- ── ADD-ON: Mitra Rafting ───────────────────────────────────
(11, 2, 6,
 'Rafting Ciater Adventure',
 'rafting-ciater-adventure',
 'Paket rafting seru di aliran sungai Ciater dekat kawasan Capolaga.',
 'Arung jeram sepanjang 8 km di Sungai Ciater dengan jeram kelas II-III. '
 'Termasuk: helm, pelampung, paddle, pemandu, dan asuransi jiwa.',
 200000.00, '/orang', 4, 20, 10,
 2.5, TRUE, TRUE, 11),

(12, 2, 6,
 'Rafting Ciater Full Day',
 'rafting-ciater-full-day',
 'Paket rafting full day dengan makan siang dan dokumentasi.',
 'Rafting 12 km + makan siang di pinggir sungai + foto profesional. '
 'Durasi total sekitar 5 jam termasuk briefing dan perjalanan.',
 350000.00, '/orang', 4, 16, 6,
 5.0, FALSE, TRUE, 12),

-- ── ADD-ON: Mitra Sari Ater ─────────────────────────────────
(13, 1, 7,
 'Tiket Pemandian Sari Ater',
 'tiket-sari-ater',
 'Tiket masuk pemandian air panas alam Sari Ater untuk satu hari.',
 'Akses ke seluruh fasilitas kolam renang air panas alam, area bermain '
 'anak, dan taman wisata Sari Ater.',
 100000.00, '/orang', 1, 50, 200,
 NULL, FALSE, TRUE, 13),

(14, 1, 7,
 'Paket Rendam & Relax Sari Ater',
 'paket-rendam-relax-sari-ater',
 'Paket premium: tiket masuk + sewa kolam privat 2 jam + snack.',
 'Sewa kolam privat 2 jam untuk rombongan 4–8 orang + tiket masuk + '
 'paket snack tradisional.',
 250000.00, '/orang', 4, 8, 20,
 2.0, FALSE, TRUE, 14),

-- ── ADD-ON: Mitra Paralayang ────────────────────────────────
(15, 3, 8,
 'Paralayang Tandem Santiong',
 'paralayang-tandem-santiong',
 'Terbang paralayang tandem bersama pilot berpengalaman di Bukit Santiong.',
 'Pengalaman terbang paralayang tandem dari ketinggian 1.200 mdpl dengan '
 'pemandangan Subang 360°. Pilot bersertifikat FASI. Termasuk asuransi.',
 350000.00, '/orang', 1, 1, 8,
 0.5, TRUE, TRUE, 15),

-- ── ADD-ON: Mitra ATV ───────────────────────────────────────
(16, 4, 9,
 'ATV Adventure Upas Hill',
 'atv-adventure-upas-hill',
 'Berkendara ATV melewati jalur off-road di kebun teh Upas Hill.',
 'Jalur ATV 3 km melewati perkebunan teh dan hutan pinus. '
 'Tersedia ATV untuk 1 orang dan 2 orang. Pemandu & helm disediakan.',
 150000.00, '/orang', 1, 2, 8,
 1.0, FALSE, TRUE, 16),

(17, 4, 9,
 'Landy Tour Upas Hill',
 'landy-tour-upas-hill',
 'Tour keliling perkebunan teh menggunakan Land Rover klasik.',
 'Jelajahi keindahan perkebunan teh Upas Hill dengan Land Rover klasik '
 'bersama pemandu wisata. Kapasitas 6 orang per unit.',
 120000.00, '/orang', 4, 6, 4,
 1.5, FALSE, TRUE, 17),

-- ── ADD-ON: Mitra Canyoneering ──────────────────────────────
(18, 5, 10,
 'Canyoneering Dayang Sumbi',
 'canyoneering-dayang-sumbi',
 'Penelusuran canyon dan terjun ke air terjun Dayang Sumbi.',
 'Menyusuri aliran sungai, melewati tebing, dan menikmati air terjun '
 'Dayang Sumbi bersama pemandu profesional. Semua peralatan disediakan.',
 225000.00, '/orang', 4, 12, 4,
 4.0, FALSE, TRUE, 18);


-- -------------------------------------------------------------
-- 6. PRODUCT_IMAGES — Gambar produk (placeholder path)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_images (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  BIGINT UNSIGNED NOT NULL,
    image_path  VARCHAR(500) NOT NULL,
    alt_text    VARCHAR(200),
    is_primary  BOOLEAN NOT NULL DEFAULT FALSE,
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_img_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO product_images (product_id, image_path, alt_text, is_primary, sort_order) VALUES
(1,  'products/glamping-riverside-luxury-1.jpg',  'Glamping Riverside Luxury tampak depan',  TRUE,  1),
(1,  'products/glamping-riverside-luxury-2.jpg',  'Interior tenda glamping riverside',        FALSE, 2),
(2,  'products/glamping-forest-view-1.jpg',       'Glamping Forest View di antara pinus',    TRUE,  1),
(3,  'products/standard-camping-ground-1.jpg',    'Area camping ground Capolaga',            TRUE,  1),
(4,  'products/family-camping-package-1.jpg',     'Paket tenda keluarga',                    TRUE,  1),
(5,  'products/homestay-forest-view-1.jpg',       'Homestay kayu Forest View',               TRUE,  1),
(5,  'products/homestay-forest-view-2.jpg',       'Kamar tidur homestay',                    FALSE, 2),
(6,  'products/homestay-riverside-1.jpg',         'Homestay tepi sungai',                    TRUE,  1),
(7,  'products/outbound-corporate-1.jpg',         'Sesi outbound corporate',                 TRUE,  1),
(8,  'products/outbound-school-1.jpg',            'Outbound school trip',                    TRUE,  1),
(9,  'products/catering-bbq-1.jpg',               'Paket BBQ malam',                         TRUE,  1),
(10, 'products/catering-nasi-box-1.jpg',          'Nasi box masakan Sunda',                  TRUE,  1),
(11, 'products/rafting-ciater-1.jpg',             'Arung jeram Sungai Ciater',               TRUE,  1),
(11, 'products/rafting-ciater-2.jpg',             'Tim rafting di jeram',                    FALSE, 2),
(12, 'products/rafting-fullday-1.jpg',            'Rafting full day dengan makan siang',     TRUE,  1),
(13, 'products/sari-ater-kolam-1.jpg',            'Kolam renang air panas Sari Ater',        TRUE,  1),
(14, 'products/sari-ater-privat-1.jpg',           'Kolam privat Sari Ater',                  TRUE,  1),
(15, 'products/paralayang-santiong-1.jpg',        'Paralayang tandem Bukit Santiong',        TRUE,  1),
(16, 'products/atv-upas-hill-1.jpg',              'ATV di perkebunan teh Upas Hill',         TRUE,  1),
(17, 'products/landy-upas-hill-1.jpg',            'Landy tour Upas Hill',                    TRUE,  1),
(18, 'products/canyoneering-dayang-1.jpg',        'Canyoneering air terjun Dayang Sumbi',    TRUE,  1);


-- -------------------------------------------------------------
-- 7. PAYMENT_METHODS — Gateway dan metode pembayaran
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_methods (
    id           TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL UNIQUE,
    code         VARCHAR(50)  NOT NULL UNIQUE,
    provider     ENUM('midtrans','xendit','manual') NOT NULL DEFAULT 'midtrans',
    type         ENUM('va','ewallet','qris','cc','cstore','manual') NOT NULL,
    logo_path    VARCHAR(255),
    fee_flat     DECIMAL(10,2) NOT NULL DEFAULT 0.00
                 COMMENT 'Biaya tetap per transaksi (Rp)',
    fee_percent  DECIMAL(5,3) NOT NULL DEFAULT 0.000
                 COMMENT 'Biaya persentase per transaksi (%)',
    min_amount   DECIMAL(12,2) NOT NULL DEFAULT 10000.00,
    max_amount   DECIMAL(12,2) NULL
                 COMMENT 'NULL = tidak ada batas',
    is_active    BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order   TINYINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO payment_methods
    (id, name, code, provider, type, fee_flat, fee_percent, min_amount, max_amount, is_active, sort_order)
VALUES
(1,  'QRIS',               'QRIS',         'midtrans', 'qris',    0.00,    0.700,  10000.00,  10000000.00, TRUE,  1),
(2,  'GoPay',              'GOPAY',        'midtrans', 'ewallet', 0.00,    2.000,  10000.00,  10000000.00, TRUE,  2),
(3,  'OVO',                'OVO',          'midtrans', 'ewallet', 0.00,    2.000,  10000.00,  10000000.00, TRUE,  3),
(4,  'Dana',               'DANA',         'midtrans', 'ewallet', 0.00,    2.000,  10000.00,  10000000.00, TRUE,  4),
(5,  'ShopeePay',          'SHOPEEPAY',    'midtrans', 'ewallet', 0.00,    2.000,  10000.00,  10000000.00, TRUE,  5),
(6,  'BCA Virtual Account','BCA_VA',       'midtrans', 'va',      4000.00, 0.000,  10000.00,  NULL,        TRUE,  6),
(7,  'BNI Virtual Account','BNI_VA',       'midtrans', 'va',      4000.00, 0.000,  10000.00,  NULL,        TRUE,  7),
(8,  'BRI Virtual Account','BRI_VA',       'midtrans', 'va',      4000.00, 0.000,  10000.00,  NULL,        TRUE,  8),
(9,  'Mandiri Virtual Acc','MANDIRI_VA',   'midtrans', 'va',      4000.00, 0.000,  10000.00,  NULL,        TRUE,  9),
(10, 'Permata VA',         'PERMATA_VA',   'midtrans', 'va',      4000.00, 0.000,  10000.00,  NULL,        TRUE, 10),
(11, 'Kartu Kredit/Debit', 'CREDIT_CARD',  'midtrans', 'cc',      0.00,    2.900,  10000.00,  NULL,        TRUE, 11),
(12, 'Indomaret',          'INDOMARET',    'midtrans', 'cstore',  5000.00, 0.000,  10000.00,  5000000.00,  TRUE, 12),
(13, 'Alfamart',           'ALFAMART',     'midtrans', 'cstore',  5000.00, 0.000,  10000.00,  5000000.00,  TRUE, 13),
(14, 'Transfer Manual',    'MANUAL_TF',    'manual',   'manual',  0.00,    0.000,  10000.00,  NULL,        FALSE, 14);


-- -------------------------------------------------------------
-- 8. COMMISSION_TIERS — Tingkatan komisi berdasarkan volume
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS commission_tiers (
    id                    TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                  VARCHAR(100) NOT NULL,
    min_monthly_revenue   DECIMAL(14,2) NOT NULL DEFAULT 0.00
                          COMMENT 'Minimum omzet bulanan mitra (Rp) untuk tier ini',
    commission_rate       DECIMAL(5,2) NOT NULL
                          COMMENT 'Tarif komisi platform (%)',
    subscription_discount DECIMAL(5,2) NOT NULL DEFAULT 0.00
                          COMMENT 'Diskon biaya subscription (%)',
    description           VARCHAR(300),
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO commission_tiers (id, name, min_monthly_revenue, commission_rate, subscription_discount, description) VALUES
(1, 'Starter',   0.00,          10.00, 0.00,  'Mitra baru, omzet s.d. Rp 5 juta/bulan'),
(2, 'Silver',    5000000.00,     8.00, 10.00, 'Omzet Rp 5–20 juta/bulan'),
(3, 'Gold',      20000000.00,    6.00, 20.00, 'Omzet Rp 20–50 juta/bulan'),
(4, 'Platinum',  50000000.00,    5.00, 30.00, 'Omzet di atas Rp 50 juta/bulan');


-- -------------------------------------------------------------
-- 9. PROMO_TYPES — Jenis promosi yang tersedia di platform
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS promo_types (
    id           TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL UNIQUE,
    code         VARCHAR(50)  NOT NULL UNIQUE,
    description  VARCHAR(300),
    discount_type ENUM('percent','fixed','free_addon','early_bird','bundle') NOT NULL,
    is_active    BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO promo_types (id, name, code, description, discount_type) VALUES
(1, 'Diskon Persentase',    'PERCENT',     'Potongan harga dalam persen dari total',             'percent'),
(2, 'Diskon Nominal',       'FIXED',       'Potongan harga langsung dalam rupiah',               'fixed'),
(3, 'Gratis Add-On',        'FREE_ADDON',  'Mendapatkan satu add-on activity secara gratis',     'free_addon'),
(4, 'Early Bird',           'EARLY_BIRD',  'Diskon khusus untuk pemesanan jauh hari (H-30+)',    'early_bird'),
(5, 'Paket Bundling',       'BUNDLE',      'Diskon saat memesan 2 atau lebih produk sekaligus',  'bundle');


-- -------------------------------------------------------------
-- 10. ACTIVITY_TAGS — Tag/label untuk filter dan pencarian
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_tags (
    id         SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL UNIQUE,
    slug       VARCHAR(100) NOT NULL UNIQUE,
    group_name VARCHAR(50)  NOT NULL
               COMMENT 'Kelompok tag: audience|difficulty|facility|theme',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO activity_tags (id, name, slug, group_name) VALUES
-- Audience / Target pasar
(1,  'Keluarga',          'keluarga',         'audience'),
(2,  'Pasangan',          'pasangan',         'audience'),
(3,  'Grup Pertemanan',   'grup-pertemanan',  'audience'),
(4,  'Corporate / Team',  'corporate-team',   'audience'),
(5,  'Pelajar & Mahasiswa','pelajar',         'audience'),
(6,  'Solo Traveler',     'solo-traveler',    'audience'),
-- Difficulty / Tingkat kesulitan
(7,  'Mudah',             'mudah',            'difficulty'),
(8,  'Menengah',          'menengah',         'difficulty'),
(9,  'Menantang',         'menantang',        'difficulty'),
-- Fasilitas
(10, 'Inklusif Sarapan',  'inklusif-sarapan', 'facility'),
(11, 'Inklusif Makan',    'inklusif-makan',   'facility'),
(12, 'Parkir Luas',       'parkir-luas',      'facility'),
(13, 'Pet Friendly',      'pet-friendly',     'facility'),
(14, 'WiFi Tersedia',     'wifi',             'facility'),
(15, 'Kolam Renang',      'kolam-renang',     'facility'),
-- Tema
(16, 'Alam & Petualangan','alam-petualangan', 'theme'),
(17, 'Relaksasi',         'relaksasi',        'theme'),
(18, 'Adrenaline Rush',   'adrenaline',       'theme'),
(19, 'Edukasi',           'edukasi',          'theme'),
(20, 'Romantis',          'romantis',         'theme'),
(21, 'Instagramable',     'instagramable',    'theme'),
(22, 'Eco Tourism',       'eco-tourism',      'theme');


-- -------------------------------------------------------------
-- 11. PRODUCT_ACTIVITY_TAGS — Pivot produk ↔ tag
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_activity_tags (
    product_id BIGINT UNSIGNED    NOT NULL,
    tag_id     SMALLINT UNSIGNED  NOT NULL,
    PRIMARY KEY (product_id, tag_id),
    CONSTRAINT fk_pat_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_pat_tag     FOREIGN KEY (tag_id)     REFERENCES activity_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO product_activity_tags (product_id, tag_id) VALUES
-- Glamping Riverside Luxury
(1, 1),(1, 2),(1, 10),(1, 16),(1, 17),(1, 20),(1, 21),
-- Glamping Forest View
(2, 1),(2, 2),(2, 10),(2, 16),(2, 17),(2, 21),
-- Standard Camping Ground
(3, 1),(3, 3),(3, 5),(3, 7),(3, 12),(3, 16),(3, 22),
-- Family Camping Package
(4, 1),(4, 5),(4, 7),(4, 12),(4, 16),(4, 22),
-- Homestay Forest View
(5, 1),(5, 12),(5, 16),(5, 21),
-- Homestay Riverside
(6, 1),(6, 2),(6, 12),(6, 16),(6, 17),
-- Outbound Corporate
(7, 4),(7, 8),(7, 16),(7, 19),
-- Outbound School Trip
(8, 5),(8, 7),(8, 16),(8, 19),
-- Catering BBQ
(9, 1),(9, 3),(9, 4),
-- Catering Nasi Box
(10, 1),(10, 3),(10, 4),(10, 5),
-- Rafting Ciater Adventure
(11, 3),(11, 6),(11, 8),(11, 16),(11, 18),
-- Rafting Full Day
(12, 3),(12, 6),(12, 11),(12, 16),(12, 18),
-- Tiket Sari Ater
(13, 1),(13, 7),(13, 15),(13, 17),
-- Paket Rendam Relax
(14, 1),(14, 2),(14, 7),(14, 15),(14, 17),(14, 20),
-- Paralayang Tandem
(15, 2),(15, 3),(15, 9),(15, 16),(15, 18),(15, 21),
-- ATV Upas Hill
(16, 3),(16, 8),(16, 16),(16, 18),
-- Landy Tour
(17, 1),(17, 2),(17, 7),(17, 16),(17, 21),
-- Canyoneering Dayang Sumbi
(18, 3),(18, 6),(18, 9),(18, 16),(18, 18);


-- =============================================================
-- RINGKASAN DATA MASTER
-- =============================================================
-- roles                : 4 record
-- users                : 7 record (1 superadmin, 1 admin, 5 mitra)
-- mitra_profiles       : 5 record
-- product_categories   : 10 record (5 internal, 5 addon)
-- products             : 18 record (10 internal, 8 addon)
-- product_images       : 21 record
-- payment_methods      : 14 record
-- commission_tiers     : 4 record
-- promo_types          : 5 record
-- activity_tags        : 22 record
-- product_activity_tags: 76 record (pivot)
-- =============================================================

SET FOREIGN_KEY_CHECKS = 1;
