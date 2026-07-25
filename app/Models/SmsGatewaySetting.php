<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGatewaySetting extends Model
{
    protected $fillable = [
        'base_url',
        'client_id',
        'client_secret',
        'sender_id',
        'is_enabled',
    ];
}
