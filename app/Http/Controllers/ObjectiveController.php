<?php
// app/Http/Controllers/ObjectiveController.php

namespace App\Http\Controllers;

use App\Models\Objective;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class ObjectiveController extends Controller
{
     use AuthorizesRequests, ValidatesRequests;
    /**
     * Update the specified objective
     */
    public function update(Request $request, Objective $objective): JsonResponse
    {
        $this->authorize('update', $objective->unit->section->project);

        $validated = $request->validate([
            'text' => 'sometimes|required|string',
            'order' => 'sometimes|integer|min:0',
        ]);

        $objective->update($validated);

        return response()->json($objective);
    }

    /**
     * Remove the specified objective
     */
    public function destroy(Objective $objective): JsonResponse
    {
        $this->authorize('update', $objective->unit->section->project);
        
        $objective->delete();

        return response()->json(['message' => 'Objective deleted successfully']);
    }
}