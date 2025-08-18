<?php

namespace App\Services;

use Uri;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Contracts\BookApiServiceInterface;
use Illuminate\Http\Client\PendingRequest;

class ISBNdbService implements BookApiServiceInterface
{
    public const ServiceName = 'ISBNdb';

    protected static string $baseUrl = 'https://api2.isbndb.com';

    protected static function client(): PendingRequest
    {
        static $client;

        return $client ??= Http::withHeaders([
            'Authorization' => config('services.isbndb.key'),
        ]);
    }

    public static function search(
        ?string $query = null,
        ?string $author = null,
        ?string $subject = null,
        int $maxResults = 30,
        $page = 1
    ): array {
        if (! $query && ! $author && ! $subject) {
            return [];
        }

        ray('ISBNdb query: '.$query);

        $queryParts = collect([
            'page' => $page,
            'pageSize' => $maxResults,
            'column' => '',
            'shouldMatchAll' => 0,
        ])->when($author, function ($parts) use ($author, &$query) {
            $parts->put('column', 'author');

            $query .= ' '.$author;

            return $parts->put('shouldMatchAll', 1);
        })->when($subject, function ($parts) use ($subject, &$query) {
            $query .= ' '.$subject;

            return $parts->put('column', 'subjects');
        })->all();

        $url = Uri::of(self::$baseUrl)
            ->withPath('books/'.urlencode(trim($query)))
            ->withQuery($queryParts);

        //            dd($query, $author, $url->__toString(), $queryParts);

        try {
            $response = self::client()
                ->retry(3, 200)
                ->get($url);
        } catch (Exception $e) {
            return [];
        }

        if (! $response->ok()) {
            return [];
        }

        $items = $response->json('books') ?? [];

        return [
            'total' => $response->json('total') ?? 0,
            'items' => $items,
        ];
    }

    public static function get(string $id): ?array
    {
        //        return Cache::remember("books:id:$id", now()->addWeek(), function () use ($id) {
        return self::getFromApi($id);
        //        });
    }

    public static function getByCode(string $isbn): ?array
    {
        return self::get($isbn);
    }

    public static function getFromApi(string $id): ?array
    {
        $response = self::client()
            ->retry(3, 200)
            ->get(self::$baseUrl.'/book/'.urlencode($id));

        if (! $response->ok()) {
            return null;
        }

        return $response->json('book');
    }
}
