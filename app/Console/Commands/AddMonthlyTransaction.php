<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AddMonthlyTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-monthly-transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menambahkan transaksi bulanan secara otomatis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Transaction::all()->map(function ($transaction) {
            if ($transaction->recurring == 1) {
                Transaction::create([
                    'user_id'     => $transaction->user_id,
                    'wallet_id'   => $transaction->wallet_id,
                    'category_id' => $transaction->category_id,
                    'amount'      => $transaction->amount,
                    'description' => $transaction->description,
                    'recurring'   => 2,
                ]);
            }
        });
    }
}
