<?php
// app/Http/Controllers/QuestionController.php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class QuestionController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    /**
     * Update the specified question
     */
    public function update(Request $request, Question $question): JsonResponse
    {
        $this->authorize('update', $question->exercise->accordion->unit->section->project);

        $validated = $request->validate([
            'question' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:dropdown,radio,checkbox,text,number,multiple-choice',
            'order' => 'sometimes|integer|min:0',
            'options' => 'sometimes|nullable|array',
            'correct_answer' => 'sometimes|nullable|string',
            'correct_answers' => 'sometimes|nullable|array',
        ]);

        $question->update($validated);

        return response()->json($question);
    }

    /**
     * Remove the specified question
     */
    public function destroy(Question $question): JsonResponse
    {
        $this->authorize('update', $question->exercise->accordion->unit->section->project);
        
        $question->delete();

        return response()->json(['message' => 'Question deleted successfully']);
    }
}