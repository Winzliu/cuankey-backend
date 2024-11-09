<?php

namespace Database\Seeders;

use App\Models\category;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'id'       => 0,
            'username' => 'Admin',
            'email'    => 'admin@gmail.com',
            'password' => bcrypt('password123'),
        ]);

        category::factory()->create([
            'name'        => 'Makanan',
            'description' => 'Pengeluaran untuk makanan',
            'budget'      => null,
            'type'        => 'Pengeluaran',
            'user_id'     => 0
        ]);

        category::factory()->create([
            'name'        => 'Gaji',
            'description' => 'Pemasukan dari gaji',
            'budget'      => null,
            'type'        => 'Pemasukan',
            'user_id'     => 0
        ]);

        category::factory()->create([
            'name'        => 'Transportasi',
            'description' => 'Pengeluaran untuk transportasi',
            'budget'      => null,
            'type'        => 'Pengeluaran',
            'user_id'     => 0
        ]);
    }
}
