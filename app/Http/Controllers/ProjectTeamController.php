<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTeamMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class ProjectTeamController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    public function addMember(Request $request, Project $project): JsonResponse
    {
        $this->authorize('manageTeam', $project);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:lead,member,viewer',
        ]);

        // Check if user is already a team member
        if ($project->teamMembers()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json([
                'error' => 'User is already a team member'
            ], 422);
        }

        $teamMember = $project->teamMembers()->create($validated);

        return response()->json([
            'data' => $teamMember->load('user')
        ], 201);
    }

    public function updateMemberRole(Request $request, Project $project, ProjectTeamMember $teamMember): JsonResponse
    {
        $this->authorize('manageTeam', $project);

        $validated = $request->validate([
            'role' => 'required|in:lead,member,viewer',
        ]);

        $teamMember->update($validated);

        return response()->json([
            'data' => $teamMember->load('user')
        ]);
    }

    public function removeMember(Project $project, ProjectTeamMember $teamMember): JsonResponse
    {
        $this->authorize('manageTeam', $project);

        // Ensure at least one lead remains
        if ($teamMember->role === 'lead' && $project->teamMembers()->where('role', 'lead')->count() <= 1) {
            return response()->json([
                'error' => 'Project must have at least one lead'
            ], 422);
        }

        $teamMember->delete();

        return response()->json(null, 204);
    }

    public function updateSignOffPerson(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $project->update(['sign_off_person_id' => $validated['user_id']]);

        return response()->json([
            'data' => $project->fresh('signOffPerson')
        ]);
    }

    public function checkAccess(Project $project): JsonResponse
    {
        $user = auth()->user();
        $teamMember = $project->teamMembers()->where('user_id', $user->id)->first();

        return response()->json([
            'hasAccess' => (bool) $teamMember,
            'role' => $teamMember?->role,
            'permissions' => [
                'canView' => true,
                'canEdit' => in_array($teamMember?->role, ['lead', 'member']),
                'canDelete' => $teamMember?->role === 'lead',
                'canManageTeam' => $teamMember?->role === 'lead',
            ]
        ]);
    }
}