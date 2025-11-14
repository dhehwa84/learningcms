<?php
// app/Models/Exercise.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'accordion_id', 'type', 'title', 'order', 
        'question_numbering', 'labels'
    ];

    protected $casts = [
        'labels' => 'array'
    ];

    protected $with = ['questions', 'dragMatchItems'];

    public function accordion()
    {
        return $this->belongsTo(Accordion::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function dragMatchItems()
    {
        return $this->hasMany(DragMatchItem::class)->orderBy('order');
    }

    /**
     * Get labels with fallback defaults
     */
    public function getLabelsAttribute($value)
    {
        $labels = json_decode($value, true) ?? [];
        
        // Base labels for all exercise types
        $defaults = [
            'submit_button' => 'Submit Answers',
            'clear_button' => 'Clear All',
            'correct_message' => 'Correct! Well done!',
            'incorrect_message' => 'Some answers are incorrect.',
            'incomplete_message' => 'Please answer all questions.',
        ];

        // Add drag-match specific defaults if this is a drag-match exercise
        if ($this->type === 'drag-match') {
            $defaults = array_merge($defaults, [
                'drag_instruction' => 'Drag items from the right column to match with items in the left column.',
                'left_column_label' => 'Match these:',
                'right_column_label' => 'Drag from here:',
            ]);
        }

        return array_merge($defaults, $labels);
    }

    /**
     * Set labels attribute - ensure proper JSON encoding
     */
    public function setLabelsAttribute($value)
    {
        if (is_array($value)) {
            // Remove null/empty values to use defaults
            $value = array_filter($value, function ($item) {
                return !is_null($item) && $item !== '';
            });
        }
        
        $this->attributes['labels'] = json_encode($value);
    }
}