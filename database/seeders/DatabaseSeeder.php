<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RbacSeeder::class);

        // Super Admin default
        $user = User::firstOrCreate(['email' => 'superadmin@capolaga.com'], [
            'name'     => 'Super Admin Capolaga',
            'password' => 'password123',
        ]);
        $user->syncRoles(['Super Admin']);

        // Admin operasional
        $admin = User::firstOrCreate(['email' => 'admin@capolaga.com'], [
            'name'     => 'Admin Operasional',
            'password' => 'password123',
        ]);
        $admin->syncRoles(['Super Admin']);

        // Master data (kategori, tag, payment, mitra, produk, dll)
        $this->call(MasterDataSeeder::class);
    }
}
