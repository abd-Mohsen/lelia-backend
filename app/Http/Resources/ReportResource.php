<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
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
            'title' => $this->title,
            'type' => $this->type,
            'size' => $this->size,
            'neighborhood' => $this->neighborhood,
            'street' => $this->street,
            'landline_number' => $this->landline_number,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'status' => $this->status,
            'date' => $this->date,
            'notes' => $this->notes,
            'owner' => new UserResource($this->owner),
        ];
    }
}
