<?php

namespace App\Database;

use Illuminate\Database\PostgresConnection;
use PDO;

// The database is far from the admin (Supabase in the US), so every round trip counts.
// With emulated prepares each query is one trip instead of three (prepare, execute, deallocate).
// Values are sent as text so Postgres can cast them, which keeps booleans like published = '1' working.
class FastPostgresConnection extends PostgresConnection
{
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                match (true) {
                    $value === null => PDO::PARAM_NULL,
                    is_resource($value) => PDO::PARAM_LOB,
                    default => PDO::PARAM_STR,
                },
            );
        }
    }
}
