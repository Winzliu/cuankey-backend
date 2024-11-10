<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'balance', 'is_active'];

    public static function userHasActiveWallet($userId)
    {
        return self::where('user_id', $userId)->where('is_active', true)->exists();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
