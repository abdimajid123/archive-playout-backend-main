<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'channel',
        'season',
        'episode',
        'type',
        'category',
        'year',
        'duration',
        'country',
    ];


    protected $casts = [
    'category' => 'array', // ✅ handles JSON <=> array
    ];


    public function schedules()
    {
        return $this->hasMany(ContentSchedule::class);
    }

}

