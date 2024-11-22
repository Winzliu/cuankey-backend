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
            'id'                => $this->id,
            'wallet'            => $this->wallet->name,
            'category'          => $this->category->name,
            'amount'            => $this->amount,
            'description'       => $this->description,
            'transaction_date'  => $this->transaction_date,
            'user'              => [
                'fullname'      => $this->user->fullname,
                'phone_number'  => $this->user->phone_number,
                'email'         => $this->user->email,
            ],
        ];
    }
}
