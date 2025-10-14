<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'grade', 'theme', 'school_name', 'webhook_url', 'created_by',
    ];

    // Relations
    public function sections() { return $this->hasMany(Section::class)->orderBy('sort_order'); }
    public function media()    { return $this->hasMany(MediaLibrary::class); }
    public function user()     { return $this->belongsTo(User::class, 'created_by'); }

    // Convenience
    public function getRouteKeyName() { return 'slug'; }
}
