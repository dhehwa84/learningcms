<?php
// app/Models/UsageTrackingFeedback.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageTrackingFeedback extends Model
{
    use HasFactory;

    protected $table = 'usage_tracking_feedback';

    protected $fillable = [
        'session_id',
        'device_id',
        'project_id',
        'section_id',
        'unit_id',
        'user_type',
        'feedback_type',
        'rating',
        'message',
        'contact_email',
        'status',
        'device_info',
        'api_key_id'
    ];

    protected $casts = [
        'device_info' => 'array',
        'rating' => 'integer',
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

    public function apiKey()
    {
        return $this->belongsTo(TrackingApiKey::class, 'api_key_id', 'key');
    }
}