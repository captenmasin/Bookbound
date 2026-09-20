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
                s.oid::regclass::text AS sequence_name,
                t.relname AS table_name,
                a.attname AS column_name
            FROM pg_class s
            JOIN (
                SELECT objid AS sequence_id, refobjid AS table_id, refobjsubid AS column_number
                FROM pg_depend
                WHERE classid = \'pg_class\'::regclass
                AND refclassid = \'pg_class\'::regclass
                AND deptype IN (\'a\', \'i\')
                UNION
                SELECT d.refobjid, ad.adrelid, ad.adnum
                FROM pg_attrdef ad
                JOIN pg_depend d ON d.objid = ad.oid
                WHERE d.classid = \'pg_attrdef\'::regclass
                AND d.refclassid = \'pg_class\'::regclass
            ) d ON d.sequence_id = s.oid
            JOIN pg_class t ON d.table_id = t.oid
            JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = d.column_number
            WHERE s.relkind = \'S\'
            AND t.relnamespace = (SELECT oid FROM pg_namespace WHERE nspname = \'public\')
            '.($table ? 'AND t.relname = ?' : '').'
            ORDER BY t.relname
        ', $table ? [$table] : []);

        if ($sequences === []) {
            $this->warn($table ? "No sequences found for table \"{$table}\"." : 'No sequences found.');

            return $table ? self::FAILURE : self::SUCCESS;
        }

        foreach ($sequences as $sequence) {
            $maxId = DB::transaction(function () use ($sequence): int {
                $table = DB::connection()->getQueryGrammar()->wrapTable($sequence->table_name);
                DB::statement("LOCK TABLE {$table} IN SHARE ROW EXCLUSIVE MODE");

                $maxId = DB::table($sequence->table_name)->max($sequence->column_name) ?? 0;

                DB::statement(
                    'SELECT setval(?, ?, ?)',
                    [$sequence->sequence_name, max($maxId, 1), $maxId > 0]
                );

                return $maxId;
            });

            $this->line("Synced {$sequence->table_name}.{$sequence->column_name} → {$maxId}");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
