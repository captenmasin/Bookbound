<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\PublicUserResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicProfileController extends Controller
{
    public function __invoke(User $user): Response
    {
        $viewer = request()->user();
        $isOwner = $viewer?->is($user) ?? false;
        $isPrivate = (bool) $user->settings()->get('profile.is_private', false);

        if ($isPrivate && ! $isOwner) {
            throw new NotFoundHttpException;
        }

        $reviews = $user->reviews()
            ->with(['book.authors', 'book.covers', 'book.ratings', 'user'])
            ->orderByDesc('created_at')
            ->paginate(10, pageName: 'reviews_page')
            ->withQueryString();

        $activities = $user->activities()
            ->orderByDesc('created_at')
            ->paginate(10, pageName: 'activities_page')
            ->withQueryString();

        return Inertia::render('profiles/Show', [
            'user' => new PublicUserResource($user),
            'reviews' => ReviewResource::collection($reviews),
            'activities' => ActivityResource::collection($activities),
            'is_owner' => $isOwner,
        ])->withMeta([
            'title' => "{$user->name} (@{$user->username})",
            'description' => "Public profile for {$user->name}.",
        ]);
    }
}
