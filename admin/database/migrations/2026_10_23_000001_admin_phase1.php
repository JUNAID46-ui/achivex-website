<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Phase 1 admin features: notifications, content revisions and workflow, audit history,
// backup history, captured application errors, a richer append only activity log,
// and scheduled publishing for website content. Everything here is additive.
return new class extends Migration
{
    // Public content tables that get scheduled publishing, with their read policy names.
    private const SCHEDULED = [
        'blog_posts' => 'Public can read published blog posts',
        'case_studies' => 'Public can read published case studies',
        'news_posts' => 'Public can read published news',
        'whitepapers' => 'Public can read published whitepapers',
        'job_openings' => 'Public can read published jobs',
        'team_members' => 'Public can read published team',
        'pages' => 'Public can read published pages',
    ];

    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('user_name', 120)->nullable();
            $table->string('user_role', 30)->nullable();
            $table->string('module', 60)->nullable();
            $table->string('subject_id', 191)->nullable();
            $table->string('status', 20)->default('success');
            $table->jsonb('context')->nullable();
            $table->index(['action', 'created_at']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 60);
            $table->string('severity', 20)->default('info');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('module', 60)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('dedupe_key', 191)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'archived_at', 'read_at']);
            $table->index(['dedupe_key', 'created_at']);
        });

        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('resource', 60);
            $table->string('record_key', 191);
            $table->string('action', 20);
            $table->jsonb('data');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name', 120)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['resource', 'record_key', 'created_at']);
        });

        Schema::create('content_meta', function (Blueprint $table) {
            $table->string('resource', 60);
            $table->string('record_key', 191);
            $table->string('status', 20)->default('published');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by_name', 120)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('updated_by_name', 120)->nullable();
            $table->timestamps();
            $table->primary(['resource', 'record_key']);
        });

        Schema::create('audit_runs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('status', 20)->default('running');
            $table->jsonb('summary')->nullable();
            $table->jsonb('findings')->nullable();
            $table->text('error')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->index(['type', 'started_at']);
        });

        Schema::create('backup_runs', function (Blueprint $table) {
            $table->id();
            $table->string('trigger', 20)->default('manual');
            $table->jsonb('scope');
            $table->string('status', 20)->default('running');
            $table->string('path', 300)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->jsonb('row_counts')->nullable();
            $table->text('error')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index('created_at');
        });

        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->default('error');
            $table->string('exception', 191);
            $table->string('message', 500);
            $table->string('location', 300)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('path', 300)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Activity log rows can be added but never edited or removed. The one exception is the
        // user_id becoming null when a user account is deleted; the saved user_name keeps the record readable.
        DB::unprepared(<<<'SQL'
            create or replace function admin.activity_logs_append_only() returns trigger language plpgsql as $$
            begin
              if tg_op = 'UPDATE' and new.user_id is null and old.user_id is not null
                 and (to_jsonb(new) - 'user_id') = (to_jsonb(old) - 'user_id') then
                return new;
              end if;
              raise exception 'Activity log entries cannot be changed or deleted';
            end $$;
            drop trigger if exists activity_logs_append_only on admin.activity_logs;
            create trigger activity_logs_append_only before update or delete on admin.activity_logs
              for each row execute function admin.activity_logs_append_only();
        SQL);

        // Scheduled publishing: a published row with a future publish_at stays hidden from the
        // website until that time, enforced by the same read policy the website already uses.
        foreach (self::SCHEDULED as $table => $policy) {
            DB::statement("alter table public.{$table} add column if not exists publish_at timestamptz");
            $exists = DB::table('pg_policies')->where('schemaname', 'public')->where('tablename', $table)->where('policyname', $policy)->exists();
            if ($exists) {
                DB::statement("alter policy \"{$policy}\" on public.{$table} using (published and (publish_at is null or publish_at <= now()))");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            foreach (self::SCHEDULED as $table => $policy) {
                $exists = DB::table('pg_policies')->where('schemaname', 'public')->where('tablename', $table)->where('policyname', $policy)->exists();
                if ($exists) {
                    DB::statement("alter policy \"{$policy}\" on public.{$table} using (published)");
                }
                DB::statement("alter table public.{$table} drop column if exists publish_at");
            }
            DB::unprepared('drop trigger if exists activity_logs_append_only on admin.activity_logs; drop function if exists admin.activity_logs_append_only();');
        }
        foreach (['error_logs', 'backup_runs', 'audit_runs', 'content_meta', 'content_revisions', 'notifications'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['action', 'created_at']);
            $table->dropColumn(['user_name', 'user_role', 'module', 'subject_id', 'status', 'context']);
        });
    }
};
