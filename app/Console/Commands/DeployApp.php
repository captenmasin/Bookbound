<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Cache\Console\ClearCommand;

class DeployApp extends Command
{
    protected $signature = 'app:deploy {--ssr : Enable server-side rendering}';

    public function handle(): int
    {
        $this->info('🔧 Starting deployment...');

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

        // Re-cache the configuration
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');

        $this->info('✅ Deployment completed.');

        return self::SUCCESS;
    }
}
