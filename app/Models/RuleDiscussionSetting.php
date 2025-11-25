<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuleDiscussionSetting extends Model
{
    protected $fillable = ['max_images_per_discussion', 'enable_notifications', 'enable_moderation', 'auto_archive_days'];

    protected $casts = [
        'enable_notifications' => 'boolean',
        'enable_moderation' => 'boolean',
    ];

    public static function getSettings()
    {
        return self::firstOrCreate(['id' => 1], [
            'max_images_per_discussion' => 1,
            'enable_notifications' => true,
            'enable_moderation' => true,
            'auto_archive_days' => 90,
        ]);
    }
}
