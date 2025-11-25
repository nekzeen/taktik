<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuleDiscussionReply extends Model
{
    use SoftDeletes;

    protected $fillable = ['discussion_id', 'user_id', 'content', 'status'];

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(RuleDiscussion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
