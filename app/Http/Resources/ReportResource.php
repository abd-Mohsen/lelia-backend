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
        $translation = [
            "small" => "صغير",
            "medium" => "وسط",
            "big" => "كبير",
            "retail" => "بائع جملة",
            "mall" => "مركز تجاري (مول)",
            "pharmacy" => "صيدلية",
            "supermarket" => "بقالية",
        ];

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $translation[$this->type],
            'size' => $translation[$this->size],
            'neighborhood' => $this->neighborhood,
            'street' => $this->street,
            'landline_number' => $this->landline_number,
            'mobile_number' => $this->mobile_number,
            'longitude' => (double) $this->longitude,
            'latitude' => (double) $this->latitude,
            'status' => $this->status,
            'issue_date' => $this->issue_date,
            'notes' => $this->notes,
            'owner' => new UserResource($this->owner),
            'images' => ImageResource::collection($this->images),
        ];
    }
}
