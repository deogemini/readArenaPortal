<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    protected $fillable = [
        'name',
        'price_tsh',
        'games_count',
        'reward_label',
        'region_scope',
        'status',
    ];
}
