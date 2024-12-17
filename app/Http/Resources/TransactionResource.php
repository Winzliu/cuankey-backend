<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'wallet'           => [
                'name'            => $this->wallet->name,
                'initial_balance' => $this->wallet->initial_balance,
                'total_income'    => $this->wallet->total_income ?? 0,
                'total_expense'   => $this->wallet->total_expense ?? 0,
                'total_balance'   => $this->wallet->total_balance ?? (($this->wallet->initial_balance ?? 0) + ($this->wallet->total_income ?? 0) - ($this->wallet->total_expense ?? 0)),
                'is_active'       => $this->wallet->is_active
            ],
            'category'         => [
                'name'        => $this->category->name,
                'description' => $this->category->description,
                'budget'      => $this->category->budget,
                'type'        => $this->category->type
            ],
            'amount'           => $this->amount,
            'description'      => $this->description,
            'transaction_date' => $this->transaction_date,
            'user'             => [
                'fullname'     => $this->user->fullname,
                'phone_number' => $this->user->phone_number,
                'email'        => $this->user->email,
            ],
        ];
    }
}
