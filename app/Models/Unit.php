<?php
// app/Models/Unit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id', 'number', 'title', 'grade', 'theme', 'order', 
        'folder_name', 'header_config'
    ];

    protected $with = ['headerMedia', 'objectives', 'accordions'];

    protected $casts = [
        'header_config' => 'array',
    ];

    /**
     * Boot method for setting default folder_name
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($unit) {
            if (empty($unit->folder_name)) {
                // Generate folder_name based on title or use default pattern
                if (!empty($unit->title)) {
                    $folderName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $unit->title));
                    $folderName = substr($folderName, 0, 50); // Limit length
                    $unit->folder_name = $folderName;
                } else {
                    // Will be set to unit-{id} after creation
                    $unit->folder_name = 'unit-temp';
                }
            }
        });

        static::created(function ($unit) {
            // Update with actual ID if temporary name was used
            if ($unit->folder_name === 'unit-temp') {
                $unit->update(['folder_name' => 'unit-' . $unit->id]);
            }
        });
    }

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

    /**
     * Get header_config with default values
     */
    public function getHeaderConfigAttribute($value)
    {
        $config = json_decode($value, true) ?? [];
        
        return array_merge([
            'main_heading' => 'Main Heading goes here.',
            'sub_heading' => 'Sub Heading goes here.',
            'objectives_label' => 'Objectives label goes here.',
            'objectives_description' => 'Objectives description goes here.',
        ], $config);
    }

    /**
     * Set header_config attribute - ensure proper JSON encoding
     */
    public function setHeaderConfigAttribute($value)
    {
        if (is_array($value)) {
            // Remove null/empty values to use defaults
            $value = array_filter($value, function ($item) {
                return !is_null($item) && $item !== '';
            });
        }
        
        $this->attributes['header_config'] = json_encode($value);
    }

    /**
     * Set folder_name attribute - ensure it's URL-safe
     */
    public function setFolderNameAttribute($value)
    {
        if (!empty($value)) {
            // Convert to URL-safe format
            $folderName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $value));
            $folderName = preg_replace('/-+/', '-', $folderName); // Remove multiple dashes
            $folderName = trim($folderName, '-'); // Remove leading/trailing dashes
            $this->attributes['folder_name'] = substr($folderName, 0, 100); // Limit length
        }
    }
}