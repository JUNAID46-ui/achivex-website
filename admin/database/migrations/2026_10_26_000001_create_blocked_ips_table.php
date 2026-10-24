<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// IP addresses blocked from the admin after repeated failed sign ins (Security Center).
// A row is kept after the block ends, so the history stays visible.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 64)->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->json('emails')->nullable();
            $table->string('user_agent', 300)->nullable();
            $table->string('reason', 200)->nullable();
            $table->timestamp('first_attempt_at')->nullable();
            $table->timestamp('blocked_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('unblocked_at')->nullable();
            $table->string('unblocked_by', 120)->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
    }
};
