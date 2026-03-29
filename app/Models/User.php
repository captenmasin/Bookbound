<?php

namespace App\Models;

use Filament\Panel;
use App\Traits\HasAvatar;
use App\Enums\ActivityType;
use App\Enums\UserPermission;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BookResource;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

use function Illuminate\Events\queueable;

use Illuminate\Database\Eloquent\Builder;
use Filament\Models\Contracts\FilamentUser;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Actions\Books\GetBookRecommendations;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Glorand\Model\Settings\Traits\HasSettingsField;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;

class User extends Authenticatable implements FilamentUser, HasMedia, HasPasskeys, MustVerifyEmail
{
    use Billable, HasApiTokens, HasAvatar, HasFactory, HasRoles, HasSettingsField,
        InteractsWithMedia, InteractsWithPasskeys, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'remember_token',
        'email_verified_at',
        'password',
        'settings',
    ];

    protected $appends = [
        'avatar',
        'has_avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_avatar' => 'bool',
        ];
    }

    public array $defaultSettings = [
        'library' => [
            'view' => 'grid',
            'tilt_books' => true,
        ],
        'single_book' => [
            'default_section' => 'notes',
        ],

        'profile' => [
            'colour' => '#f2ead9',
        ],
    ];

    public function getSettingsRules(): array
    {
        return [
            'library' => 'array',
            'library.view' => ['string', 'in:grid,list,shelf'],
            'library.tilt_books' => ['boolean'],

            'single_book' => 'array',
            'single_book.default_section' => ['string', 'in:notes,reviews'],

            'profile' => 'array',
            'profile.colour' => ['string', 'hex_color'],
        ];
    }

    protected static function booted(): void
    {
        static::updated(queueable(function (User $customer) {
            if ($customer->hasStripeId()) {
                $customer->syncStripeCustomerDetails();
            }
        }));
    }

    public function getBookIdentifiers(): array
    {
        $books = $this->relationLoaded('books')
            ? $this->books
            : $this->books()->withPivot('status')->get();

        return $books->pluck('pivot.status', 'identifier')->toArray();
    }

    public function getAvatarAttribute(): string
    {
        return $this->getFirstMediaUrl('avatar');
    }

    public function getAuthors()
    {
        return Author::query()
            ->select('authors.*', DB::raw('count(*) as book_count'))
            ->join('author_book', 'authors.id', '=', 'author_book.author_id')
            ->join('book_user', 'author_book.book_id', '=', 'book_user.book_id')
            ->where('book_user.user_id', $this->id)
            ->groupBy('authors.id')
            ->orderByDesc('book_count')
            ->limit(5);
    }

    public function getTags(): Collection
    {
        $books = $this->books()
            ->with(['authors', 'tags'])
            ->withPivot('status', 'created_at')
            ->get();

        $books = $books->sortByDesc(fn ($book) => $book->pivot->created_at)
            ->values();

        $topTagNames = $books->flatMap(fn ($book) => $book->tags->pluck('name'))
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(10);

        return Tag::whereIn('name', $topTagNames)
            ->get()->sortBy(fn ($tag) => $topTagNames->search($tag->name))->values();
    }

    public function getRecommendations()
    {
        return collect(GetBookRecommendations::run($this))
            ->map(fn (array $recommendation): array => [
                'reason' => $recommendation['reason'],
                'book' => BookResource::make($recommendation['book'])->toArray(request()),
            ])
            ->values()
            ->all();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->can(UserPermission::VIEW_ADMIN_PANEL);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function logActivity(ActivityType $type, ?Model $subject = null, array $properties = []): void
    {
        $this->activities()->create([
            'type' => $type,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
        ]);
    }

    public function book_covers(): HasMany
    {
        return $this->hasMany(Cover::class);
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class)
            ->using(BookUser::class)
            ->withPivot(['status', 'tags', 'created_at', 'updated_at']);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function previousSearches(): Builder|HasMany
    {
        return $this->hasMany(PreviousSearch::class);
    }

    public function reviews(): Builder|HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function ratings(): Builder|HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }
}
