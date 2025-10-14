<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
class PersonalAccessToken extends SanctumPersonalAccessToken
{
   protected $casts = [
        'abilities'    => 'array',     // add this
        'last_used_at' => 'datetime',  // add this (optional but recommended)
        'expires_at'   => 'datetime',
    ];
}