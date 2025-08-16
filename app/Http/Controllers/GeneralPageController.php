<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Mail\ContactFormSubmission;
use Illuminate\Support\Facades\Mail;

class GeneralPageController extends Controller
{
    public function privacy()
    {
        return Inertia::render('PrivacyPolicy')
            ->withMeta([
                'title' => 'Privacy Policy',
                'description' => 'Read our privacy policy to understand how we handle your data.',
            ]);
    }

    public function contact()
    {
        return Inertia::render('Contact', [
            'options' => config('contact.subjects'),
            'email' => config('contact.email'),
        ])->withMeta([
            'title' => 'Contact Us',
            'description' => 'Get in touch with us for any inquiries or support.',
        ]);
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'subject' => ['required', 'in:'.implode(',', array_keys(config('contact.subjects')))],
            'message' => ['required', 'string'],
        ]);

        // Here you would typically send the email using a mail service.
        // For example:
        Mail::to(config('contact.email'))->send((new ContactFormSubmission(
            name: $validated['name'],
            email: $validated['email'],
            message: $validated['message'],
            userId: auth()->id() ?? null,
        ))->subject($validated['subject']));

        return redirect()->back()
            ->with('success', 'Your message has been sent successfully!');
    }
}
