<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'initial_balance'   => $this->initial_balance,
            'total_income'      => $this->total_income ?? 0,
            'total_outcome'     => $this->total_outcome ?? 0,
            'total_balance'     => $this->total_balance ?? (($this->initial_balance ?? 0) + ($this->total_income ?? 0) - ($this->total_outcome ?? 0)),
            'is_active'         => $this->is_active,
            'transactions'      => $this->transactions->map(function ($transaction){
                return [
                    'category'              => $transaction->category->name,
                    'transaction_type'      => $transaction->category->type,
                    'date'                  => $transaction->created_at->format('d M Y'),
                    'description'           => $transaction->description,
                    'amount'                => $transaction->amount,
                ];
            }),
        ];
    }
}
