<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class SchemaIntrospector
{
    public static function tableSchema(string $table): array
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'pgsql'  => self::pgTableSchema($table),
            'mysql', 'mariadb' => self::myTableSchema($table),
            default  => self::genericTableSchema($table), // fallback
        };
    }

    /** PostgreSQL (handles enums properly) */
    protected static function pgTableSchema(string $table): array
    {
        // Pull all columns in one shot
        $cols = DB::select("
            SELECT column_name, data_type, udt_name
            FROM information_schema.columns
            WHERE table_name = ?
            ORDER BY ordinal_position
        ", [$table]);

        // Filter out unwanted columns
        $cols = array_filter($cols, fn($c) => !in_array($c->column_name, ['id', 'request_id']));

        $out = [];
        // Collect enum type names used in this table
        $enumTypes = [];
        foreach ($cols as $c) {
            if ($c->data_type === 'USER-DEFINED') {
                $enumTypes[] = $c->udt_name;
            }
        }
        $enumTypes = array_values(array_unique($enumTypes));

        // Map enum type -> values (1 query per distinct enum type)
        $enumMap = [];
        foreach ($enumTypes as $typname) {
            $rows = DB::select("
                SELECT e.enumlabel AS value
                FROM pg_type t
                JOIN pg_enum e ON t.oid = e.enumtypid
                WHERE t.typname = ?
                ORDER BY e.enumsortorder
            ", [$typname]);
            $enumMap[$typname] = array_map(fn($r) => $r->value, $rows);
        }

        foreach ($cols as $c) {
            if ($c->data_type === 'USER-DEFINED' && isset($enumMap[$c->udt_name])) {
                $out[$c->column_name] = [
                    'type'   => 'enum',
                    'values' => $enumMap[$c->udt_name],
                ];
            } else {
                $out[$c->column_name] = [
                    'type' => $c->data_type,
                ];
            }
        }
        return $out;
    }

    /** MySQL/MariaDB (parses enum values from SHOW COLUMNS) */
    protected static function myTableSchema(string $table): array
    {
        $rows = DB::select("SHOW COLUMNS FROM `$table`");

        // Filter unwanted columns
        $rows = array_filter($rows, fn($r) => !in_array($r->Field, ['id', 'request_id']));

        $out = [];
        foreach ($rows as $r) {
            $type = strtolower($r->Type);
            if (str_starts_with($type, 'enum(')) {
                if (preg_match('/^enum\((.*)\)$/', $type, $m)) {
                    $vals = array_map(fn($v) => trim($v, "'"), explode(',', $m[1]));
                } else {
                    $vals = [];
                }
                $out[$r->Field] = [
                    'type'   => 'enum',
                    'values' => $vals,
                ];
            } else {
                $out[$r->Field] = [
                    'type' => $r->Type,
                ];
            }
        }
        return $out;
    }

    /** Generic fallback using Laravel schema builder (no enum values) */
    protected static function genericTableSchema(string $table): array
    {
        $builder = DB::connection()->getSchemaBuilder();
        $cols = $builder->getColumnListing($table);

        // Filter unwanted columns
        $cols = array_filter($cols, fn($col) => !in_array($col, ['id', 'request_id']));

        $out = [];
        foreach ($cols as $col) {
            $out[$col] = [
                'type' => $builder->getColumnType($table, $col),
            ];
        }
        return $out;
    }
}
