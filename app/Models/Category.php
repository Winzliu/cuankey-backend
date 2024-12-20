<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'user_id',
        'description',
        'budget',
        'type'
    ];
    // set relasi category dengan tabel lain
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurring()
    {
        return $this->hasMany(Recurring::class);
    }
}
