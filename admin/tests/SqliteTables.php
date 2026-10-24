<?php

namespace Tests;

use Illuminate\Support\Facades\Schema;

// Tables the SQLite based tests need, matching the production columns used by the code.
trait SqliteTables
{
    protected function createActivityLogs(): void
    {
        Schema::create('activity_logs', function ($t) {
            $t->id();
            $t->bigInteger('user_id')->nullable();
            $t->string('user_name')->nullable();
            $t->string('user_role')->nullable();
            $t->string('action');
            $t->string('module')->nullable();
            $t->string('subject_type')->nullable();
            $t->string('subject_label')->nullable();
            $t->string('subject_id')->nullable();
            $t->string('status')->default('success');
            $t->string('description')->nullable();
            $t->text('context')->nullable();
            $t->string('ip')->nullable();
            $t->timestamp('created_at')->nullable();
        });
    }
}
