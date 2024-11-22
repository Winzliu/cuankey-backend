<?php

namespace Database\Seeders;

use App\Models\category;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
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
            'id'           => 0,
            'fullname'     => 'Admin',
            'phone_number' => '000000000',
            'email'        => 'admin@gmail.com',
            'password'     => bcrypt('password123'),
        ]);

        // category::factory()->create([
        //     'name'        => 'Makanan',
        //     'description' => 'Pengeluaran untuk makanan',
        //     'budget'      => null,
        //     'type'        => 'Pengeluaran',
        //     'user_id'     => 0
        // ]);

        // category::factory()->create([
        //     'name'        => 'Gaji',
        //     'description' => 'Pemasukan dari gaji',
        //     'budget'      => null,
        //     'type'        => 'Pemasukan',
        //     'user_id'     => 0
        // ]);

        // category::factory()->create([
        //     'name'        => 'Transportasi',
        //     'description' => 'Pengeluaran untuk transportasi',
        //     'budget'      => null,
        //     'type'        => 'Pengeluaran',
        //     'user_id'     => 0
        // ]);

        // Wallet::factory()->create([
        //     'name'      => 'Cash Wallet',
        //     'initial_balance' => 100000 ,
        //     'is_active' => true,
        //     'user_id'   => 0
        // ]);

        // Wallet::factory()->create([
        //     'name'      => 'Credit Card Wallet',
        //     'initial_balance' => 50000 ,
        //     'is_active' => false,
        //     'user_id'   => 0
        // ]);

        // Transaction::factory()->create([
        //     'user_id'     => 0,
        //     'wallet_id'   => 1,
        //     'category_id' => 1,
        //     'amount'      => 50000,
        //     'description' => 'Sample transaction for seeding',
        //     'transaction_date' => now()->format('d F Y')
        // ]);
        
        // Transaction::factory()->create([
        //     'user_id'     => 0,
        //     'wallet_id'   => 1,
        //     'category_id' => 2,
        //     'amount'      => 100000,
        //     'description' => 'Sample transaction for seeding 2',
        //     'transaction_date' => now()->format('d F Y')
        // ]);
    }
}
