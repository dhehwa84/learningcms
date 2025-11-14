<?php
// app/Models/Accordion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accordion extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id', 'title', 'icon_url', 'order'
    ];

    protected $with = ['content', 'exercises'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function content()
    {
        return $this->hasMany(ContentBlock::class)->orderBy('order');
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class)->orderBy('order');
    }
}