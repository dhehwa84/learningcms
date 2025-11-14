<?php
// app/Http/Controllers/ExerciseController.php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class ExerciseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    /**
     * Update the specified exercise
     */
    public function update(Request $request, Exercise $exercise): JsonResponse
    {
        $this->authorize('update', $exercise->accordion->unit->section->project);

        $validated = $request->validate([
            'title' => 'sometimes|nullable|string|max:500',
            'type' => 'sometimes|required|in:multiple-choice,radio,checkbox,text,number,mixed,drag-match',
            'order' => 'sometimes|integer|min:0',
            'question_numbering' => 'sometimes|nullable|in:123,abc,ABC',
            'labels' => 'sometimes|nullable|array',
            'labels.submit_button' => 'sometimes|string|max:100',
            'labels.clear_button' => 'sometimes|string|max:100',
            'labels.correct_message' => 'sometimes|string|max:255',
            'labels.incorrect_message' => 'sometimes|string|max:255',
            'labels.incomplete_message' => 'sometimes|string|max:255',
        ]);

        $exercise->update($validated);

        return response()->json($exercise);
    }

    /**
     * Remove the specified exercise
     */
    public function destroy(Exercise $exercise): JsonResponse
    {
        $this->authorize('update', $exercise->accordion->unit->section->project);
        
        $exercise->delete();

        return response()->json(['message' => 'Exercise deleted successfully']);
    }
}