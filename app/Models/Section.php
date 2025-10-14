<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id', 'title', 'slug', 'sort_order', 'menu_icon_media_id',
    ];

    // Relations
    public function project()    { return $this->belongsTo(Project::class); }
    public function units()      { return $this->hasMany(Unit::class)->orderBy('number'); }
    public function menuIcon()   { return $this->belongsTo(MediaLibrary::class, 'menu_icon_media_id'); }

    // Convenience
    public function getRouteKeyName() { return 'slug'; }
}
