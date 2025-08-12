<?php

namespace App\Http\Controllers;

use Number;
use Stripe\Price;
use Stripe\Stripe;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __invoke()
    {
        Stripe::setApiKey(config('cashier.secret'));
        $priceObject = Price::retrieve(config('subscriptions.plans.pro.key'));
        $recurring = $priceObject->recurring;
        $interval = $recurring->interval;

        Number::useCurrency($priceObject->currency);
        $price = Number::currency($priceObject->unit_amount / 100);

        $freeLimits = config('subscriptions.plans.free.limits');

        return Inertia::render('Home', [
            'price' => $price,
            'interval' => $interval,
            'freeLimits' => $freeLimits,
        ])->withMeta([]);
    }
}
