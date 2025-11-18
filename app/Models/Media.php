<?php
// app/Models/Media.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'filename', 'type', 'file_path', 'url', 'alt',
        'size', 'mime_type', 'width', 'height', 'thumbnail'
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer'
    ];

    protected $appends = ['full_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL for the media file
     */
    public function getFullUrlAttribute()
    {
        if ($this->file_path && Storage::disk('minio')->exists($this->file_path)) {
            return Storage::disk('minio')->url($this->file_path);
        }
        
        return $this->url;
    }
}