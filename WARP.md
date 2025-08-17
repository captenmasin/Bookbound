# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## 🚀 Common Commands

### Backend (Laravel)
• Install dependencies: `composer install`
• Run migrations: `php artisan migrate`
• Generate application key: `php artisan key:generate`
• Run tests (Pest): `vendor/bin/pest`
  - Single test: `vendor/bin/pest tests/Feature/YourTest.php::test_method`
• Run PHPUnit: `vendor/bin/phpunit`
• Code style (Pint): `php artisan pint`
• List available Artisan commands: use the `list-artisan-commands` tool or `php artisan list --no-ansi`

### Frontend (Inertia & Vue 3)
• Install dependencies: `npm install`
• Development server: `npm run dev`
• Production build: `npm run build`
• Type-check (Vue + TS): `vue-tsc --noEmit`
• Lint & fix JS/TS/Vue: `npm run lint`
• Format & check (Prettier): `npm run format` / `npm run format:check`

### Full-stack
• Run both back and front in parallel (if set up):
```bash
# In one pane
php artisan serve --host=127.0.0.1 --port=8000
# In another pane
npm run dev
```

## 📐 High-Level Architecture

• **Backend**: Laravel 12 (PHP 8.4) MVC
  - Entry: `public/index.php`
  - Routes: `routes/web.php`, `routes/api.php`
  - Controllers: `app/Http/Controllers`
  - Models: `app/Models`
  - Config: `config/*.php`

• **Frontend**: Vue 3 + TypeScript + Inertia.js
  - Entry: `resources/js/app.ts`
  - Pages: `resources/js/pages/*.vue`
  - Shared components: `resources/js/components/`
  - Layouts: `resources/js/layouts/`
  - Styles: `resources/css/` (Tailwind CSS)
  - Build tooling: Vite (`vite.config.ts`)

• **Database & Testing**
  - Factories: `database/factories`
  - Seeders: `database/seeders`
  - Tests: `tests/Feature`, `tests/Unit`

• **Assets & Public**
  - Compiled JS/CSS: `public/js`, `public/css`
  - Static: `public/` (images, service worker, etc.)

## 🔧 Tools & Conventions

• **Laravel Herd**: Application is served locally at `https://<project-directory>.test`. Use `get-absolute-url` to generate valid URLs.

• **Laravel Boost**
  - PHP 8.4.11, Laravel 12, Inertia v2, Filament v4, Livewire v3, Pint v1, Pest v3, Tailwind v4
  - Use `search-docs` for version-specific documentation before coding changes.
  - Use `tinker` or `database-query` tools for quick data inspection.

• **Artisan**
  - Always include `--no-interaction` on automated commands where supported.
  - For Filament components/resources, discover args with `list-artisan-commands`.

## 📚 Existing Documentation

• **README.md**: Feature list, tech stack, quick setup.
• **.github/copilot-instructions.md**: Laravel Boost guidelines—adhere to package versions and conventions.


---
*Last updated: 2025-08-17*

