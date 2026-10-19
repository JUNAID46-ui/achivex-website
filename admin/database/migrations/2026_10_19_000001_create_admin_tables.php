<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);          // created, updated, deleted, login, settings ...
            $table->string('subject_type', 80)->nullable();
            $table->string('subject_label')->nullable();
            $table->string('description')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('filename');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Inbox state on the public form tables
        DB::statement('alter table public.contact_submissions add column if not exists read_at timestamptz');
        DB::statement('alter table public.contact_submissions add column if not exists starred boolean not null default false');
        DB::statement('alter table public.whitepaper_requests add column if not exists handled_at timestamptz');
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('activity_logs');
    }
};
