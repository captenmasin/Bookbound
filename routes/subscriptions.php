<?php

use Illuminate\Http\Request;

Route::get('checkout', function (Request $request) {
    return $request->user()
        ->newSubscription('default', config('subscriptions.plans.pro.key'))
        ->allowPromotionCodes()
        ->checkout([
            'success_url' => route('checkout-success'),
            'cancel_url' => route('checkout-cancel'),
        ]);
})->name('checkout');

Route::get('billing', function (Request $request) {
    return $request->user()->redirectToBillingPortal(route('home'));
})->middleware(['auth'])->name('billing');

Route::get('checkout/success',
    fn () => redirect()->route('home')->with('upgrade_success', true)
)->name('checkout-success');

Route::get('checkout/cancel',
    fn () => redirect()->route('home')->with('info', 'Your subscription was not completed.')
)->name('checkout-cancel');
