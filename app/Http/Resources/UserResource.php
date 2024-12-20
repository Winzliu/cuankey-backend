<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
     // Mengembalikan data user dalam bentuk array yang sudah diformat.

        return [
            'id'              => $this->id,
            'profile_picture' => $this->profile_picture,
            'fullname'        => $this->fullname,
            'phone_number'    => $this->phone_number,
            'email'           => $this->email,
            'token'           => $this->whenNotNull($this->token)
        ];
    }
}
