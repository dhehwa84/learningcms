<?php
// app/Models/UsageTrackingEvent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageTrackingEvent extends Model
{
    use HasFactory;

    protected $table = 'usage_tracking_events';

    protected $fillable = [
        'session_id',
        'device_id',
        'project_id',
        'section_id',
        'unit_id',
        'event_type',
        'target_id',
        'target_name',
        'timestamp',
        'event_data',
        'device_info',
        'api_key_id'
    ];

    protected $casts = [
        'event_data' => 'array',
        'device_info' => 'array',
        'timestamp' => 'datetime',
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(UsageTrackingSession::class, 'session_id', 'session_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function apiKey()
    {
        return $this->belongsTo(TrackingApiKey::class, 'api_key_id', 'key');
    }
}