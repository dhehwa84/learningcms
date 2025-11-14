<?php
// app/Models/ContentBlock.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'accordion_id', 'type', 'order', 'content', 'layout'
    ];

    public function accordion()
    {
        return $this->belongsTo(Accordion::class);
    }

    public function media()
    {
        return $this->hasMany(ContentBlockMedia::class)->orderBy('order');
    }
}