<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuleDiscussion extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'category_id', 'title', 'description', 'image_path', 'status', 'is_pinned'];

    protected $casts = ['is_pinned' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RuleDiscussionCategory::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(RuleDiscussionReply::class, 'discussion_id');
    }

    public function approvedReplies(): HasMany
    {
        return $this->replies()->where('status', 'approved');
    }
}
