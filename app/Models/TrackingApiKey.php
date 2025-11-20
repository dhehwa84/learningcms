<?php
// app/Models/TrackingApiKey.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingApiKey extends Model
{
    use HasFactory;

    protected $table = 'tracking_api_keys';

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_active',
        'allowed_projects',
        'rate_limit',
        'last_used_at',
        'usage_count'
    ];

    protected $casts = [
        'allowed_projects' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    // Relationships
    public function sessions()
    {
        return $this->hasMany(UsageTrackingSession::class, 'api_key_id', 'key');
    }

    public function exercises()
    {
        return $this->hasMany(UsageTrackingExercise::class, 'api_key_id', 'key');
    }

    public function events()
    {
        return $this->hasMany(UsageTrackingEvent::class, 'api_key_id', 'key');
    }

    public function feedback()
    {
        return $this->hasMany(UsageTrackingFeedback::class, 'api_key_id', 'key');
    }

    // Helper methods
    public function incrementUsage()
    {
        $this->update([
            'usage_count' => $this->usage_count + 1,
            'last_used_at' => now(),
        ]);
    }

    public function isWithinRateLimit()
    {
        // Simple rate limiting - in production, use Laravel RateLimiter
        return $this->usage_count < 1000000; // High limit for now
    }
}