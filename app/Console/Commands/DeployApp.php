<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Cache\Console\ClearCommand;
use Laravel\Horizon\Console\TerminateCommand;

class DeployApp extends Command
{
    protected const NPM_COMMAND_TIMEOUT_SECONDS = 900;

    protected $signature = 'app:deploy {--ssr : Enable server-side rendering}';

    public function handle(): int
    {
        $this->info('🔧 Starting deployment...');

        $this->info('📀 Storage link');
        $this->call('storage:link');

//        // NPM
//        $this->runShell('pnpm ci');
//
//        if ($this->option('ssr')) {
//            $this->info('🌐 Running SSR build...');
//            $this->runShell('pnpm run build:ssr');
//        } else {
//            $this->info('📦 Running frontend build...');
//            $this->runShell('pnpm run build');
//        }

        // Terminate Horizon
        $this->terminateHorizon();

        // Laravel caches
        $this->info('🗄️  Clearing and caching Laravel configurations...');
        $this->call(ClearCommand::class);
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        // Migrate and seed
        $this->info('🔄 Running migrations and seeding the database...');
        $this->call('migrate', ['--force' => true]);
        $this->call('db:seed', ['--force' => true]);

        // Horizon again
        $this->terminateHorizon();

        // Generate Sitemap
        $this->call(GenerateSitemap::class);

        // Generate Robots.txt
        $this->call(GenerateRobotsTxt::class);

        // Generate PWA manifest
        $this->call(GeneratePwaManifest::class);

        // Reload Octane if running
        //        if ($this->isOctaneRunning()) {
        //            $this->info('♻️ Reloading Octane...');
        //            $this->call('octane:reload');
        //        } else {
        //            $this->info('🛑 Octane is not running. Skipping reload.');
        //        }

        // Re-cache the configuration
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');

        $this->info('✅ Deployment completed.');

        return self::SUCCESS;
    }

    protected function runShell(string $command, int $timeout = self::NPM_COMMAND_TIMEOUT_SECONDS): void
    {
        $this->info("⚙️  Running: {$command}");
        $result = Process::timeout($timeout)->run($command);

        if (! $result->successful()) {
            $this->error("❌ Failed: {$command}");
            $this->error($result->errorOutput());
            exit(1);
        }

        $this->line($result->output());
    }

    protected function terminateHorizon(): void
    {
        if (! $this->isHorizonInUse()) {
            $this->info('🛑 Horizon is not in use. Skipping terminate.');

            return;
        }

        $this->callSilent(TerminateCommand::class);
    }

    protected function isHorizonInUse(): bool
    {
        $connection = config('queue.connections.'.config('queue.default'));

        return is_array($connection) && ($connection['driver'] ?? null) === 'redis';
    }

    protected function isOctaneRunning(): bool
    {
        $output = trim(shell_exec('php artisan octane:status'));

        return str_contains($output, 'running');
    }
}
