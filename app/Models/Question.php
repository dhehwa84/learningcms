<?php
// app/Models/Question.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'exercise_id', 'question', 'type', 'order',
        'options', 'correct_answer', 'correct_answers'
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array'
    ];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}