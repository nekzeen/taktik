<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'source_text',
        'translated_text',
        'locale',
        'resource_type',
        'resource_id',
        'field',
        'status',
    ];
}
