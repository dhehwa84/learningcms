<?php
// app/Http/Controllers/Api/External/TrackingController.php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Models\TrackingApiKey;
use App\Models\UsageTrackingSession;
use App\Models\UsageTrackingExercise;
use App\Models\UsageTrackingEvent;
use App\Models\UsageTrackingFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TrackingController extends Controller
{
    /**
     * Validate API key
     */
    private function validateApiKey($apiKey)
    {
        $key = TrackingApiKey::where('key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$key) {
            return null;
        }

        // Update usage stats
        $key->incrementUsage();

        return $key;
    }

    /**
     * Detect device type from user agent
     */
    private function detectDeviceType(string $userAgent): string
    {
        if (preg_match('/(mobile|android|iphone|ipod)/i', $userAgent)) {
            return 'mobile';
        }
        if (preg_match('/(tablet|ipad)/i', $userAgent)) {
            return 'tablet';
        }
        return 'desktop';
    }

    /**
     * Detect platform from user agent
     */
    private function detectPlatform(string $userAgent): string
    {
        if (preg_match('/windows/i', $userAgent)) {
            return 'windows';
        }
        if (preg_match('/macintosh|mac os/i', $userAgent)) {
            return 'macos';
        }
        if (preg_match('/linux/i', $userAgent)) {
            return 'linux';
        }
        if (preg_match('/android/i', $userAgent)) {
            return 'android';
        }
        if (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            return 'ios';
        }
        return 'unknown';
    }

    /**
     * Detect browser from user agent
     */
    private function detectBrowser(string $userAgent): string
    {
        if (preg_match('/chrome/i', $userAgent)) {
            return 'chrome';
        }
        if (preg_match('/firefox/i', $userAgent)) {
            return 'firefox';
        }
        if (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            return 'safari';
        }
        if (preg_match('/edge/i', $userAgent)) {
            return 'edge';
        }
        return 'unknown';
    }

    /**
     * Get enhanced device info from request
     */
    private function getEnhancedDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent() ?? '';

        return [
            'user_agent' => $userAgent,
            'device_type' => $this->detectDeviceType($userAgent),
            'platform' => $this->detectPlatform($userAgent),
            'browser' => $this->detectBrowser($userAgent),
            'screen_resolution' => $request->header('X-Screen-Resolution'),
            'ip_address' => $request->ip(),
            'accept_language' => $request->header('Accept-Language'),
            // Location data can be added later via IP geolocation service
            'country' => null,
            'city' => null,
            'region' => null,
        ];
    }

    /**
     * Start a new tracking session
     */
    public function startSession(Request $request): JsonResponse
    {
        // Validate API key
        $apiKey = $this->validateApiKey($request->header('X-API-Key'));
        if (!$apiKey) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $validated = $request->validate([
            'device_id' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'timestamp' => 'required|date',
            'device_info' => 'required|array',
        ]);

        // Check if session already exists
        $session = UsageTrackingSession::where('device_id', $validated['device_id'])
            ->whereNull('ended_at')
            ->first();

        if ($session) {
            // End previous session
            $session->update([
                'ended_at' => now(),
                'duration_seconds' => now()->diffInSeconds($session->started_at),
            ]);
        }

        // Get enhanced device info
        $enhancedDeviceInfo = $this->getEnhancedDeviceInfo($request);
        $mergedDeviceInfo = array_merge($validated['device_info'], $enhancedDeviceInfo);

        // Create new session
        $session = UsageTrackingSession::create([
            'session_id' => 'sess_' . Str::random(32),
            'device_id' => $validated['device_id'],
            'project_id' => $validated['project_id'],
            'started_at' => $validated['timestamp'],
            'device_info' => $mergedDeviceInfo,
            'api_key_id' => $apiKey->key,
        ]);

        return response()->json([
            'success' => true,
            'session_id' => $session->session_id,
            'device_id' => $session->device_id,
        ], 201);
    }

    /**
     * Track exercise submission
     */
    public function trackExercise(Request $request): JsonResponse
    {
        $apiKey = $this->validateApiKey($request->header('X-API-Key'));
        if (!$apiKey) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $validated = $request->validate([
            'session_id' => 'required|string|exists:usage_tracking_sessions,session_id',
            'device_id' => 'required|string',
            'project_id' => 'required|exists:projects,id',
            'section_id' => 'required|exists:sections,id',
            'unit_id' => 'required|exists:units,id',
            'exercise_id' => 'required|string',
            'exercise_type' => 'required|string|in:multiple-choice,radio,checkbox,text,number,drag-match,fill-blank',
            'timestamp' => 'required|date',
            'interaction_data' => 'required|array',
            'interaction_data.started_at' => 'required|date',
            'interaction_data.completed_at' => 'nullable|date',
            'interaction_data.time_spent_seconds' => 'nullable|integer|min:0',
            'interaction_data.answer' => 'nullable',
            'interaction_data.is_correct' => 'nullable|boolean',
            'interaction_data.score' => 'nullable|numeric|between:0,100',
            'interaction_data.attempts' => 'nullable|integer|min:1',
            'device_info' => 'required|array',
        ]);

        // Get enhanced device info
        $enhancedDeviceInfo = $this->getEnhancedDeviceInfo($request);
        $mergedDeviceInfo = array_merge($validated['device_info'], $enhancedDeviceInfo);

        $exercise = UsageTrackingExercise::create([
            'session_id' => $validated['session_id'],
            'device_id' => $validated['device_id'],
            'project_id' => $validated['project_id'],
            'section_id' => $validated['section_id'],
            'unit_id' => $validated['unit_id'],
            'exercise_id' => $validated['exercise_id'],
            'exercise_type' => $validated['exercise_type'],
            'started_at' => $validated['interaction_data']['started_at'],
            'completed_at' => $validated['interaction_data']['completed_at'] ?? now(),
            'time_spent_seconds' => $validated['interaction_data']['time_spent_seconds'],
            'answer_data' => $validated['interaction_data']['answer'] ?? null,
            'is_correct' => $validated['interaction_data']['is_correct'] ?? null,
            'score' => $validated['interaction_data']['score'] ?? null,
            'attempts' => $validated['interaction_data']['attempts'] ?? 1,
            'device_info' => $mergedDeviceInfo,
            'api_key_id' => $apiKey->key,
        ]);

        return response()->json([
            'success' => true,
            'exercise_id' => $exercise->id,
            'tracking_id' => 'ex_' . $exercise->id,
        ], 201);
    }

    /**
     * Track various events (project access, section access, unit access)
     */
    public function trackEvent(Request $request): JsonResponse
    {
        $apiKey = $this->validateApiKey($request->header('X-API-Key'));
        if (!$apiKey) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $validated = $request->validate([
            'session_id' => 'required|string|exists:usage_tracking_sessions,session_id',
            'device_id' => 'required|string',
            'project_id' => 'required|exists:projects,id',
            'event_type' => 'required|string|in:project_access,section_access,unit_access,exercise_view,content_view',
            'target_id' => 'nullable|string',
            'target_name' => 'nullable|string',
            'timestamp' => 'required|date',
            'event_data' => 'nullable|array',
            'device_info' => 'required|array',
            'section_id' => 'nullable|exists:sections,id',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        // Get enhanced device info
        $enhancedDeviceInfo = $this->getEnhancedDeviceInfo($request);
        $mergedDeviceInfo = array_merge($validated['device_info'], $enhancedDeviceInfo);

        $event = UsageTrackingEvent::create([
            'session_id' => $validated['session_id'],
            'device_id' => $validated['device_id'],
            'project_id' => $validated['project_id'],
            'section_id' => $validated['section_id'] ?? null,
            'unit_id' => $validated['unit_id'] ?? null,
            'event_type' => $validated['event_type'],
            'target_id' => $validated['target_id'],
            'target_name' => $validated['target_name'],
            'timestamp' => $validated['timestamp'],
            'event_data' => $validated['event_data'] ?? [],
            'device_info' => $mergedDeviceInfo,
            'api_key_id' => $apiKey->key,
        ]);

        return response()->json([
            'success' => true,
            'event_id' => $event->id,
            'tracking_id' => 'evt_' . $event->id,
        ], 201);
    }

    /**
     * Batch track multiple events
     */
    public function trackBatch(Request $request): JsonResponse
    {
        $apiKey = $this->validateApiKey($request->header('X-API-Key'));
        if (!$apiKey) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $validated = $request->validate([
            'device_id' => 'required|string',
            'events' => 'required|array|max:50',
            'events.*.type' => 'required|string|in:session_start,session_end,exercise,event,feedback',
            'events.*.data' => 'required|array',
        ]);

        // Get enhanced device info for the batch
        $enhancedDeviceInfo = $this->getEnhancedDeviceInfo($request);

        $results = [];
        
        DB::transaction(function () use ($validated, $apiKey, $enhancedDeviceInfo, &$results) {
            foreach ($validated['events'] as $event) {
                try {
                    // Enhance each event with device info
                    if (isset($event['data']['device_info'])) {
                        $event['data']['device_info'] = array_merge($event['data']['device_info'], $enhancedDeviceInfo);
                    } else {
                        $event['data']['device_info'] = $enhancedDeviceInfo;
                    }

                    switch ($event['type']) {
                        case 'exercise':
                            $result = $this->processExerciseEvent($event['data'], $apiKey);
                            break;
                        case 'event':
                            $result = $this->processGenericEvent($event['data'], $apiKey);
                            break;
                        case 'feedback':
                            $result = $this->processFeedbackEvent($event['data'], $apiKey);
                            break;
                        default:
                            $result = ['success' => false, 'error' => 'Unknown event type'];
                    }
                    $results[] = $result;
                } catch (\Exception $e) {
                    Log::error('Batch tracking error: ' . $e->getMessage());
                    $results[] = ['success' => false, 'error' => $e->getMessage()];
                }
            }
        });

        return response()->json([
            'success' => true,
            'processed' => count($results),
            'results' => $results,
        ], 201);
    }

    /**
     * Submit feedback
     */
    public function submitFeedback(Request $request): JsonResponse
    {
        $apiKey = $this->validateApiKey($request->header('X-API-Key'));
        if (!$apiKey) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $validated = $request->validate([
            'session_id' => 'required|string|exists:usage_tracking_sessions,session_id',
            'device_id' => 'required|string',
            'project_id' => 'nullable|exists:projects,id',
            'section_id' => 'nullable|exists:sections,id',
            'unit_id' => 'nullable|exists:units,id',
            'user_type' => 'required|in:student,teacher,parent,other',
            'feedback_type' => 'required|in:improvement,bug,feature_request,general',
            'rating' => 'nullable|integer|between:1,5',
            'message' => 'required|string|min:10|max:2000',
            'contact_email' => 'nullable|email',
            'device_info' => 'required|array',
        ]);

        $feedback = UsageTrackingFeedback::create([
            'session_id' => $validated['session_id'],
            'device_id' => $validated['device_id'],
            'project_id' => $validated['project_id'],
            'section_id' => $validated['section_id'],
            'unit_id' => $validated['unit_id'],
            'user_type' => $validated['user_type'],
            'feedback_type' => $validated['feedback_type'],
            'rating' => $validated['rating'],
            'message' => $validated['message'],
            'contact_email' => $validated['contact_email'],
            'device_info' => $validated['device_info'],
            'api_key_id' => $apiKey->key,
        ]);

        return response()->json([
            'success' => true,
            'feedback_id' => $feedback->id,
            'message' => 'Thank you for your feedback!',
        ], 201);
    }

    /**
     * Process exercise event from batch
     */
    private function processExerciseEvent($data, $apiKey)
    {
        // Similar to trackExercise but without request validation
        $exercise = UsageTrackingExercise::create([
            'session_id' => $data['session_id'],
            'device_id' => $data['device_id'],
            'project_id' => $data['project_id'],
            'section_id' => $data['section_id'],
            'unit_id' => $data['unit_id'],
            'exercise_id' => $data['exercise_id'],
            'exercise_type' => $data['exercise_type'],
            'started_at' => $data['interaction_data']['started_at'],
            'completed_at' => $data['interaction_data']['completed_at'] ?? now(),
            'time_spent_seconds' => $data['interaction_data']['time_spent_seconds'],
            'answer_data' => $data['interaction_data']['answer'] ?? null,
            'is_correct' => $data['interaction_data']['is_correct'] ?? null,
            'score' => $data['interaction_data']['score'] ?? null,
            'attempts' => $data['interaction_data']['attempts'] ?? 1,
            'device_info' => $data['device_info'],
            'api_key_id' => $apiKey->key,
        ]);

        return ['success' => true, 'type' => 'exercise', 'id' => $exercise->id];
    }

    /**
     * Process generic event from batch
     */
    private function processGenericEvent($data, $apiKey)
    {
        $event = UsageTrackingEvent::create([
            'session_id' => $data['session_id'],
            'device_id' => $data['device_id'],
            'project_id' => $data['project_id'],
            'section_id' => $data['section_id'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'event_type' => $data['event_type'],
            'target_id' => $data['target_id'] ?? null,
            'target_name' => $data['target_name'] ?? null,
            'timestamp' => $data['timestamp'],
            'event_data' => $data['event_data'] ?? [],
            'device_info' => $data['device_info'],
            'api_key_id' => $apiKey->key,
        ]);

        return ['success' => true, 'type' => 'event', 'id' => $event->id];
    }

    /**
     * Process feedback event from batch
     */
    private function processFeedbackEvent($data, $apiKey)
    {
        $feedback = UsageTrackingFeedback::create([
            'session_id' => $data['session_id'],
            'device_id' => $data['device_id'],
            'project_id' => $data['project_id'] ?? null,
            'section_id' => $data['section_id'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'user_type' => $data['user_type'],
            'feedback_type' => $data['feedback_type'],
            'rating' => $data['rating'] ?? null,
            'message' => $data['message'],
            'contact_email' => $data['contact_email'] ?? null,
            'device_info' => $data['device_info'],
            'api_key_id' => $apiKey->key,
        ]);

        return ['success' => true, 'type' => 'feedback', 'id' => $feedback->id];
    }

    
}