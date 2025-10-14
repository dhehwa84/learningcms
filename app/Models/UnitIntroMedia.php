<?php

// app/Models/UnitIntroMedia.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitIntroMedia extends Model
{
    protected $table = 'unit_intro_media';
    protected $fillable = ['unit_id','media_id','sort_order','caption'];

    public function unit(){ return $this->belongsTo(Unit::class); }
    public function media(){ return $this->belongsTo(MediaLibrary::class,'media_id'); }
}
