<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallCommand extends Command
{
    protected $signature = 'achivex:install';
    protected $description = 'Prepare the database and create the first administrator';

    public function handle(): int
    {
        $this->info('Creating the private admin schema...');
        DB::statement('create schema if not exists admin');

        $this->info('Running migrations...');
        Artisan::call('migrate', ['--force' => true], $this->output);

        $email = env('ADMIN_EMAIL') ?: $this->ask('Administrator email');
        $password = env('ADMIN_PASSWORD') ?: $this->secret('Administrator password (at least 8 characters)');
        if (strlen((string) $password) < 8) {
            $this->error('The password must be at least 8 characters.');

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->fill(['name' => env('ADMIN_NAME', 'Site Admin'), 'password' => $password, 'role' => 'administrator'])->save();
        $this->info("Administrator ready: {$email}");

        if (! file_exists(public_path('storage'))) {
            Artisan::call('storage:link', [], $this->output);
        }

        $this->newLine();
        $this->info('Done. Run "php artisan serve" and open http://localhost:8000/admin');

        return self::SUCCESS;
    }
}
