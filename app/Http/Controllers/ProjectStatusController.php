<?php
namespace App\Http\Controllers;

use App\Models\ProjectStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectStatusController extends Controller
{
    public function index(): JsonResponse
    {
        $statuses = ProjectStatus::orderBy('order')->get();
        
        return response()->json([
            'data' => $statuses
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'order' => 'required|integer',
        ]);

        $status = ProjectStatus::create($validated);

        return response()->json($status, 201);
    }

    public function update(Request $request, ProjectStatus $projectStatus): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|max:7',
            'order' => 'sometimes|integer',
        ]);

        $projectStatus->update($validated);

        return response()->json($projectStatus);
    }

    public function destroy(ProjectStatus $projectStatus): JsonResponse
    {
        // Check if status is used by any projects
        if ($projectStatus->projects()->exists()) {
            return response()->json([
                'error' => 'Cannot delete status that is assigned to projects'
            ], 422);
        }

        $projectStatus->delete();

        return response()->json(null, 204);
    }
}