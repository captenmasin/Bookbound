<?php

namespace App\Http\Resources;

use Str;
use App\Models\User;
use Illuminate\Http\Request;
use App\Support\SubscriptionLimits;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        $description = $this->description ?? '';
        $description = str_replace('\n', "\n", $description);

        return [
            'id' => $this->id,
            'path' => $this->path,
            'identifier' => $this->identifier,
            'title' => html_entity_decode($this->title),
            'description' => $description,
            'description_clean' => strip_tags($description),
            'published_date' => Str::before($this->published_date, ' '),
            'tags' => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
            'page_count' => $this->page_count,

            'has_custom_cover' => $user ? $this->hasCustomCover($user) : false,
            'cover' => $this->getCover($user),
            'authors' => $this->whenLoaded('authors', fn () => $this->getAuthors()),
            'publisher' => $this->whenLoaded('publisher', fn () => $this->getPublisher()),

            'user_notes' => $this->whenLoaded('notes', fn () => $this->getUserNotes($user)),
            'user_review' => $this->whenLoaded('reviews', fn () => $this->getUserReview($user)),
            'user_rating' => $this->whenLoaded('ratings', fn () => $this->getUserRating($user)),

            'average_rating' => $this->whenLoaded('ratings', fn () => $this->ratings->avg('value')),
            'ratings_count' => $this->whenLoaded('ratings', fn () => $this->ratings->count()),

            'in_library' => $user && $this->isInLibrary($user),
            'user_status' => $user ? $this->getUserStatus($user) : null,
            'user_tags' => $user ? $this->getUserTags($user) : [],

            'edition' => $this->edition,
            'binding' => $this->normalizeBinding($this->binding),
            'language' => $this->language,

            'colour' => $this->settings()->get('colour', '#000000'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'links' => [
                'show' => route('books.show', $this),
            ],
        ];
    }

    protected function getAuthors(): AnonymousResourceCollection
    {
        return AuthorResource::collection($this->authors);
    }

    protected function getPublisher(): PublisherResource
    {
        return new PublisherResource($this->publisher);
    }

    protected function getCover(?User $user = null): ?string
    {
        if (! $user || ! $this->hasCustomCover($user)) {
            return $this->primary_cover;
        }

        $cover = $user->book_covers->where('book_id', $this->id)->last();

        return $cover?->hasMedia('image')
            ? $cover->image
            : $this->primary_cover;
    }

    protected function hasCustomCover(?User $user = null): bool
    {
        if (! SubscriptionLimits::allowCustomCovers($user)) {
            return false;
        }

        return $user->book_covers->contains('book_id', $this->id);
    }

    protected function getUserPivot(User $user)
    {
        if ($this->relationLoaded('users')) {
            return $this->users->firstWhere('id', $user->id)?->pivot;
        }

        return once(fn () => $this->users()->where('user_id', $user->id)->first()?->pivot);
    }

    protected function isInLibrary(User $user): bool
    {
        return (bool) $this->getUserPivot($user);
    }

    protected function getUserStatus(User $user): ?string
    {
        return $this->getUserPivot($user)?->status;
    }

    protected function getUserTags(User $user): ?array
    {
        return $this->getUserPivot($user)?->tags;
    }

    protected function getUserReview(?User $user = null): ?ReviewResource
    {
        if (! $user) {
            return null;
        }

        $review = $this->reviews->firstWhere('user_id', $user?->id)?->load('user');

        return $review ? new ReviewResource($review) : null;
    }

    protected function getUserRating(?User $user = null): ?RatingResource
    {
        if (! $user) {
            return null;
        }

        $rating = $this->ratings->firstWhere('user_id', $user?->id);

        return $rating ? new RatingResource($rating) : null;
    }

    protected function getUserNotes(?User $user = null): ?AnonymousResourceCollection
    {
        if (! $user) {
            return null;
        }

        $notes = $this->notes->where('user_id', $user?->id)->sortByDesc('created_at');

        return $notes ? NoteResource::collection($notes) : null;
    }

    private function normalizeBinding(mixed $binding): string
    {
        if (Str::contains($binding, 'unknown')) {
            return '';
        }

        $binding = Str::replace('Mass Market', '', $binding);
        $binding = Str::replace('School & Library Binding', 'Paperback', $binding);
        $binding = Str::replace('Edition', '', $binding);

        if ($binding === 'electronic resource') {
            return 'eBook';
        }

        if (strtolower($binding) === 'print') {
            return 'Paperback';
        }

        return ucfirst(strtolower($binding));
    }
}
