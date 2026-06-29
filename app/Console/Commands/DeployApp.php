<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Cache\Console\ClearCommand;

class DeployApp extends Command
{
    protected $signature = 'app:deploy {--ssr : Enable server-side rendering}';

    public function handle(): int
    {
        $this->info('🔧 Starting deployment...');

        if (DB::connection()->getDriverName() === 'pgsql') {
            $this->info('🔢 Syncing PostgreSQL sequences...');
            $this->call('db:sync-sequences');
        }

        // Migrate and seed
        $this->info('🔄 Running migrations and seeding the database...');
        $this->call('migrate', ['--force' => true]);
        $this->call('db:seed', ['--force' => true]);

        // Laravel caches
        $this->info('🗄️  Clearing and caching Laravel configurations...');
        $this->call(ClearCommand::class);
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        // Generate Sitemap
        $this->call(GenerateSitemap::class);

        // Generate Robots.txt
        $this->call(GenerateRobotsTxt::class);

        // Generate PWA manifest
        $this->call(GeneratePwaManifest::class);

        $this->line('Installing PNPM packages');
        if (app()->environment('production')) {
            Process::forever()->run('pnpm install --frozen-lockfile --silent');
        } else {
            Process::forever()->run('pnpm install --silent');
        }
        $this->info('PNPM packages installed');
        $this->newLine();

        $this->line('Removing vite public/hot file');
        Process::forever()->run('rm -rf public/hot');
        $this->newLine();

        $this->line('Running PNPM build'.(config('inertia.ssr.enabled') ? ' (SSR)' : ''));
        if (config('inertia.ssr.enabled')) {
            Process::forever()->run('pnpm run build:ssr');
        } else {
            Process::forever()->run('pnpm run build');
        }
        $this->info('PNPM built');
        $this->newLine();

        // Re-cache the configuration
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');

        $this->info('✅ Deployment completed.');

        return self::SUCCESS;
    }
}
