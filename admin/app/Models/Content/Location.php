<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'public.locations';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['is_hq' => 'boolean'];
}
