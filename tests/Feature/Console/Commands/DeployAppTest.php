<?php

use App\Console\Commands\DeployApp;
use Illuminate\Support\Facades\Process;

it('passes the configured timeout to shell processes', function () {
    Process::fake();

    $command = new class extends DeployApp
    {
        public function runShellForTest(string $command, int $timeout = self::NPM_COMMAND_TIMEOUT_SECONDS): void
        {
            $this->runShell($command, $timeout);
        }

        public function info($string, $verbosity = null): void {}

        public function line($string, $style = null, $verbosity = null): void {}

        public function error($string, $verbosity = null): void {}
    };

    $command->runShellForTest('npm ci', 900);

    Process::assertRan(fn ($process) => $process->command === 'npm ci' && $process->timeout === 900);
});

it('uses the extended timeout for deploy npm commands', function () {
    $command = new class extends DeployApp
    {
        public array $shellCommands = [];

        public bool $ssr = false;

        public function call($command, array $arguments = []): int
        {
            return self::SUCCESS;
        }

        public function callSilent($command, array $arguments = []): int
        {
            return self::SUCCESS;
        }

        public function error($string, $verbosity = null): void {}

        public function info($string, $verbosity = null): void {}

        public function line($string, $style = null, $verbosity = null): void {}

        public function option($key = null): mixed
        {
            return $key === 'ssr' ? $this->ssr : null;
        }

        protected function isOctaneRunning(): bool
        {
            return false;
        }

        protected function runShell(string $command, int $timeout = self::NPM_COMMAND_TIMEOUT_SECONDS): void
        {
            $this->shellCommands[] = [
                'command' => $command,
                'timeout' => $timeout,
            ];
        }
    };

    $exitCode = $command->handle();

    expect($exitCode)->toBe(0)
        ->and($command->shellCommands)->toBe([
            ['command' => 'npm ci', 'timeout' => 900],
            ['command' => 'npm run build', 'timeout' => 900],
        ]);
});

it('uses the ssr build command when the flag is enabled', function () {
    $command = new class extends DeployApp
    {
        public array $shellCommands = [];

        public bool $ssr = true;

        public function call($command, array $arguments = []): int
        {
            return self::SUCCESS;
        }

        public function callSilent($command, array $arguments = []): int
        {
            return self::SUCCESS;
        }

        public function error($string, $verbosity = null): void {}

        public function info($string, $verbosity = null): void {}

        public function line($string, $style = null, $verbosity = null): void {}

        public function option($key = null): mixed
        {
            return $key === 'ssr' ? $this->ssr : null;
        }

        protected function isOctaneRunning(): bool
        {
            return false;
        }

        protected function runShell(string $command, int $timeout = self::NPM_COMMAND_TIMEOUT_SECONDS): void
        {
            $this->shellCommands[] = [
                'command' => $command,
                'timeout' => $timeout,
            ];
        }
    };

    $command->handle();

    expect($command->shellCommands)->toBe([
        ['command' => 'npm ci', 'timeout' => 900],
        ['command' => 'npm run build:ssr', 'timeout' => 900],
    ]);
});
