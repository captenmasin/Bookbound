<?php

use Illuminate\Http\Request;

Route::get('subscription-checkout', function (Request $request) {
    return $request->user()
        ->newSubscription('default', 'price_1RtwihEQqXeritAADeoHtRJ1')
        ->trialDays(5)
        ->allowPromotionCodes()
        ->checkout([
            'success_url' => route('checkout-success'),
            'cancel_url' => route('checkout-cancel'),
        ]);
});

Route::get('billing', function (Request $request) {
    return $request->user()->redirectToBillingPortal(route('home'));
})->middleware(['auth'])->name('billing');

Route::get('checkout/success', fn () => dd('Success'))->name('checkout-success');
Route::get('checkout/cancel', fn () => dd('Cancel'))->name('checkout-cancel');
