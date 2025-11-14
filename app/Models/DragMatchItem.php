<?php
// app/Models/DragMatchItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DragMatchItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'exercise_id', 'order', 'left_type', 'left_value', 'left_alt',
        'right_type', 'right_value', 'right_alt'
    ];

    protected $appends = ['left_side', 'right_side'];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    /**
     * Get left_side attribute in frontend format
     */
    public function getLeftSideAttribute()
    {
        return [
            'type' => $this->left_type,
            'value' => $this->left_value,
            'alt' => $this->left_alt,
        ];
    }

    /**
     * Get right_side attribute in frontend format
     */
    public function getRightSideAttribute()
    {
        return [
            'type' => $this->right_type,
            'value' => $this->right_value,
            'alt' => $this->right_alt,
        ];
    }
}