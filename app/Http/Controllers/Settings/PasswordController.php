<?php

namespace App\Http\Controllers\Settings;

use Throwable;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Password', [
            'passkeys' => $request->user()->passkeys()->get()
                ->map(fn ($key) => $key->only(['id', 'name', 'last_used_at'])),
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => route('dashboard')],
                ['title' => 'Settings', 'href' => route('user.settings.profile.edit')],
                ['title' => 'Password', 'href' => route('user.settings.password.edit')],
            ],
        ])->withMeta([
            'title' => 'Password Settings',
            'description' => 'Manage your password and passkeys.',
        ]);
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }

    public function storePassKey()
    {
        $data = request()->validate([
            'passkey' => 'required|json',
            'options' => 'required|json',
        ]);

        $user = auth()->user();
        $storePasskeyAction = app(StorePasskeyAction::class);

        try {
            $storePasskeyAction->execute(
                $user,
                $data['passkey'],
                $data['options'],
                request()->getHost(),
                ['name' => Str::random(10)],
            );

            // Redirect back
            return redirect()->back();

        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'name' => __('passkeys::passkeys.error_something_went_wrong_generating_the_passkey'),
            ]);
        }
    }

    public function deletePasskey(Request $request, string $id)
    {
        $request->user()->passkeys()->where('id', $id)->delete();

        return redirect()->back()
            ->with('success', 'Passkey deleted successfully');
    }

    public function generatePasskeyOptions()
    {
        return app(GeneratePasskeyRegisterOptionsAction::class)->execute(auth()->user());
    }
}
