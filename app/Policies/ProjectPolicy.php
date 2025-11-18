<?php
// app/Policies/ProjectPolicy.php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        // User can view if they are the creator, admin, or team member
        return $project->user_id === $user->id || 
               $user->isAdmin() || 
               $project->isTeamMember($user->id);
    }

    public function update(User $user, Project $project): bool
    {
        // User can update if they are the creator, admin, or team member with lead/member role
        if ($project->user_id === $user->id || $user->isAdmin()) {
            return true;
        }

        $userRole = $project->getUserRole($user->id);
        return in_array($userRole, ['lead', 'member']);
    }

    public function delete(User $user, Project $project): bool
    {
        // Only creator or admin can delete
        return $project->user_id === $user->id || $user->isAdmin();
    }

    public function manageTeam(User $user, Project $project): bool
    {
        // User can manage team if they are the creator, admin, or team lead
        if ($project->user_id === $user->id || $user->isAdmin()) {
            return true;
        }

        return $project->userHasRole($user->id, 'lead');
    }
}