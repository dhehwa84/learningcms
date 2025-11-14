<?php
// app/Models/Unit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id', 'number', 'title', 'grade', 'theme', 'order'
    ];

    protected $with = ['headerMedia', 'objectives', 'accordions'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function headerMedia()
    {
        return $this->hasOne(UnitHeaderMedia::class);
    }

    public function objectives()
    {
        return $this->hasMany(Objective::class)->orderBy('order');
    }

    public function accordions()
    {
        return $this->hasMany(Accordion::class)->orderBy('order');
    }
}