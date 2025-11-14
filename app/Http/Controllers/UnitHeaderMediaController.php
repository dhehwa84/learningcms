<?php
// app/Http/Controllers/UnitHeaderMediaController.php

namespace App\Http\Controllers;

use App\Models\UnitHeaderMedia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class UnitHeaderMediaController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Update the specified unit header media
     */
    public function update(Request $request, UnitHeaderMedia $unitHeaderMedia): JsonResponse
    {
        $this->authorize('update', $unitHeaderMedia->unit->section->project);

        $validated = $request->validate([
            'type' => 'sometimes|required|in:audio,video,image',
            'url' => 'sometimes|required|string|max:500',
            'alt' => 'sometimes|nullable|string|max:255',
            'caption' => 'sometimes|nullable|string',
        ]);

        $unitHeaderMedia->update($validated);

        return response()->json([
            'message' => 'Unit header media updated successfully',
            'data' => $unitHeaderMedia
        ]);
    }

    /**
     * Remove the specified unit header media
     */
    public function destroy(UnitHeaderMedia $unitHeaderMedia): JsonResponse
    {
        $this->authorize('update', $unitHeaderMedia->unit->section->project);
        
        $unitHeaderMedia->delete();

        return response()->json(['message' => 'Unit header media deleted successfully']);
    }
}