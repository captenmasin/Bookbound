<?php

namespace App\Providers;

use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Cashier;
use App\Services\ISBNdbService;
use Filament\Support\Colors\Color;
use App\Services\GoogleBooksService;
use App\Services\OpenLibraryService;
use Illuminate\Support\Facades\Vite;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Support\Facades\Config;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\ServiceProvider;
use Lorisleiva\Actions\Facades\Actions;
use App\Contracts\BookApiServiceInterface;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->bind(BookApiServiceInterface::class, GoogleBooksService::class);
        // $this->app->bind(BookApiServiceInterface::class, OpenLibraryService::class);
        //        $this->app->bind(BookApiServiceInterface::class, ISBNdbService::class);

        $map = Config::get('books.providers', []);
        $key = Config::get('books.provider', 'isbndb');

        $class = $map[$key] ?? null;

        if ($class) {
            $this->app->bind(BookApiServiceInterface::class, $class);
        } else {
            $this->app->bind(BookApiServiceInterface::class, ISBNdbService::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Actions::registerCommands();

        JsonResource::withoutWrapping();

        Vite::prefetch(2);

        FilamentColor::register([
            'primary' => Color::Amber,
        ]);

        Cashier::calculateTaxes();

        Inertia::macro('prefetch', function (string|array $urls) {
            $urls = is_array($urls) ? $urls : [$urls];

            Inertia::share('prefetch', $urls);

            return $this;
        });

        Response::macro('withBreadcrumbs', function ($breadcrumbs) {
            $breadcrumbs = collect($breadcrumbs)->map(function ($url, $name) {
                return [
                    'title' => $name,
                    'href' => $url,
                ];
            })->values();

            return $this->with('breadcrumbs', $breadcrumbs);
        });

        Response::macro('withMeta', function ($meta) {
            $meta = (object) $meta;
            $title = $meta->title ?? 'untitled';
            $description = $meta->description ?? config('seotools.meta.defaults.description');
            $description = $description ? str_replace(["\r", "\n"], '', $description) : null;
            $json = $meta->json ?? '';
            $preload = $meta->preload ?? [];
            $image = $meta->image ?? url('og.png');
            $canonical = $meta->url ?? url()->full();

            SEOTools::setTitle($title);
            SEOTools::setCanonical($canonical);
            SEOTools::setDescription($description);
            SEOTools::addImages($image);

            JsonLd::setTitle($title);
            JsonLd::setDescription($description);
            JsonLd::setImages([$image]);

            return $this->with('meta', [
                'title' => SEOMeta::getTitle(),
                'description' => SEOMeta::getDescription(),
            ])->withViewData('meta', [
                'feeds' => $meta->feeds ?? null,
                'title' => SEOMeta::getTitle(),
                'json' => $json,
                'description' => $description,
                'image' => $image,
                'canonical' => $canonical,
                'preload' => $preload,
            ]);
        });
    }
}
