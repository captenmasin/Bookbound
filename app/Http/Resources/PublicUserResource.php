<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Enums\UserBookStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicUserResource extends JsonResource
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
            'name' => $this->name,
            'username' => $this->username,
            'avatar' => $this->avatar,
            'colour' => $this->settings()->get('profile.colour', '#000000'),
            'books_count' => $this->books()->count(),
            'books_read_count' => $this->books()
                ->wherePivot('status', UserBookStatus::Read->value)
                ->count(),
        ];
    }
}
