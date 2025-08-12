<?php

namespace App\Exceptions;

use Throwable;
use Inertia\Inertia;
use App\Actions\ErrorPage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        if ($e instanceof ModelNotFoundException) {
            return $this->handleArticle($e, $request);
        }

        if ($e->getPrevious() instanceof ModelNotFoundException) {
            return $this->handleArticle($e->getPrevious(), $request);
        }

        if ($e instanceof HttpException) {
            if (in_array($e->getStatusCode(), [403, 404])) {
                return ErrorPage::run($e->getStatusCode());
            }
        }

        //        Ignore admin pages, they handle their own errors
        if (! Str::startsWith($request->path(), config('filament.path'))) {
            // Just flash a message if it's an Inertia request
            if ($request->headers->get('x-inertia') === 'true') {
                if ($response->status() === 403) {
                    return back()->with('error', 'Unauthorized action');
                }
                if (in_array($response->status(), [500, 503])) {
                    return back()->with('error', 'Something went wrong');
                }

                return back();
            }

            // Flash a message if it's 419, inertia or otherwise
            if ($response->status() === 419) {
                return back()->with('error', 'The page expired, please try again.');
            }

            // Return a nice error page for some error codes
            if (in_array($response->status(), [500, 503, 404, 403]) && ! config('app.debug')) {
                return Inertia::render('Error', [
                    'status' => $response->status(),
                    'breadcrumbs' => [
                        ['title' => 'Home', 'href' => route('dashboard')],
                        ['title' => 'Error '.$response->status()],
                    ],
                ])->withMeta(['title' => 'Error '.$response->status()])
                    ->toResponse($request)->setStatusCode($response->status());
            }
        }

        return $response;
    }

    public function handleArticle($exception, $request)
    {
        try {
            return ErrorPage::run($exception->getStatusCode());
        } catch (Throwable $e) {
            return ErrorPage::run(404);
        }
    }
}
