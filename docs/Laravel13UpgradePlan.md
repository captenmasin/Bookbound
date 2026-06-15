# Laravel 13 Upgrade Plan

Created: 2026-06-15

## Current State

The application is currently running Laravel 12 on PHP 8.5. PHP is not a blocker for Laravel 13, because Laravel 13 supports PHP 8.3 through 8.5.

Current framework/package baseline from Laravel Boost:

- PHP: 8.5
- Laravel framework: 12.61.1 / application reports 12.62.0
- Filament: 4.11.7
- Inertia Laravel: 2.0.21
- Livewire: 3.8.1
- Pest: 4.7.2
- PHPUnit: 12.5.28

Laravel 13 is released. The official upgrade guide calls out these dependency changes:

- `laravel/framework` to `^13.0`
- `laravel/boost` to `^2.0`
- `laravel/tinker` to `^3.0`
- `phpunit/phpunit` to `^12.0`
- `pestphp/pest` to `^4.0`

Pest and PHPUnit already satisfy the Laravel 13 guide constraints.

## Initial Composer Findings

`composer prohibits laravel/framework 13.15.0 --locked --recursive` identified these blockers:

| Package | Current constraint/version | Issue | Planned action |
| --- | --- | --- | --- |
| `laravel/framework` | `^12.0` | Root app requires Laravel 12 | Change to `^13.0` |
| `laravel/tinker` | `^2.10.1` / 2.11.1 | Tinker 2 does not support Illuminate 13 | Change to `^3.0` |
| `laravel/boost` | `^1.0` / 1.8.13 | Boost 1 does not support Illuminate 13 | Change to `^2.0` in `require-dev` |
| `barryvdh/laravel-debugbar` | `^3.15` / 3.16.5 | Debugbar 3 does not support Illuminate 13 | Change to `^4.0` in `require-dev` |
| `filament/spatie-laravel-media-library-plugin` | `^3.2` / 3.3.30 | Plugin 3 does not support Illuminate 13 | Change to `^4.0` with current Filament 4 |
| `captenmasin/laravel-dumper` | `^1.0` / 1.0.0 | Does not advertise stable Illuminate 13 support | Remove, replace, or fork before production upgrade |

A combined Composer dry-run resolved only when related blockers were upgraded together, but it selected `laravel/framework` `dev-master` instead of a stable Laravel 13 tag. Do not accept that outcome for production. The stable target is the latest `v13.x` tag, currently `v13.15.0` as of 2026-06-15.

## Upgrade Strategy

### 1. Prepare a dedicated upgrade branch

Create a branch and keep the current unrelated working tree changes separate.

```bash
git checkout -b upgrade/laravel-13
git status --short
```

Do not include unrelated files in the Laravel 13 commit.

### 2. Resolve Composer constraints

Edit `composer.json` deliberately rather than running a single `composer require` that moves dev packages into `require`.

Expected constraint changes:

```json
{
  "require": {
    "filament/spatie-laravel-media-library-plugin": "^4.0",
    "laravel/framework": "^13.0",
    "laravel/tinker": "^3.0"
  },
  "require-dev": {
    "barryvdh/laravel-debugbar": "^4.0",
    "laravel/boost": "^2.0"
  }
}
```

For `captenmasin/laravel-dumper`, choose one before running the final update:

- Preferred: remove it if it is no longer needed.
- Alternative: replace it with a Laravel 13-compatible maintained package.
- Last resort: fork it and update its Illuminate constraints after verifying compatibility.

Then run a targeted update:

```bash
composer update laravel/framework laravel/tinker filament/spatie-laravel-media-library-plugin laravel/boost barryvdh/laravel-debugbar captenmasin/laravel-dumper --with-all-dependencies
```

Acceptance gate:

```bash
composer show laravel/framework
```

The installed version must be a stable `v13.x` release, not `dev-master`, `13.x-dev`, or an alias of `dev-master`.

### 3. Review first-party package compatibility

After Composer resolves Laravel 13, run:

```bash
composer outdated --direct
composer prohibits laravel/framework 13.15.0 --locked --recursive
```

Confirm these packages still resolve cleanly:

- `laravel/cashier`
- `laravel/horizon`
- `laravel/nightwatch`
- `laravel/sanctum`
- `laravel/scout`
- `laravel/dusk`
- `laravel/pail`
- `laravel/sail`
- `filament/filament`
- `livewire/livewire`
- `inertiajs/inertia-laravel`

### 4. Apply Laravel 13 code changes

Update renamed CSRF middleware references:

- `app/Providers/Filament/AdminPanelProvider.php`
  - Replace `Illuminate\Foundation\Http\Middleware\VerifyCsrfToken` with `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`.
  - Replace `VerifyCsrfToken::class` with `PreventRequestForgery::class`.
- `config/sanctum.php`
  - Replace `Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class` with `Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class`.

Review `bootstrap/app.php` for any custom CSRF configuration and migrate it to the Laravel 13 `preventRequestForgery(...)` API if needed.

### 5. Review behavioral changes

Search and inspect for Laravel 13 behavior changes that may affect this app:

```bash
rg -n "VerifyCsrfToken|ValidateCsrfToken|PreventRequestForgery|exceptionOccurred|QueueBusy|upsert\\(|serializable_classes|Container::call|withoutMiddleware|MorphPivot|new static\\(\\)|new self\\(\\)" app bootstrap config database routes tests --glob '*.php'
```

Specific review points:

- Cache: decide whether to add the new `serializable_classes` config if the app stores PHP objects in cache.
- Queue events: replace `JobAttempted::$exceptionOccurred` with `$exception` if used.
- Queue busy events: replace `$connection` with `$connectionName` if used.
- Eloquent booting: avoid instantiating models from inside model boot methods.
- Routing: review domain-specific routes because Laravel 13 prioritizes explicit domain routes before non-domain routes.
- Database: review any `upsert()` calls for empty `uniqueBy` values.

### 6. Regenerate framework assets and discovery

After Composer finishes:

```bash
php artisan optimize:clear
php artisan package:discover --ansi
php artisan filament:upgrade
php artisan ziggy:generate
```

If published config files differ materially from Laravel 13 defaults, compare against a fresh Laravel 13 app and selectively port only relevant changes.

### 7. Verification

Run formatting first:

```bash
vendor/bin/pint --dirty --format agent
```

Run the focused backend checks:

```bash
php artisan test --compact
```

Run frontend checks:

```bash
pnpm run lint
pnpm run build
```

If browser behavior is affected, run focused Pest browser tests or smoke pages with no JavaScript errors.

### 8. Deployment checklist

Before deploying:

- Confirm production PHP is 8.3 or newer.
- Confirm queue workers, Horizon, scheduler, and deployment scripts are using the upgraded vendor tree.
- Run database migrations in staging first.
- Restart queue workers after deployment.
- Clear and rebuild config, route, and view caches.
- Exercise login, admin panel, file uploads/media library, billing flows, search, and queue-backed workflows in staging.

## Rollback Plan

Keep the Laravel 12 lockfile available through the previous commit. If staging fails:

```bash
git revert <upgrade-commit>
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
```

Restart queue workers after rollback so no Laravel 13-serialized jobs keep running against Laravel 12 code.

## Open Decisions

- Decide whether to remove, replace, or fork `captenmasin/laravel-dumper`.
- Decide whether to pin Symfony components to `^7.4` or allow Composer to select Symfony 8, after checking package compatibility.
- Decide whether Laravel Boost should be upgraded first in a separate commit so the `/upgrade-laravel-v13` workflow is available.

