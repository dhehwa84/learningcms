<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exercise extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'block_id', 'title', 'type', 'config', 'marks', 'sort_order',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    // Relations
    public function block()     { return $this->belongsTo(Block::class); }
    public function questions() { return $this->hasMany(Question::class)->orderBy('sort_order'); }

    // Scope: quick filter by type (mcq, radio, checkbox, mix, etc.)
    public function scopeType($q, string $type) { return $q->where('type', $type); }
}
