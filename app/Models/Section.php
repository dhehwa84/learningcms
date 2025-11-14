<?php
// app/Models/Section.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'name', 'description', 'order'
    ];

    protected $withCount = ['units'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class)->orderBy('order');
    }
}