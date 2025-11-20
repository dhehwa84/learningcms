<?php
// app/Models/UsageTrackingSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageTrackingSession extends Model
{
    use HasFactory;

    protected $table = 'usage_tracking_sessions';

    protected $fillable = [
        'session_id',
        'device_id',
        'project_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'device_info',
        'api_key_id'
    ];

    protected $casts = [
        'device_info' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function exercises()
    {
        return $this->hasMany(UsageTrackingExercise::class, 'session_id', 'session_id');
    }

    public function events()
    {
        return $this->hasMany(UsageTrackingEvent::class, 'session_id', 'session_id');
    }

    public function feedback()
    {
        return $this->hasMany(UsageTrackingFeedback::class, 'session_id', 'session_id');
    }

    public function apiKey()
    {
        return $this->belongsTo(TrackingApiKey::class, 'api_key_id', 'key');
    }
}