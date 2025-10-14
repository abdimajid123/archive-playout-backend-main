<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentCooldown extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'channel',
        'last_used_at',
        'cooldown_days',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
