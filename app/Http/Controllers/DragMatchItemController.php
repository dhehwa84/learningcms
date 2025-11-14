<?php
// app/Http/Controllers/DragMatchItemController.php

namespace App\Http\Controllers;

use App\Models\DragMatchItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class DragMatchItemController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Update the specified drag match item
     */
    public function update(Request $request, DragMatchItem $dragMatchItem): JsonResponse
    {
        $this->authorize('update', $dragMatchItem->exercise->accordion->unit->section->project);

        $validated = $request->validate([
            'order' => 'sometimes|integer|min:0',
            'left_type' => 'sometimes|required|in:text,image',
            'left_value' => 'sometimes|required|string',
            'left_alt' => 'sometimes|nullable|string|max:255',
            'right_type' => 'sometimes|required|in:text,image',
            'right_value' => 'sometimes|required|string',
            'right_alt' => 'sometimes|nullable|string|max:255',
        ]);

        $dragMatchItem->update($validated);

        return response()->json($dragMatchItem);
    }

    /**
     * Remove the specified drag match item
     */
    public function destroy(DragMatchItem $dragMatchItem): JsonResponse
    {
        $this->authorize('update', $dragMatchItem->exercise->accordion->unit->section->project);
        
        $dragMatchItem->delete();

        return response()->json(['message' => 'Drag match item deleted successfully']);
    }
}