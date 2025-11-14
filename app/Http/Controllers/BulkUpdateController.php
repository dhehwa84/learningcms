<?php
// app/Http/Controllers/BulkUpdateController.php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class BulkUpdateController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Bulk update unit structure
     */
    public function updateUnitStructure(Request $request, $unitId): JsonResponse
    {
        $unit = Unit::findOrFail($unitId);
        $this->authorize('update', $unit->section->project);

        $validated = $request->validate([
            'objectives' => 'sometimes|array',
            'objectives.*.id' => 'required|exists:objectives,id',
            'objectives.*.text' => 'required|string',
            'objectives.*.order' => 'required|integer|min:0',
            
            'accordions' => 'sometimes|array',
            'accordions.*.id' => 'required|exists:accordions,id',
            'accordions.*.title' => 'required|string|max:255',
            'accordions.*.order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            // Update objectives
            foreach ($validated['objectives'] ?? [] as $objectiveData) {
                \App\Models\Objective::where('id', $objectiveData['id'])
                    ->update([
                        'text' => $objectiveData['text'],
                        'order' => $objectiveData['order']
                    ]);
            }

            // Update accordions
            foreach ($validated['accordions'] ?? [] as $accordionData) {
                \App\Models\Accordion::where('id', $accordionData['id'])
                    ->update([
                        'title' => $accordionData['title'],
                        'order' => $accordionData['order']
                    ]);
            }
        });

        return response()->json([
            'message' => 'Unit structure updated successfully',
            'data' => $unit->fresh(['objectives', 'accordions'])
        ]);
    }
}