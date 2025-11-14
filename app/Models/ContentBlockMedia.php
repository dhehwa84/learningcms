<?php
// app/Models/ContentBlockMedia.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentBlockMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_block_id', 'type', 'url', 'alt', 'caption', 'order'
    ];

    public function contentBlock()
    {
        return $this->belongsTo(ContentBlock::class);
    }
}