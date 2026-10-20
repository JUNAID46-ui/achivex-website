<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Remembers which contact messages and guide requests were already emailed to the owner.
// Rows that exist before this runs are marked as sent, so nobody gets a flood of old messages.
return new class extends Migration
{
    public function up(): void
    {
        foreach (['public.contact_submissions', 'public.whitepaper_requests'] as $table) {
            DB::statement("alter table {$table} add column if not exists notified_at timestamptz");
            DB::statement("update {$table} set notified_at = now() where notified_at is null");
        }
    }

    public function down(): void
    {
        foreach (['public.contact_submissions', 'public.whitepaper_requests'] as $table) {
            DB::statement("alter table {$table} drop column if exists notified_at");
        }
    }
};
