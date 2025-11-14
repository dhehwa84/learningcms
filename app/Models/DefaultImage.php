<?php
// app/Models/DefaultImage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename', 'url', 'alt', 'category'
    ];
}