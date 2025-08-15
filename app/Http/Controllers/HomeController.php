<?php

namespace App\Http\Controllers;

use Cache;
use Number;
use Stripe\Price;
use Stripe\Stripe;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __invoke()
    {
        [$price, $interval] = Cache::rememberForever('home.price', function () {
            try {
                Stripe::setApiKey(config('cashier.secret'));

                $priceObject = Price::retrieve(config('subscriptions.plans.pro.key'));

                Number::useCurrency($priceObject->currency);
                $price = Number::currency($priceObject->unit_amount / 100);

                $interval = $priceObject->recurring?->interval;

                return [$price, $interval];
            } catch (\Exception $e) {
                // Handle the error gracefully, maybe log it or return a default value
                return ['N/A', 'N/A'];
            }
        });

        $freeLimits = config('subscriptions.plans.free.limits');
        $freeFeatures = config('subscriptions.plans.free.features');
        $proLimits = config('subscriptions.plans.pro.limits');
        $proFeatures = config('subscriptions.plans.pro.features');

        return Inertia::render('Home', [
            'price' => $price,
            'interval' => $interval,
            'freeLimits' => $freeLimits,
            'freeFeatures' => $freeFeatures,
            'proLimits' => $proLimits,
            'proFeatures' => $proFeatures,
        ])->withMeta([
            'title' => 'Your Reading Life at a Glance - Track, Categorize & Review Your Books',
            'description' => 'Manage your entire reading collection with ease—search by title, author, or tag; filter by reading status; and save personal notes and reviews.',
        ]);
    }
}
