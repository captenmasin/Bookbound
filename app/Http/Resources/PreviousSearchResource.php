<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreviousSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'search_term' => $this->search_term,
            'search_term_normalised' => $this->normalise($this->search_term),
            'type' => $this->type,
        ];
    }

    private function normalise(mixed $search_term)
    {
        $output = $search_term;
        $output = str_replace('tag:', '', $output);
        $output = str_replace('author:', '', $output);

        return $output;
    }
}
