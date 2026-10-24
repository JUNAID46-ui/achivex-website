<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Workflow status and who created / last changed a content record.
class ContentMeta extends Model
{
    protected $table = 'content_meta';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $guarded = [];

    // Composite key (resource, record_key): saves go through updateOrInsert in ContentWorkflow.
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('resource', $this->resource)->where('record_key', $this->record_key);
    }
}
