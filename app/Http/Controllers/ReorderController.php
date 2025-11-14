<?php
// app/Http/Controllers/ReorderController.php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Accordion;
use App\Models\Exercise;
use App\Models\Objective;
use App\Models\Question;
use App\Models\ContentBlock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class ReorderController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    /**
     * Reorder objectives within a unit
     */
    public function reorderObjectives(Request $request, $unitId): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:objectives,id',
            'order.*.order' => 'required|integer|min:0',
        ]);

        $unit = Unit::findOrFail($unitId);
        $this->authorize('update', $unit->section->project);

        DB::transaction(function () use ($request) {
            foreach ($request->order as $item) {
                Objective::where('id', $item['id'])->update(['order' => $item['order']]);
            }
        });

        return response()->json(['message' => 'Objectives reordered successfully']);
    }

    /**
     * Reorder accordions within a unit
     */
    public function reorderAccordions(Request $request, $unitId): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:accordions,id',
            'order.*.order' => 'required|integer|min:0',
        ]);

        $unit = Unit::findOrFail($unitId);
        $this->authorize('update', $unit->section->project);

        DB::transaction(function () use ($request) {
            foreach ($request->order as $item) {
                Accordion::where('id', $item['id'])->update(['order' => $item['order']]);
            }
        });

        return response()->json(['message' => 'Accordions reordered successfully']);
    }

    /**
     * Reorder content blocks within an accordion
     */
    public function reorderContentBlocks(Request $request, $accordionId): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:content_blocks,id',
            'order.*.order' => 'required|integer|min:0',
        ]);

        $accordion = Accordion::findOrFail($accordionId);
        $this->authorize('update', $accordion->unit->section->project);

        DB::transaction(function () use ($request) {
            foreach ($request->order as $item) {
                ContentBlock::where('id', $item['id'])->update(['order' => $item['order']]);
            }
        });

        return response()->json(['message' => 'Content blocks reordered successfully']);
    }

    /**
     * Reorder exercises within an accordion
     */
    public function reorderExercises(Request $request, $accordionId): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:exercises,id',
            'order.*.order' => 'required|integer|min:0',
        ]);

        $accordion = Accordion::findOrFail($accordionId);
        $this->authorize('update', $accordion->unit->section->project);

        DB::transaction(function () use ($request) {
            foreach ($request->order as $item) {
                Exercise::where('id', $item['id'])->update(['order' => $item['order']]);
            }
        });

        return response()->json(['message' => 'Exercises reordered successfully']);
    }

    /**
     * Reorder questions within an exercise
     */
    public function reorderQuestions(Request $request, $exerciseId): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:questions,id',
            'order.*.order' => 'required|integer|min:0',
        ]);

        $exercise = Exercise::findOrFail($exerciseId);
        $this->authorize('update', $exercise->accordion->unit->section->project);

        DB::transaction(function () use ($request) {
            foreach ($request->order as $item) {
                Question::where('id', $item['id'])->update(['order' => $item['order']]);
            }
        });

        return response()->json(['message' => 'Questions reordered successfully']);
    }
}