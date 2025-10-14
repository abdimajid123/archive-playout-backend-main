<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleSlot extends Model
{
    protected $fillable = [
        'channel',
        'date',
        'start_time',
        'end_time',
    ];

    // Relationship: one slot has many content schedules
    public function schedules()
    {
        return $this->hasMany(ContentSchedule::class, 'slot_id');
    }
}
