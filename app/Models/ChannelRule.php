<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelRule extends Model
{
    protected $fillable = [
        'channel',
        'min_content_per_day',
        'max_content_per_day',
        'slot_duration_minutes',
        'preferred_content_types',
        'scheduling_algorithm',
        'cooldown_days',
    ];

    protected $casts = [
        'preferred_content_types' => 'array',
    ];
}
