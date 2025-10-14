<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rawfile extends Model
{
    protected $fillable = [
        'name',
        'path',
        'description', // ✅ Add this line
        'channel',
        'status',
    ];
}
