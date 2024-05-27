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
        $translation = [
            "supervisor" => "مشرف",
            "salesman" => "مندوب مبيعات",
            "admin" => "مسؤول",
        ];

        return [
            'id' => $this->id,
            'user_name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $translation[$this->role->title],
            'supervisor' => new UserResource($this->supervisor),
            'is_verified' => $this->hasVerifiedEmail(), 
        ];
    }
}
