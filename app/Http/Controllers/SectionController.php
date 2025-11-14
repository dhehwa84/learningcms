<?php
// app/Http/Controllers/SectionController.php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class SectionController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
    }

    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $sections = $project->sections()->withCount('units')->get();

        return response()->json(['data' => $sections]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
        ]);

        $section = $project->sections()->create($validated);

        return response()->json($section, 201);
    }

    public function update(Request $request, Section $section): JsonResponse
    {
        $this->authorize('update', $section->project);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'sometimes|required|integer|min:0',
        ]);

        $section->update($validated);

        return response()->json($section);
    }

    public function destroy(Section $section): JsonResponse
    {
        $this->authorize('delete', $section->project);

        DB::transaction(function () use ($section) {
            $section->units()->each(function ($unit) {
                $unit->objectives()->delete();
                $unit->headerMedia()->delete();
                $unit->accordions()->each(function ($accordion) {
                    $accordion->content()->each(function ($content) {
                        $content->media()->delete();
                    });
                    $accordion->content()->delete();
                    $accordion->exercises()->each(function ($exercise) {
                        $exercise->questions()->delete();
                        $exercise->dragMatchItems()->delete();
                    });
                    $accordion->exercises()->delete();
                });
                $unit->accordions()->delete();
            });
            $section->units()->delete();
            $section->delete();
        });

        return response()->json(['message' => 'Section deleted successfully']);
    }
}