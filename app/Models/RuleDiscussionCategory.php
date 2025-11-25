<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RuleDiscussionCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'order', 'is_active'];

    public function discussions(): HasMany
    {
        return $this->hasMany(RuleDiscussion::class, 'category_id');
    }
}
