<?php

namespace App\Support;

use App\Models\User;

class SubscriptionLimits
{
    /**
     * Returns the config key of the current plan for the user (e.g., 'pro') if any matches, else null.
     */
    public static function currentPlanKey(User $user): ?string
    {
        $plans = config('subscriptions.plans', []);

        foreach ($plans as $key => $plan) {
            $priceKey = $plan['key'] ?? null;
            if (! $priceKey) {
                continue;
            }

            // Cashier: subscribed(type = 'default', price = price ID)
            if (method_exists($user, 'subscribed') && $user->subscribed('default', $priceKey)) {
                return $key;
            }
        }

        return 'free';
    }

    /**
     * Returns the limits array for the current user's plan or an empty array if no plan matched.
     */
    public static function limitsFor(User $user): array
    {
        $plans = config('subscriptions.plans', []);
        $planKey = self::currentPlanKey($user);

        if ($planKey && isset($plans[$planKey])) {
            return $plans[$planKey]['limits'] ?? [];
        }

        return [];
    }

    /**
     * Resolve a single limit value, returning $default if not present.
     *
     * @template T
     *
     * @param  T  $default
     * @return mixed|T
     */
    public static function getLimit(User $user, string $name, $default = null)
    {
        $limits = self::limitsFor($user);

        return array_key_exists($name, $limits) ? $limits[$name] : $default;
    }

    /**
     * Whether the user can add one more book according to their plan limits.
     */
    public static function canAddBook(User $user): bool
    {
        $maxBooks = self::getLimit($user, 'max_books');

        // No limit defined means unlimited
        if ($maxBooks === null) {
            return true;
        }

        // Explicit zero or negative treated as not allowed
        $maxBooks = (int) $maxBooks;
        if ($maxBooks <= 0) {
            return false;
        }

        $current = $user->books()->count();

        return $current < $maxBooks;
    }

    public static function remainingBooks(User $user): ?int
    {
        $maxBooks = self::getLimit($user, 'max_books');
        if ($maxBooks === null) {
            return null; // unlimited
        }

        $current = $user->books()->count();

        return max(0, (int) $maxBooks - $current);

    }

    public static function allowPrivateNotes(User $user): bool
    {
        return self::getLimit($user, 'private_notes', false);
    }

    public static function allowCustomCovers(User $user): bool
    {
        return self::getLimit($user, 'custom_covers', false);
    }
}
