<?php
// app/Http/Controllers/ProjectController.php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['status', 'teamMembers.user', 'signOffPerson'])
            ->withCount('sections');

        // If user is not admin, only show projects they are team members of
        if (!auth()->user()->isAdmin()) {
            $query->whereHas('teamMembers', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        // Apply filters
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('lead_id')) {
            $query->whereHas('teamMembers', function ($q) use ($request) {
                $q->where('user_id', $request->lead_id)->where('role', 'lead');
            });
        }

        if ($request->has('team_member_id')) {
            $query->whereHas('teamMembers', function ($q) use ($request) {
                $q->where('user_id', $request->team_member_id);
            });
        }

        $projects = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $projects->items(),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
                'total_pages' => $projects->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'language' => 'required|string|max:10',
            'status_id' => 'required|exists:project_statuses,id',
            'expected_start_date' => 'nullable|date',
            'team' => 'required|array|min:1',
            'team.*.user_id' => 'required|exists:users,id',
            'team.*.role' => 'required|in:lead,member,viewer',
            'sign_off_person_id' => 'nullable|exists:users,id',
        ]);

        // Ensure at least one lead
        $hasLead = collect($validated['team'])->contains('role', 'lead');
        if (!$hasLead) {
            return response()->json([
                'error' => 'Project must have at least one team member with lead role'
            ], 422);
        }

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'language' => $validated['language'],
            'status_id' => $validated['status_id'],
            'expected_start_date' => $validated['expected_start_date'],
            'sign_off_person_id' => $validated['sign_off_person_id'],
            'user_id' => auth()->id(),
        ]);

        // Add team members
        foreach ($validated['team'] as $member) {
            $project->teamMembers()->create($member);
        }

        return response()->json($project->load(['status', 'teamMembers.user', 'signOffPerson']), 201);
    }

    public function show(Project $project): JsonResponse
    {
        // Check if user has access to this project
        $teamMember = $project->teamMembers()->where('user_id', auth()->id())->first();
        if (!$teamMember) {
            return response()->json([
                'error' => 'Access denied. You are not a member of this project team.'
            ], 403);
        }

        $project->load([
            'status', 
            'teamMembers.user', 
            'signOffPerson',
            'sections.units'
        ]);

        return response()->json($project);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'language' => 'sometimes|string|max:10',
            'status_id' => 'sometimes|exists:project_statuses,id',
            'expected_start_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
        ]);

        $project->update($validated);

        return response()->json($project->fresh(['status', 'teamMembers.user', 'signOffPerson']));
    }
}