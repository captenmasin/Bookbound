<?php

use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

use App\Mail\ContactFormSubmission;

use function Pest\Laravel\actingAs;

use Illuminate\Support\Facades\Mail;

describe('GeneralPageController', function () {
    it('displays privacy policy page', function () {
        $response = get(route('privacy-policy'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PrivacyPolicy')
        );
    });

    it('has correct meta tags for privacy policy page', function () {
        $response = get(route('privacy-policy'));

        $response->assertOk();
        $response->assertSee('Privacy Policy');
    });

    it('displays contact page with correct data', function () {
        $response = get(route('contact'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Contact')
            ->has('options', fn ($options) => $options
                ->where('support', 'Support')
                ->where('billing', 'Billing')
                ->where('feedback', 'Feedback')
                ->where('general', 'General Inquiry')
            )
            ->has('email')
        );
    });

    it('has correct meta tags for contact page', function () {
        $response = get(route('contact'));

        $response->assertOk();
        $response->assertSee('Contact Us');
    });

    it('submits contact form with valid data successfully', function () {
        Mail::fake();

        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'support',
            'message' => 'This is a test message for support.',
        ];

        $response = post(route('contact.submit'), $contactData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Your message has been sent successfully!');

        Mail::assertSent(ContactFormSubmission::class);
    });

    it('submits contact form when authenticated', function () {
        Mail::fake();
        $user = User::factory()->create();
        actingAs($user);

        $contactData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'billing',
            'message' => 'This is a billing inquiry.',
        ];

        $response = post(route('contact.submit'), $contactData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Your message has been sent successfully!');

        Mail::assertSent(ContactFormSubmission::class);
    });

    it('submits contact form when unauthenticated', function () {
        Mail::fake();

        $contactData = [
            'name' => 'Anonymous User',
            'email' => 'anonymous@example.com',
            'subject' => 'general',
            'message' => 'This is a general inquiry.',
        ];

        $response = post(route('contact.submit'), $contactData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Your message has been sent successfully!');

        Mail::assertSent(ContactFormSubmission::class);
    });

    it('validates required fields in contact form', function () {
        $response = post(route('contact.submit'), []);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'subject',
            'message',
        ]);
    });

    it('validates email format in contact form', function () {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'subject' => 'support',
            'message' => 'Test message',
        ];

        $response = post(route('contact.submit'), $contactData);

        $response->assertSessionHasErrors(['email']);
    });

    it('validates subject is from allowed options', function () {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'invalid-subject',
            'message' => 'Test message',
        ];

        $response = post(route('contact.submit'), $contactData);

        $response->assertSessionHasErrors(['subject']);
    });

    it('accepts all valid subject options', function () {
        Mail::fake();

        $subjects = ['support', 'billing', 'feedback', 'general'];

        foreach ($subjects as $subject) {
            $contactData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'subject' => $subject,
                'message' => "Test message for {$subject}",
            ];

            $response = post(route('contact.submit'), $contactData);

            $response->assertRedirect();
            $response->assertSessionHas('success');
        }

        Mail::assertSent(ContactFormSubmission::class, 4);
    });

    it('sends email with correct data when contact form is submitted', function () {
        Mail::fake();

        $contactData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'feedback',
            'message' => 'This is a feedback message.',
        ];

        post(route('contact.submit'), $contactData);

        Mail::assertSent(ContactFormSubmission::class);
    });

    it('includes user ID in email when authenticated user submits contact form', function () {
        Mail::fake();
        $user = User::factory()->create();
        actingAs($user);

        $contactData = [
            'name' => 'Authenticated User',
            'email' => 'auth@example.com',
            'subject' => 'support',
            'message' => 'Message from authenticated user.',
        ];

        post(route('contact.submit'), $contactData);

        Mail::assertSent(ContactFormSubmission::class, function ($mail) use ($user) {
            return $mail->userId === $user->id;
        });
    });

    it('sets user ID to null when unauthenticated user submits contact form', function () {
        Mail::fake();

        $contactData = [
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'subject' => 'general',
            'message' => 'Message from guest user.',
        ];

        post(route('contact.submit'), $contactData);

        Mail::assertSent(ContactFormSubmission::class, function ($mail) {
            return $mail->userId === null;
        });
    });
});
