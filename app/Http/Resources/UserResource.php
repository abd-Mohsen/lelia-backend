<?php

namespace App\Http\Resources;

use App\Models\User;
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
            'id' => $this->id,
            'user_name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'supervisor' => new UserResource($this->supervisor),
            'is_verified' => $this->hasVerifiedEmail(),
        ];
    }
}
