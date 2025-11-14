<?php
// app/Http/Controllers/LabelController.php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LabelController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
    }

    public function index(): JsonResponse
    {
        $labels = Label::where('user_id', auth()->id())->get();
        
        $defaultLabels = [
            'default_exercise_labels' => [
                'submit_button' => 'Timphendvulo letiphakanyisiwe',
                'clear_button' => 'Cisha',
                'correct_message' => 'Kulungile!',
                'incorrect_message' => 'Lutsa kutsi',
                'incomplete_message' => 'Sicela uphendvule',
            ],
            'ui_labels' => [
                'objectives_title' => 'Ekupheleni kwalomsebenti utawuzuza loku',
                'media_player_title' => 'Cindzetela inkinobho ulalele',
                'exercise_title' => 'Nakufundvwa',
            ]
        ];

        // Merge with user's custom labels
        foreach ($labels as $label) {
            if (str_contains($label->key, 'default_exercise_labels.')) {
                $key = str_replace('default_exercise_labels.', '', $label->key);
                $defaultLabels['default_exercise_labels'][$key] = $label->value;
            } elseif (str_contains($label->key, 'ui_labels.')) {
                $key = str_replace('ui_labels.', '', $label->key);
                $defaultLabels['ui_labels'][$key] = $label->value;
            }
        }

        return response()->json($defaultLabels);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'default_exercise_labels' => 'sometimes|array',
            'default_exercise_labels.submit_button' => 'sometimes|string|max:100',
            'default_exercise_labels.clear_button' => 'sometimes|string|max:100',
            'default_exercise_labels.correct_message' => 'sometimes|string|max:255',
            'default_exercise_labels.incorrect_message' => 'sometimes|string|max:255',
            'default_exercise_labels.incomplete_message' => 'sometimes|string|max:255',
            'ui_labels' => 'sometimes|array',
        ]);

        $userId = auth()->id();

        // Update default exercise labels
        if (isset($validated['default_exercise_labels'])) {
            foreach ($validated['default_exercise_labels'] as $key => $value) {
                Label::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'key' => 'default_exercise_labels.' . $key,
                    ],
                    ['value' => $value]
                );
            }
        }

        // Update UI labels
        if (isset($validated['ui_labels'])) {
            foreach ($validated['ui_labels'] as $key => $value) {
                Label::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'key' => 'ui_labels.' . $key,
                    ],
                    ['value' => $value]
                );
            }
        }

        return response()->json(['message' => 'Labels updated successfully']);
    }
}