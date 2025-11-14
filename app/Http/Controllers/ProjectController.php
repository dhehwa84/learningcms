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

    public function index(): JsonResponse
    {
        $projects = Project::where('user_id', auth()->id())
            ->withCount('sections')
            ->get();

        return response()->json(['data' => $projects]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'language' => 'required|string|max:10',
        ]);

        $project = Project::create([
            ...$validated,
            'user_id' => auth()->id()
        ]);

        return response()->json($project, 201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load(['sections.units']);

        return response()->json($project);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'language' => 'sometimes|required|string|max:10',
        ]);

        $project->update($validated);

        return response()->json($project);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }
}