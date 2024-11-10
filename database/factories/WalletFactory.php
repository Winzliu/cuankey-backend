<?php

namespace Database\Factories;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition()
    {
        return [
            'name' => fake()->name(),
            'balance' => fake()->randomFloat(2, 0, 10000), 
            'is_active' => true,
            'user_id' => 0
        ];
    }
}
