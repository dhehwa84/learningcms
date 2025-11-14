<?php
// app/Models/UnitHeaderMedia.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitHeaderMedia extends Model
{
    use HasFactory;

    protected $table = 'unit_header_media';

    protected $fillable = [
        'unit_id', 'type', 'url', 'alt', 'caption'
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}