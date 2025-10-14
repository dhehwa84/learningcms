<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Block extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'accordion_id', 'type', 'payload', 'sort_order',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    // Relations
    public function accordion() { return $this->belongsTo(Accordion::class); }
    public function exercise()  { return $this->hasOne(Exercise::class); }

    // Helpers
    public function isExercise(): bool { return $this->type === 'exercise'; }
}
