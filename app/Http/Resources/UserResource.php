<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            'name' => $this->name,
            'username' => $this->username,
            'avatar' => $this->avatar,
            'email_verified' => $this->email_verified_at !== null,
            'colour' => $this->settings()->get('profile.colour', '#000000'),
        ];
    }

    public function asUser(): array
    {
        $data = $this->toArray(request());

        $data['email'] = $this->email;
        $data['settings'] = $this->settings()->all();
        $data['permissions'] = Cache::remember('user_'.$this->id.'_permissions', now()->addHour(), function () {
            return $this->getAllPermissions()->pluck('name')->toArray();
        });
        $data['book_identifiers'] = $this->getBookIdentifiers();

        return $data;
    }
}
