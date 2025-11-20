<?php
// app/Models/UsageTrackingExercise.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageTrackingExercise extends Model
{
    use HasFactory;

    protected $table = 'usage_tracking_exercises';

    protected $fillable = [
        'session_id',
        'device_id',
        'project_id',
        'section_id',
        'unit_id',
        'exercise_id',
        'exercise_type',
        'started_at',
        'completed_at',
        'time_spent_seconds',
        'answer_data',
        'is_correct',
        'score',
        'attempts',
        'device_info',
        'api_key_id'
    ];

    protected $casts = [
        'answer_data' => 'array',
        'device_info' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_correct' => 'boolean',
        'score' => 'decimal:2',
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