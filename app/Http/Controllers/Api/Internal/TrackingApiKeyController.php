<?php
// app/Http/Controllers/Api/Internal/TrackingApiKeyController.php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Models\TrackingApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrackingApiKeyController extends Controller
{
    /**
     * Get all API keys
     */
    public function index(Request $request): JsonResponse
    {
        $keys = TrackingApiKey::orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $keys->map(function ($key) {
                return [
                    'id' => $key->id,
                    'name' => $key->name,
                    'key' => $key->key,
                    'description' => $key->description,
                    'is_active' => $key->is_active,
                    'rate_limit' => $key->rate_limit,
                    'last_used_at' => $key->last_used_at?->toISOString(),
                    'usage_count' => $key->usage_count,
                    'created_at' => $key->created_at->toISOString(),
                ];
            })
        ]);
    }

    /**
     * Create a new API key
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rate_limit' => 'sometimes|integer|min:1|max:10000',
            'allowed_projects' => 'sometimes|array',
            'allowed_projects.*' => 'exists:projects,id',
        ]);

        $apiKey = TrackingApiKey::create([
            'key' => 'track_' . Str::random(32),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'rate_limit' => $validated['rate_limit'] ?? 100,
            'allowed_projects' => $validated['allowed_projects'] ?? null,
        ]);

        return response()->json([
            'message' => 'API key created successfully',
            'data' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'key' => $apiKey->key, // Only returned once!
                'description' => $apiKey->description,
                'rate_limit' => $apiKey->rate_limit,
                'created_at' => $apiKey->created_at->toISOString(),
            ]
        ], 201);
    }

    /**
     * Update an API key
     */
    public function update(Request $request, TrackingApiKey $trackingApiKey): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'rate_limit' => 'sometimes|integer|min:1|max:10000',
            'allowed_projects' => 'sometimes|array',
            'allowed_projects.*' => 'exists:projects,id',
        ]);

        $trackingApiKey->update($validated);

        return response()->json([
            'message' => 'API key updated successfully',
            'data' => [
                'id' => $trackingApiKey->id,
                'name' => $trackingApiKey->name,
                'description' => $trackingApiKey->description,
                'is_active' => $trackingApiKey->is_active,
                'rate_limit' => $trackingApiKey->rate_limit,
                'last_used_at' => $trackingApiKey->last_used_at?->toISOString(),
                'usage_count' => $trackingApiKey->usage_count,
                'updated_at' => $trackingApiKey->updated_at->toISOString(),
            ]
        ]);
    }

    /**
     * Delete an API key
     */
    public function destroy(TrackingApiKey $trackingApiKey): JsonResponse
    {
        $trackingApiKey->delete();

        return response()->json([
            'message' => 'API key deleted successfully'
        ]);
    }

    /**
     * Regenerate API key
     */
    public function regenerate(TrackingApiKey $trackingApiKey): JsonResponse
    {
        $newKey = 'track_' . Str::random(32);
        
        $trackingApiKey->update([
            'key' => $newKey,
            'last_used_at' => null,
            'usage_count' => 0,
        ]);

        return response()->json([
            'message' => 'API key regenerated successfully',
            'data' => [
                'id' => $trackingApiKey->id,
                'name' => $trackingApiKey->name,
                'key' => $newKey, // Only returned once!
                'regenerated_at' => now()->toISOString(),
            ]
        ]);
    }
}