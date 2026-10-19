<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $table = 'public.case_studies';
    protected $primaryKey = 'slug';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['services' => \App\Casts\PgTextArray::class, 'challenge' => 'array', 'solution' => 'array', 'results' => 'array', 'published' => 'boolean'];
}
