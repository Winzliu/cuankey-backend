<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    // Mengembalikan data transaksi perulangan dalam bentuk array yang sudah diformat.

        return [
            'id'          => $this->id,
            'user'        => [
                'fullname'     => $this->user->fullname,
                'phone_number' => $this->user->phone_number,
                'email'        => $this->user->email
            ],
            'wallet'      => [
                'name'      => $this->wallet->name,
                'is_active' => $this->wallet->is_active
            ],
            'category'    => [
                'name'        => $this->category->name,
                'description' => $this->category->description,
                'budget'      => $this->category->budget,
                'type'        => $this->category->type
            ],
            'amount'      => $this->amount,
            'is_active'   => $this->is_active,
            'description' => $this->description
        ];
    }
}
