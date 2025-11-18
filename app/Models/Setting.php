<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['category', 'key', 'value', 'type', 'description'];

    protected $casts = [
        'value' => 'string', // We'll handle type casting manually
    ];
}