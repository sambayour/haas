<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'email_verified_at' => $this->email_verified_at,
            'type' => $this->getLevel($this->level),
            'created_at' => $this->created_at ? date('F d, Y', strtotime($this->created_at)) : null,
            'updated_at' => $this->updated_at ? date('F d, Y', strtotime($this->updated_at)) : null,
        ];
    }
}
