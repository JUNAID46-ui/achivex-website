<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $table = 'public.milestones';

    public $timestamps = false;
    protected $guarded = [];

}
