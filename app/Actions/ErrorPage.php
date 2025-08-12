<?php

namespace App\Actions;

use Inertia\Inertia;

use function request;

use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Middleware\HandleInertiaRequests;
use Symfony\Component\HttpFoundation\Response;

class ErrorPage
{
    use AsAction;

    public function handle($statusCode = 404): Response
    {
        if (is_string($statusCode)) {
            $statusCode = 404;
        }

        return Inertia::render('Error', array_merge((new HandleInertiaRequests)->share(request()), [
            'status' => 404,
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => route('dashboard')],
                ['title' => 'Page not found'],
            ],
        ]))->withMeta([
            'title' => 'Not found',
        ])->toResponse(request())->setStatusCode($statusCode);
    }
}
