<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGatewaySetting extends Model
{
    protected $hidden = ['client_secret'];

    protected $fillable = [
        'base_url',
        'client_id',
        'client_secret',
        'sender_id',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'client_secret' => 'encrypted',
            'is_enabled' => 'boolean',
        ];
    }
}
