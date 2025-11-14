<?php
// app/Http/Controllers/ContentBlockMediaController.php

namespace App\Http\Controllers;

use App\Models\ContentBlockMedia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class ContentBlockMediaController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Update the specified content block media
     */
    public function update(Request $request, ContentBlockMedia $contentBlockMedia): JsonResponse
    {
        $this->authorize('update', $contentBlockMedia->contentBlock->accordion->unit->section->project);

        $validated = $request->validate([
            'type' => 'sometimes|required|in:image,text,video,audio',
            'url' => 'sometimes|nullable|string|max:500',
            'alt' => 'sometimes|nullable|string|max:255',
            'caption' => 'sometimes|nullable|string',
            'order' => 'sometimes|integer|min:0',
        ]);

        $contentBlockMedia->update($validated);

        return response()->json([
            'message' => 'Content block media updated successfully',
            'data' => $contentBlockMedia
        ]);
    }

    /**
     * Remove the specified content block media
     */
    public function destroy(ContentBlockMedia $contentBlockMedia): JsonResponse
    {
        $this->authorize('update', $contentBlockMedia->contentBlock->accordion->unit->section->project);
        
        $contentBlockMedia->delete();

        return response()->json(['message' => 'Content block media deleted successfully']);
    }
}