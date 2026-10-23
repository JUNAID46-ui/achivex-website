<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// A website managed from this admin (Settings, Sites). Lives in the main database.
class Site extends Model
{
    protected $table = 'sites';
    protected $guarded = [];
    protected $hidden = ['db_password'];

    protected function casts(): array
    {
        return ['db_password' => 'encrypted', 'db_port' => 'integer', 'checked_at' => 'datetime'];
    }

    public function getConnectionName()
    {
        return \App\Support\Sites::mainConnection() ?? parent::getConnectionName();
    }

    public function host(): string
    {
        return parse_url($this->public_url ?: $this->site_url, PHP_URL_HOST) ?: $this->site_url;
    }
}
