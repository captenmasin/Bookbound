<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPostgresSequences extends Command
{
    protected $signature = 'db:sync-sequences {--table= : Sync a single table}';

    protected $description = 'Resync PostgreSQL auto-increment sequences to match current max IDs';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->error('This command only works with PostgreSQL.');

            return self::FAILURE;
        }

        $table = $this->option('table');

        $sequences = DB::select('
            SELECT
                s.relname AS sequence_name,
                t.relname AS table_name,
                a.attname AS column_name
            FROM pg_class s
            JOIN pg_depend d ON d.objid = s.oid
            JOIN pg_class t ON d.refobjid = t.oid
            JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = d.refobjsubid
            WHERE s.relkind = \'S\'
            AND t.relnamespace = (SELECT oid FROM pg_namespace WHERE nspname = \'public\')
            '.($table ? 'AND t.relname = ?' : '').'
            ORDER BY t.relname
        ', $table ? [$table] : []);

        if ($sequences === []) {
            $this->warn($table ? "No sequences found for table \"{$table}\"." : 'No sequences found.');

            return self::SUCCESS;
        }

        foreach ($sequences as $sequence) {
            $maxId = DB::table($sequence->table_name)->max($sequence->column_name) ?? 0;

            DB::statement(
                'SELECT setval(?, ?, true)',
                [$sequence->sequence_name, $maxId]
            );

            $this->line("Synced {$sequence->table_name}.{$sequence->column_name} → {$maxId}");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
