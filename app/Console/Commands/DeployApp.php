<?php

namespace App\Console\Commands;

use Laravel\Octane\Octane;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class DeployApp extends Command
{
    protected $signature = 'app:deploy';

    public function handle(): int
    {
        $this->info('🔧 Starting deployment...');

        // NPM
        $this->runShell('npm ci');
        $this->runShell('npm run build');

        // Terminate Horizon
        $this->callSilent('horizon:terminate');

        // Laravel caches
        $this->info('🗄️  Clearing and caching Laravel configurations...');
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        // SQLite file
        $sqlitePath = database_path('database.sqlite');
        if (! file_exists($sqlitePath)) {
            touch($sqlitePath);
            $this->info('🧱 Created SQLite database file.');
        }

        // Migrate and seed
        $this->info('🔄 Running migrations and seeding the database...');
        $this->call('migrate', ['--force' => true]);
        $this->call('db:seed', ['--force' => true]);

        // Horizon again
        $this->callSilent('horizon:terminate');

        // Reload Octane if running
        if ($this->isOctaneRunning()) {
            $this->info('♻️ Reloading Octane...');
            $this->call('octane:reload');
        } else {
            $this->info('🛑 Octane is not running. Skipping reload.');
        }

        // Re-cache the configuration
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');

        $this->info('✅ Deployment completed.');

        return self::SUCCESS;
    }

    protected function runShell(string $command): void
    {
        $this->info("⚙️  Running: {$command}");
        $result = Process::run($command);

        if (! $result->successful()) {
            $this->error("❌ Failed: {$command}");
            $this->error($result->errorOutput());
            exit(1);
        }

        $this->line($result->output());
    }

    protected function isOctaneRunning(): bool
    {
        $output = trim(shell_exec('php artisan octane:status'));

        return str_contains($output, 'running');
    }
}
