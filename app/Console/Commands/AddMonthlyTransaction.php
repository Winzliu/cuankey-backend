<?php

namespace App\Console\Commands;

use App\Models\Recurring;
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
        Recurring::all()->map(function ($recurring) {
            if ($recurring->is_active == true) {
                Transaction::create([
                    'user_id'     => $recurring->user_id,
                    'wallet_id'   => $recurring->wallet_id,
                    'category_id' => $recurring->category_id,
                    'amount'      => $recurring->amount,
                    'description' => $recurring->description,
                ]);
            }
        });
    }
}
