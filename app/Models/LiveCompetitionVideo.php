<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveCompetitionVideo extends Model
{
    protected $fillable = [
        'title',
        'live_show_id',
        'video_path',
        'uploaded_by',
        'status',
    ];

    public function liveShow()
    {
        return $this->belongsTo(LiveShow::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
