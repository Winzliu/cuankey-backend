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
        return [
            "status"  => "success",
            "code"    => 200,
            "message" => $this->message ?? null,
            "data"    => [
                'id'       => $this->id,
                'username' => $this->username,
                'email'    => $this->email,
                'token'    => $this->whenNotNull($this->token)
            ],
        ];
    }
}
