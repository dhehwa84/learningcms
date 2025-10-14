<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'section_id', 'number', 'title', 'subtitle', 'overview',
        'level', 'theme', 'hero_media_id', 'audio_media_id', 'video_media_id',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'bool',
        // If you store objectives JSON inside overview, cast to array:
        // 'overview' => 'array',
    ];

    // Relations
    public function section()     { return $this->belongsTo(Section::class); }
    public function accordions()  { return $this->hasMany(Accordion::class)->orderBy('sort_order'); }

    // Media
    public function heroMedia()   { return $this->belongsTo(MediaLibrary::class, 'hero_media_id'); }
    public function audioMedia()  { return $this->belongsTo(MediaLibrary::class, 'audio_media_id'); }
    public function videoMedia()  { return $this->belongsTo(MediaLibrary::class, 'video_media_id'); }

    // Siblings for numbered nav
    public function siblings()
    {
        return $this->section->units()->select('id','number','title')->get();
    }
    public function introMedia()
    {
        return $this->hasMany(\App\Models\UnitIntroMedia::class)->orderBy('sort_order');
    }
}
