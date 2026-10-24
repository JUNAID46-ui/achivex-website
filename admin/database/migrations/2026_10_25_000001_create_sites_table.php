<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Other websites this admin can manage (Sites). Each one has its own database and hosting;
// this table only stores how to reach them. Passwords and keys are stored encrypted.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('site_url', 500);
            $table->string('public_url', 500)->nullable();
            $table->string('db_host', 255);
            $table->unsignedInteger('db_port')->default(5432);
            $table->string('db_database', 120)->default('postgres');
            $table->string('db_username', 255);
            $table->text('db_password');
            $table->string('db_sslmode', 20)->default('require');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('new');
            $table->text('status_message')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
