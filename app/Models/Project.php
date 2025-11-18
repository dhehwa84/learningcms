<?php
// app/Models/Project.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'description', 'language', 'status_id',
        'expected_start_date', 'completion_date', 'sign_off_person_id'
    ];

    protected $withCount = ['sections', 'units'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function signOffPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sign_off_person_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(ProjectTeamMember::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    public function units(): HasManyThrough
    {
        return $this->hasManyThrough(Unit::class, Section::class);
    }

    /**
     * Check if user is a team member of this project
     */
    public function isTeamMember($userId): bool
    {
        return $this->teamMembers()->where('user_id', $userId)->exists();
    }

    /**
     * Check if user has specific role in this project
     */
    public function userHasRole($userId, $role): bool
    {
        return $this->teamMembers()
            ->where('user_id', $userId)
            ->where('role', $role)
            ->exists();
    }

    /**
     * Get user's role in this project
     */
    public function getUserRole($userId): ?string
    {
        $teamMember = $this->teamMembers()
            ->where('user_id', $userId)
            ->first();
            
        return $teamMember ? $teamMember->role : null;
    }
}