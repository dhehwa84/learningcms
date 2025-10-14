<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MediaLibrary extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'media_library';

    protected $fillable = [
        'project_id','path','type','title','description','tags',
        'width','height','duration_ms','used_count',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    // Relations
    public function project() { return $this->belongsTo(Project::class); }

    // Where this media is used (optional if you add a media_usages table later)
    // public function usages() { ... }
}
