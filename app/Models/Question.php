<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'exercise_id', 'prompt', 'type', 'config', 'marks', 'sort_order',
    ];

    protected $casts = [
        'config' => 'array', // options, correct answers, etc.
    ];

    // Relations
    public function exercise() { return $this->belongsTo(Exercise::class); }
}
