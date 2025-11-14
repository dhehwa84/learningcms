<?php
// app/Http/Controllers/AccordionController.php

namespace App\Http\Controllers;

use App\Models\Accordion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class AccordionController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    /**
     * Update the specified accordion
     */
    public function update(Request $request, Accordion $accordion): JsonResponse
    {
        $this->authorize('update', $accordion->unit->section->project);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'icon_url' => 'sometimes|nullable|string|max:500',
            'order' => 'sometimes|integer|min:0',
        ]);

        $accordion->update($validated);

        return response()->json($accordion);
    }

    /**
     * Remove the specified accordion
     */
    public function destroy(Accordion $accordion): JsonResponse
    {
        $this->authorize('update', $accordion->unit->section->project);
        
        $accordion->delete();

        return response()->json(['message' => 'Accordion deleted successfully']);
    }
}