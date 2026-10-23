<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

// An IP address blocked from the admin after repeated failed sign ins. See Services\Security\LoginGuard.
class BlockedIp extends Model
{
    protected $guarded = [];
    protected $casts = [
        'emails' => 'array', 'first_attempt_at' => 'datetime', 'blocked_at' => 'datetime', 'expires_at' => 'datetime',
        'unblocked_at' => 'datetime', 'notified_at' => 'datetime',
    ];

    // Blocks protect this admin itself, so they stay on the main database while another website is selected.
    public function getConnectionName()
    {
        return \App\Support\Sites::mainConnection() ?? parent::getConnectionName();
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNull('unblocked_at')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isActive(): bool
    {
        return ! $this->unblocked_at && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
