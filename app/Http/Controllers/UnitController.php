<?php
// app/Http/Controllers/UnitController.php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class UnitController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
    }

    public function index(Section $section): JsonResponse
    {
        $this->authorize('view', $section->project);

        $units = $section->units()->get();

        return response()->json(['data' => $units]);
    }

    public function store(Request $request, Section $section): JsonResponse
    {
        $this->authorize('update', $section->project);

        $validated = $request->validate([
            'number' => 'required|integer',
            'title' => 'required|string|max:255',
            'grade' => 'nullable|string|max:100',
            'theme' => 'nullable|string|max:255',
            'order' => 'required|integer|min:0',
            'folder_name' => 'sometimes|string|max:100|regex:/^[a-z0-9\-]+$/|unique:units,folder_name',
        ]);

        $unit = $section->units()->create($validated);

        return response()->json($unit, 201);
    }

    public function show($id): JsonResponse
    {
        $unit = Unit::with([
            'headerMedia',
            'objectives',
            'accordions.content.media',
            'accordions.exercises.questions',
            'accordions.exercises.dragMatchItems'
        ])->findOrFail($id);

        $this->authorize('view', $unit->section->project);

        // Test 1: Basic array conversion
        try {
            $unitArray = $unit->toArray();
            return response()->json($unitArray);
        } catch (\Exception $e) {
            // If that fails, try minimal data
            return response()->json([
                'id' => $unit->id,
                'title' => $unit->title,
                'section_id' => $unit->section_id,
                'objectives_count' => $unit->objectives->count(),
                'accordions_count' => $unit->accordions->count(),
            ]);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $unit = Unit::findOrFail($id);
        $this->authorize('update', $unit->section->project);

        // Clean the incoming data before validation
        $cleanedData = $this->cleanMediaUrls($request->all());
        
        // Use the original request for validation, but merge cleaned data
        $validationRequest = new Request($cleanedData);
        
        // Get validation rules and validate
        $validationRules = $this->getValidationRules();
        $validated = $validationRequest->validate($validationRules);

        DB::transaction(function () use ($unit, $validated) {
            // Update unit basic fields including folder_name
            $unit->update([
                'title' => $validated['title'],
                'grade' => $validated['grade'] ?? null,
                'theme' => $validated['theme'] ?? null,
                'order' => $validated['order'] ?? 0,
                'folder_name' => $validated['folder_name'] ?? $unit->folder_name,
                'header_config' => $validated['header_config'] ?? null,
            ]);

            // Update header media with cleaned URL
            if (isset($validated['header_media'])) {
                $unit->headerMedia()->updateOrCreate(
                    ['unit_id' => $unit->id],
                    $validated['header_media']
                );
            } else {
                $unit->headerMedia()->delete();
            }

            // Sync objectives (with order)
            $unit->objectives()->delete();
            foreach ($validated['objectives'] ?? [] as $objective) {
                $unit->objectives()->create($objective);
            }

            // Sync accordions with nested content and exercises
            $unit->accordions()->delete();
            foreach ($validated['accordions'] ?? [] as $accordionData) {
                $accordion = $unit->accordions()->create([
                    'title' => $accordionData['title'],
                    'icon_url' => $accordionData['icon_url'] ?? '/media/icons/book.svg', // Default icon
                    'order' => $accordionData['order']
                ]);

                // Sync content blocks
                foreach ($accordionData['content'] ?? [] as $contentData) {
                    $contentBlock = $accordion->content()->create([
                        'type' => $contentData['type'],
                        'order' => $contentData['order'],
                        'content' => $contentData['content'] ?? null,
                        'layout' => $contentData['layout'] ?? null,
                    ]);

                    // Handle single media if type is image/video/audio
                    if (in_array($contentData['type'], ['image', 'video', 'audio']) && isset($contentData['media'])) {
                        $contentBlock->media()->create([
                            'type' => $contentData['media']['type'],
                            'url' => $contentData['media']['url'],
                            'alt' => $contentData['media']['alt'] ?? null,
                            'caption' => $contentData['media']['caption'] ?? null,
                            'order' => 0,
                        ]);
                    }
                    
                    // Handle grid items if type is grid
                    if ($contentData['type'] === 'grid' && isset($contentData['grid_items']) && is_array($contentData['grid_items'])) {
                        foreach ($contentData['grid_items'] as $index => $item) {
                            $contentBlock->media()->create([
                                'type' => $item['type'],
                                'url' => $item['url'],
                                'alt' => $item['alt'] ?? null,
                                'caption' => $item['caption'] ?? null,
                                'order' => $index,
                            ]);
                        }
                    }
                }

                // Sync exercises with questions and drag-match items
                foreach ($accordionData['exercises'] ?? [] as $exerciseData) {
                    $exercise = $accordion->exercises()->create([
                        'type' => $exerciseData['type'],
                        'title' => $exerciseData['title'] ?? null,
                        'order' => $exerciseData['order'],
                        'question_numbering' => $exerciseData['question_numbering'] ?? '123',
                        'labels' => $exerciseData['labels'] ?? null,
                    ]);

                    // Add questions
                    foreach ($exerciseData['questions'] ?? [] as $questionData) {
                        $exercise->questions()->create($questionData);
                    }

                    // Add drag-match items
                    foreach ($exerciseData['drag_match_items'] ?? [] as $dragMatchData) {
                        $exercise->dragMatchItems()->create([
                            'order' => $dragMatchData['order'],
                            'left_type' => $dragMatchData['left_side']['type'],
                            'left_value' => $dragMatchData['left_side']['value'],
                            'left_alt' => $dragMatchData['left_side']['alt'] ?? null,
                            'right_type' => $dragMatchData['right_side']['type'],
                            'right_value' => $dragMatchData['right_side']['value'],
                            'right_alt' => $dragMatchData['right_side']['alt'] ?? null,
                        ]);
                    }
                }
            }
        });

        return response()->json([
            'message' => 'Unit updated successfully',
            'data' => $unit->fresh([
                'headerMedia',
                'objectives',
                'accordions.content.media',
                'accordions.exercises.questions',
                'accordions.exercises.dragMatchItems'
            ])
        ]);
    }


    private function cleanMediaUrls(array $data): array
    {
        array_walk_recursive($data, function (&$value, $key) {
            if (is_string($value) && str_contains($value, '/minio/')) {
                $value = str_replace('/minio/', '/', $value);
            }
        });
        
        return $data;
    }

    public function destroy(Unit $unit): JsonResponse
    {
        $this->authorize('delete', $unit->section->project);

        DB::transaction(function () use ($unit) {
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
            $unit->delete();
        });

        return response()->json(['message' => 'Unit deleted successfully']);
    }

    private function getValidationRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'grade' => 'nullable|string|max:100',
            'theme' => 'nullable|string|max:255',
            'order' => 'integer|min:0',
            'folder_name' => 'sometimes|required|string|max:100|regex:/^[a-z0-9\-]+$/',            
            // Header Config Validation
            'header_config' => 'sometimes|nullable|array',
            'header_config.main_heading' => 'sometimes|string|max:255',
            'header_config.sub_heading' => 'sometimes|string|max:500',
            'header_config.objectives_label' => 'sometimes|string|max:255',
            'header_config.objectives_description' => 'sometimes|string|max:1000',
            
            // Header Media
            'header_media.type' => 'nullable|in:audio,video,image',
            'header_media.url' => 'required_with:header_media.type|string|max:500',
            'header_media.alt' => 'nullable|string|max:255',
            'header_media.caption' => 'nullable|string',
            
            // Objectives
            'objectives' => 'array',
            'objectives.*.text' => 'required|string',
            'objectives.*.order' => 'required|integer|min:0',
            
            // Accordions
            'accordions' => 'array',
            'accordions.*.title' => 'required|string|max:255',
            'accordions.*.icon_url' => 'sometimes|nullable|string|max:500', // Custom icon support
            'accordions.*.order' => 'required|integer|min:0',
            
            // Content Blocks
            'accordions.*.content' => 'array',
            'accordions.*.content.*.type' => 'required|in:text,richtext,image,video,audio,grid',
            'accordions.*.content.*.order' => 'required|integer|min:0',
            'accordions.*.content.*.content' => 'nullable|string',
            'accordions.*.content.*.layout' => 'nullable|in:single,two-column,grid',
            
            // Grid Items
            'accordions.*.content.*.grid_items' => 'nullable|array',
            'accordions.*.content.*.grid_items.*.type' => 'required|in:image,text',
            'accordions.*.content.*.grid_items.*.url' => 'required_if:accordions.*.content.*.grid_items.*.type,image|string|max:500',
            'accordions.*.content.*.grid_items.*.alt' => 'nullable|string|max:255',
            'accordions.*.content.*.grid_items.*.caption' => 'nullable|string',
            
            // Exercises
            'accordions.*.exercises' => 'array',
            'accordions.*.exercises.*.type' => 'required|in:multiple-choice,radio,checkbox,text,number,mixed,drag-match',
            'accordions.*.exercises.*.title' => 'nullable|string|max:500',
            'accordions.*.exercises.*.order' => 'required|integer|min:0',
            'accordions.*.exercises.*.question_numbering' => 'nullable|in:123,abc,ABC',
            
            // Exercise Labels
            'accordions.*.exercises.*.labels' => 'sometimes|nullable|array',
            'accordions.*.exercises.*.labels.submit_button' => 'sometimes|string|max:100',
            'accordions.*.exercises.*.labels.clear_button' => 'sometimes|string|max:100',
            'accordions.*.exercises.*.labels.correct_message' => 'sometimes|string|max:255',
            'accordions.*.exercises.*.labels.incorrect_message' => 'sometimes|string|max:255',
            'accordions.*.exercises.*.labels.incomplete_message' => 'sometimes|string|max:255',
            'accordions.*.exercises.*.labels.drag_instruction' => 'sometimes|string|max:500',
            'accordions.*.exercises.*.labels.left_column_label' => 'sometimes|string|max:100',
            'accordions.*.exercises.*.labels.right_column_label' => 'sometimes|string|max:100',
            
            // Questions
            'accordions.*.exercises.*.questions' => 'array',
            'accordions.*.exercises.*.questions.*.question' => 'required|string',
            'accordions.*.exercises.*.questions.*.type' => 'required|in:dropdown,radio,checkbox,text,number,multiple-choice',
            'accordions.*.exercises.*.questions.*.order' => 'required|integer|min:0',
            'accordions.*.exercises.*.questions.*.options' => 'nullable|array',
            'accordions.*.exercises.*.questions.*.correct_answer' => 'nullable|string',
            'accordions.*.exercises.*.questions.*.correct_answers' => 'nullable|array',
            
            // Drag Match Items
            'accordions.*.exercises.*.drag_match_items' => 'nullable|array',
            'accordions.*.exercises.*.drag_match_items.*.order' => 'required|integer|min:0',
            'accordions.*.exercises.*.drag_match_items.*.left_side.type' => 'required|in:text,image',
            'accordions.*.exercises.*.drag_match_items.*.left_side.value' => 'required|string',
            'accordions.*.exercises.*.drag_match_items.*.left_side.alt' => 'nullable|string',
            'accordions.*.exercises.*.drag_match_items.*.right_side.type' => 'required|in:text,image',
            'accordions.*.exercises.*.drag_match_items.*.right_side.value' => 'required|string',
            'accordions.*.exercises.*.drag_match_items.*.right_side.alt' => 'nullable|string',
        ];
    }
}