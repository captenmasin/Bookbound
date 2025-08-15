<?php

namespace Tests\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

trait GiveSubscription
{
    public function giveActiveSubscription(User $user, string $priceId, string $name = 'default'): void
    {
        // Works across Cashier Stripe versions (stripe_price vs stripe_plan)
        $isStripe = Schema::hasColumn('subscriptions', 'stripe_status');
        $hasStripePrice = Schema::hasColumn('subscriptions', 'stripe_price');
        $isPaddle = Schema::hasColumn('subscriptions', 'paddle_status');

        $base = [
            'type' => 'default',
            'quantity' => 1,
            'trial_ends_at' => null,
            'ends_at' => null,
        ];

        if ($isStripe) {
            $cols = [
                'stripe_id' => 'sub_'.fake()->uuid(),
                'stripe_status' => 'active',
            ] + ($hasStripePrice
                    ? ['stripe_price' => $priceId]          // Cashier Stripe v13+
                    : ['stripe_plan' => $priceId]);        // Older Cashier
            $user->subscriptions()->create($base + $cols);

            return;
        }

        if ($isPaddle) {
            $cols = [
                'paddle_subscription_id' => 'sub_'.fake()->uuid(),
                'paddle_status' => 'active',
                'paddle_price_id' => $priceId,
            ];
            $user->subscriptions()->create($base + $cols);

            return;
        }

        throw new \RuntimeException('No supported Cashier columns found. Did you run Cashier migrations?');
    }

    public function giveCanceledSubscription(User $user, string $priceId, string $name = 'default'): void
    {
        // Same idea, but simulate grace-period/canceled
        $isStripe = Schema::hasColumn('subscriptions', 'stripe_status');
        $isPaddle = Schema::hasColumn('subscriptions', 'paddle_status');

        $base = [
            'type' => 'default',
            'quantity' => 1,
            'trial_ends_at' => null,
            'ends_at' => now(), // canceled (no grace)
        ];

        if ($isStripe) {
            $user->subscriptions()->create($base + [
                'stripe_id' => 'sub_'.fake()->uuid(),
                'stripe_status' => 'canceled',
                (Schema::hasColumn('subscriptions', 'stripe_price') ? 'stripe_price' : 'stripe_plan') => $priceId,
            ]);

            return;
        }

        if ($isPaddle) {
            $user->subscriptions()->create($base + [
                'paddle_subscription_id' => 'sub_'.fake()->uuid(),
                'paddle_status' => 'deleted',
                'paddle_price_id' => $priceId,
            ]);

            return;
        }

        throw new \RuntimeException('No supported Cashier columns found.');
    }
}
