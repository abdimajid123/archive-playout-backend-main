<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'slot_id',
        'channel',
        'date',
        'start_time',
        'end_time',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function slot()
    {
        return $this->belongsTo(ScheduleSlot::class, 'slot_id');
    }
}

