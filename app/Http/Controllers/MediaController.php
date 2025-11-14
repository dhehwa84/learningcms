<?php
// app/Http/Controllers/MediaController.php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\DefaultImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Media::where('user_id', auth()->id());

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $query->where('filename', 'like', '%' . $request->search . '%');
        }

        $media = $query->paginate(20);

        // Transform URLs to be publicly accessible
        $transformedItems = collect($media->items())->map(function ($mediaItem) {
            return $this->transformMediaUrls($mediaItem);
        });

        return response()->json([
            'data' => $transformedItems,
            'meta' => [
                'current_page' => $media->currentPage(),
                'total' => $media->total(),
                'per_page' => $media->perPage(),
                'last_page' => $media->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:15360', // 15MB max
            'type' => 'required|in:image,audio,video',
            'alt' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension() ?: $this->getExtensionFromMime($file->getMimeType());
        $mimeType = $file->getMimeType();
        
        // Generate unique filename
        $filename = Str::uuid() . '.' . $extension;
        $path = "media/{$filename}";

        try {
            // Upload to MinIO using file contents
            $fileContents = file_get_contents($file->getRealPath());
            
            $success = Storage::disk('minio')->put($path, $fileContents, [
                'visibility' => 'public',
            ]);

            if (!$success) {
                throw new \Exception('Failed to store file in MinIO');
            }

            // Generate public URL - Use external MinIO port or ngrok URL
            $publicUrl = $this->generatePublicUrl($path);

            $mediaData = [
                'user_id' => auth()->id(),
                'filename' => $originalName,
                'type' => $request->type,
                'url' => $publicUrl,
                'file_path' => $path,
                'alt' => $request->alt,
                'size' => $file->getSize(),
                'mime_type' => $mimeType,
            ];

            // Get image dimensions if it's an image
            if ($request->type === 'image') {
                $imageInfo = getimagesize($file->getRealPath());
                if ($imageInfo) {
                    $mediaData['width'] = $imageInfo[0];
                    $mediaData['height'] = $imageInfo[1];
                }
                
                $mediaData['thumbnail'] = $publicUrl;
            }

            $media = Media::create($mediaData);

            return response()->json($this->transformMediaUrls($media), 201);

        } catch (\Exception $e) {
            \Log::error('File upload failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'File upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate public URL for MinIO files
     */
    private function generatePublicUrl(string $path): string
    {
        $baseUrl = rtrim(env('MINIO_NGROK_URL', 'http://localhost:19000'), '/');
        return "{$baseUrl}/minio/uploads/{$path}";
    }
    /**
     * Transform media URLs to be publicly accessible
     */
    private function transformMediaUrls(Media $media): array
    {
        $mediaArray = $media->toArray();
        
        // Transform URLs to public URLs
        $mediaArray['url'] = $this->generatePublicUrl($media->file_path);
        $mediaArray['thumbnail'] = $this->generatePublicUrl($media->file_path);
        
        return $mediaArray;
    }

    public function destroy(Media $media): JsonResponse
    {
        // Manual authorization
        if ($media->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        try {
            // Delete from MinIO using stored file_path
            if ($media->file_path) {
                Storage::disk('minio')->delete($media->file_path);
            }

            $media->delete();

            return response()->json(['message' => 'Media deleted successfully']);

        } catch (\Exception $e) {
            \Log::error('File deletion failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'File deletion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function defaultImages(): JsonResponse
    {
        $icons = DefaultImage::where('category', 'icon')->get();
        $illustrations = DefaultImage::where('category', 'illustration')->get();

        return response()->json([
            'icons' => $icons,
            'illustrations' => $illustrations,
        ]);
    }

    /**
     * Get file extension from mime type
     */
    private function getExtensionFromMime(?string $mime): string
    {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'video/x-msvideo' => 'avi',
        ];

        return $mimeMap[$mime] ?? 'bin';
    }
}