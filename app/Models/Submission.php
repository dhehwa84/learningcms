<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id','section_id','unit_id','exercise_id',
        'student_name','student_number','payload',
        'score','max_score','posted_to_webhook_at','post_status','last_error',
    ];

    protected $casts = [
        'payload' => 'array',
        'posted_to_webhook_at' => 'datetime',
    ];

    // Relations
    public function project()  { return $this->belongsTo(Project::class); }
    public function section()  { return $this->belongsTo(Section::class); }
    public function unit()     { return $this->belongsTo(Unit::class); }
    public function exercise() { return $this->belongsTo(Exercise::class); }
}
