<?php

use App\Models\User;
use Stripe\Customer;
use Stripe\StripeClient;
use Stripe\Checkout\Session;

it('redirects guests away from checkout', function () {
    $response = $this->get(route('checkout'));

    $response->assertRedirect(route('login'));
});

it('creates checkout sessions that save billing addresses for automatic tax', function () {
    config(['subscriptions.plans.pro.key' => 'price_pro']);

    $captured = new class
    {
        /** @var array<string, mixed> */
        public array $checkoutSession = [];
    };

    $stripe = new class($captured)
    {
        public object $customers;

        public object $checkout;

        public function __construct(object $captured)
        {
            $this->customers = new class
            {
                /**
                 * @param  array<int, string>  $options
                 */
                public function retrieve(string $customerId, array $options): Customer
                {
                    return Customer::constructFrom(['id' => $customerId]);
                }
            };

            $this->checkout = new class($captured)
            {
                public object $sessions;

                public function __construct(object $captured)
                {
                    $this->sessions = new class($captured)
                    {
                        public function __construct(private object $captured) {}

                        /**
                         * @param  array<string, mixed>  $payload
                         */
                        public function create(array $payload): Session
                        {
                            $this->captured->checkoutSession = $payload;

                            return Session::constructFrom([
                                'id' => 'cs_test',
                                'url' => 'https://checkout.stripe.test/session',
                            ]);
                        }
                    };
                }
            };
        }
    };

    app()->bind(StripeClient::class, fn (): object => $stripe);

    $user = User::factory()->create(['stripe_id' => 'cus_test']);

    $response = $this->actingAs($user)->get(route('checkout'));

    $response->assertRedirect('https://checkout.stripe.test/session');

    expect($captured->checkoutSession)->toMatchArray([
        'customer' => 'cus_test',
        'mode' => 'subscription',
        'billing_address_collection' => 'required',
        'line_items' => [
            [
                'price' => 'price_pro',
                'quantity' => 1,
            ],
        ],
    ]);
    expect($captured->checkoutSession['customer_update']['address'])->toBe('auto');
});
