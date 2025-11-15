<?php
// app/Http/Controllers/ContentBlockController.php

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use App\Models\ContentBlockMedia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class ContentBlockController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Update the specified content block
     */
    public function update(Request $request, ContentBlock $contentBlock): JsonResponse
    {
        $this->authorize('update', $contentBlock->accordion->unit->section->project);

        // Clean media URLs before validation
        $cleanedData = $this->cleanMediaUrls($request->all());
        $cleanedRequest = new Request($cleanedData);
        
        $validated = $cleanedRequest->validate([
            'type' => 'sometimes|required|in:text,richtext,image,video,audio,grid',
            'content' => 'sometimes|nullable|string',
            'layout' => 'sometimes|nullable|in:single,two-column,grid',
            'order' => 'sometimes|integer|min:0',
        ]);

        DB::transaction(function () use ($contentBlock, $validated, $cleanedRequest) {
            $contentBlock->update($validated);

            // Handle single media if type is image/video/audio
            if (in_array($contentBlock->type, ['image', 'video', 'audio']) && $cleanedRequest->has('media')) {
                $mediaData = $cleanedRequest->validate([
                    'media.type' => 'required|in:image,video,audio',
                    'media.url' => 'required|string|max:500',
                    'media.alt' => 'nullable|string|max:255',
                    'media.caption' => 'nullable|string',
                    'media.order' => 'sometimes|integer|min:0',
                ])['media'];

                // Delete existing media and create new one
                $contentBlock->media()->delete();
                $contentBlock->media()->create($mediaData);
            }

            // Handle grid items if type is grid
            if ($contentBlock->type === 'grid' && $request->has('grid_items')) {
                $gridItems = $request->validate([
                    'grid_items' => 'required|array',
                    'grid_items.*.type' => 'required|in:image,text',
                    'grid_items.*.url' => 'required_if:grid_items.*.type,image|string|max:500',
                    'grid_items.*.value' => 'required_if:grid_items.*.type,text|string',
                    'grid_items.*.alt' => 'nullable|string|max:255',
                    'grid_items.*.caption' => 'nullable|string',
                    'grid_items.*.order' => 'required|integer|min:0',
                ])['grid_items'];

                // Delete existing grid items and create new ones
                $contentBlock->media()->delete();
                foreach ($gridItems as $item) {
                    $contentBlock->media()->create([
                        'type' => $item['type'],
                        'url' => $item['type'] === 'image' ? $item['url'] : null,
                        'alt' => $item['alt'] ?? null,
                        'caption' => $item['caption'] ?? null,
                        'order' => $item['order'],
                    ]);
                }
            }

            // Clear media if type is text/richtext
            if (in_array($contentBlock->type, ['text', 'richtext'])) {
                $contentBlock->media()->delete();
            }
        });

        return response()->json([
            'message' => 'Content block updated successfully',
            'data' => $contentBlock->fresh('media')
        ]);
    }

    /**
     * Clean media URLs by removing /minio/ from them
     */
    private function cleanMediaUrls(array $data): array
    {
        array_walk_recursive($data, function (&$value, $key) {
            if (is_string($value) && str_contains($value, '/minio/')) {
                $value = str_replace('/minio/', '/', $value);
            }
        });
        
        return $data;
    }

    /**
     * Remove the specified content block
     */
    public function destroy(ContentBlock $contentBlock): JsonResponse
    {
        $this->authorize('update', $contentBlock->accordion->unit->section->project);
        
        DB::transaction(function () use ($contentBlock) {
            $contentBlock->media()->delete();
            $contentBlock->delete();
        });

        return response()->json(['message' => 'Content block deleted successfully']);
    }
}