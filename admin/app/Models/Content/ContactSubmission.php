<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $table = 'public.contact_submissions';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['created_at' => 'datetime', 'read_at' => 'datetime', 'starred' => 'boolean'];
}
