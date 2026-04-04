<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MitraSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama (urutan penting karena FK)
        DB::table('mitra_profiles')->whereIn('user_id', function ($q) {
            $q->select('id')->from('users')
              ->whereIn('email', [
                  'mitra.sariater@email.com',
                  'mitra.rafting@email.com',
                  'mitra.paralayang@email.com',
                  'mitra.atv@email.com',
                  'mitra.canyon@email.com',
              ]);
        })->delete();

        // Buat user mitra (gunakan firstOrCreate agar idempotent)
        $mitras = [
            [
                'name'     => 'Sari Ater Hot Spring',
                'email'    => 'mitra.sariater@email.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name'     => 'Ciater Rafting Center',
                'email'    => 'mitra.rafting@email.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name'     => 'Paralayang Santiong',
                'email'    => 'mitra.paralayang@email.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name'     => 'ATV Upas Hill',
                'email'    => 'mitra.atv@email.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name'     => 'Canyoneering Dayang',
                'email'    => 'mitra.canyon@email.com',
                'password' => Hash::make('password123'),
            ],
        ];

        $userIds = [];
        foreach ($mitras as $data) {
            $user = User::firstOrCreate(['email' => $data['email']], [
                'name'     => $data['name'],
                'password' => $data['password'],
            ]);
            $user->syncRoles(['Mitra']);
            $userIds[$data['email']] = $user->id;
        }

        // Insert mitra_profiles
        DB::table('mitra_profiles')->insert([
            [
                'user_id'           => $userIds['mitra.sariater@email.com'],
                'business_name'     => 'Sari Ater Hot Spring Resort',
                'slug'              => 'sari-ater-hot-spring',
                'description'       => 'Pemandian air panas alam terbesar di Subang dengan kolam renang dan villa.',
                'address'           => 'Jl. Raya Ciater, Ciater, Subang, Jawa Barat',
                'latitude'          => -6.7234567,
                'longitude'         => 107.7654321,
                'contact_person'    => 'Budi Santoso',
                'whatsapp'          => '081211110001',
                'commission_rate'   => 8.00,
                'subscription_type' => 'premium',
                'status'            => 'active',
                'joined_at'         => '2024-01-15',
            ],
            [
                'user_id'           => $userIds['mitra.rafting@email.com'],
                'business_name'     => 'Ciater Rafting Adventure',
                'slug'              => 'ciater-rafting-adventure',
                'description'       => 'Paket arung jeram di Sungai Ciater dengan pemandu berpengalaman dan peralatan safety lengkap.',
                'address'           => 'Jl. Sungai Ciater, Subang, Jawa Barat',
                'latitude'          => -6.7312345,
                'longitude'         => 107.7543210,
                'contact_person'    => 'Agus Rahayu',
                'whatsapp'          => '081211110002',
                'commission_rate'   => 10.00,
                'subscription_type' => 'basic',
                'status'            => 'active',
                'joined_at'         => '2024-02-01',
            ],
            [
                'user_id'           => $userIds['mitra.paralayang@email.com'],
                'business_name'     => 'Paralayang Santiong',
                'slug'              => 'paralayang-santiong',
                'description'       => 'Olahraga paralayang dengan pemandangan alam Subang dari ketinggian 1.200 mdpl.',
                'address'           => 'Bukit Santiong, Subang, Jawa Barat',
                'latitude'          => -6.7198765,
                'longitude'         => 107.7812345,
                'contact_person'    => 'Dedi Wijaya',
                'whatsapp'          => '081211110003',
                'commission_rate'   => 10.00,
                'subscription_type' => 'basic',
                'status'            => 'active',
                'joined_at'         => '2024-03-10',
            ],
            [
                'user_id'           => $userIds['mitra.atv@email.com'],
                'business_name'     => 'ATV & Landy Upas Hill',
                'slug'              => 'atv-upas-hill',
                'description'       => 'Petualangan off-road dengan ATV dan Landy di kawasan perkebunan teh Upas Hill.',
                'address'           => 'Kawasan Upas Hill, Subang, Jawa Barat',
                'latitude'          => -6.7456789,
                'longitude'         => 107.7234567,
                'contact_person'    => 'Rudi Hermawan',
                'whatsapp'          => '081211110004',
                'commission_rate'   => 10.00,
                'subscription_type' => 'basic',
                'status'            => 'active',
                'joined_at'         => '2024-04-05',
            ],
            [
                'user_id'           => $userIds['mitra.canyon@email.com'],
                'business_name'     => 'Canyoneering Dayang Sumbi',
                'slug'              => 'canyoneering-dayang-sumbi',
                'description'       => 'Penelusuran canyon dan air terjun Dayang Sumbi dengan pemandu profesional.',
                'address'           => 'Kawasan Dayang Sumbi, Subang, Jawa Barat',
                'latitude'          => -6.7567890,
                'longitude'         => 107.7345678,
                'contact_person'    => 'Eko Prasetyo',
                'whatsapp'          => '081211110005',
                'commission_rate'   => 10.00,
                'subscription_type' => 'free',
                'status'            => 'active',
                'joined_at'         => '2024-05-20',
            ],
        ]);
    }
}
