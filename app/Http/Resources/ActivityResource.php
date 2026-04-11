<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $description = $request->routeIs('profiles.show')
            ? $this->publicDescription()
            : $this->description;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $description,
            'created_at' => $this->created_at,
        ];
    }
}
