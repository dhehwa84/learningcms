<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Accordion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id', 'title', 'icon_media_id', 'sort_order',
    ];

    // Relations
    public function unit()     { return $this->belongsTo(Unit::class); }
    public function blocks()   { return $this->hasMany(Block::class)->orderBy('sort_order'); }
    public function iconMedia(){ return $this->belongsTo(MediaLibrary::class, 'icon_media_id'); }
}
