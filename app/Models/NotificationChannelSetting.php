<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationChannelSetting extends Model
{
    protected $hidden = ['smtp_password', 'firebase_service_account_json'];

    protected $fillable = [
        'email_enabled',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'mail_from_address',
        'mail_from_name',
        'push_enabled',
        'firebase_project_id',
        'firebase_service_account_json',
    ];

    protected function casts(): array
    {
        return [
            'email_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'smtp_password' => 'encrypted',
            'firebase_service_account_json' => 'encrypted',
        ];
    }
}
