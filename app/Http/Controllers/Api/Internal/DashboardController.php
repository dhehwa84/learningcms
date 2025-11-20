<?php
// app/Http/Controllers/Api/Internal/DashboardController.php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Models\UsageTrackingSession;
use App\Models\UsageTrackingExercise;
use App\Models\UsageTrackingFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get comprehensive dashboard overview with comparison data
     */
    public function getOverview(Request $request): JsonResponse
    {
        $timeRange = $request->get('timeRange', '30days');
        $dateFilter = $this->getDateFilter($timeRange);
        $previousDateFilter = $this->getPreviousDateFilter($timeRange);

        // Current period metrics
        $current = $this->calculateMetrics($dateFilter, now());
        $previous = $this->calculateMetrics($previousDateFilter, $dateFilter);

        return response()->json([
            'summary_metrics' => [
                'total_usage' => [
                    'value' => $current['total_sessions'],
                    'change_percentage' => $this->calculateChange($current['total_sessions'], $previous['total_sessions']),
                    'comparison_period' => 'last_period'
                ],
                'active_users' => [
                    'value' => $current['unique_devices'],
                    'change_percentage' => $this->calculateChange($current['unique_devices'], $previous['unique_devices']),
                    'comparison_period' => 'last_period'
                ],
                'avg_session_time' => [
                    'value' => round($current['avg_session_duration'] / 60, 1),
                    'change_minutes' => round(($current['avg_session_duration'] - $previous['avg_session_duration']) / 60, 1),
                    'comparison_period' => 'last_period'
                ],
                'completion_rate' => [
                    'value' => $current['completion_rate'],
                    'change_percentage' => $this->calculateChange($current['completion_rate'], $previous['completion_rate']),
                    'comparison_period' => 'last_period'
                ]
            ]
        ]);
    }

    private function calculateMetrics($startDate, $endDate): array
    {
        $sessions = UsageTrackingSession::whereBetween('started_at', [$startDate, $endDate]);
        
        $totalSessions = $sessions->count();
        $uniqueDevices = $sessions->distinct('device_id')->count('device_id');
        $avgSessionDuration = $sessions->whereNotNull('duration_seconds')->avg('duration_seconds') ?? 0;

        // Completion rate
        $exercises = UsageTrackingExercise::whereBetween('started_at', [$startDate, $endDate]);
        $totalExercises = $exercises->count();
        $completedExercises = $exercises->whereNotNull('completed_at')->count();
        $completionRate = $totalExercises > 0 ? round(($completedExercises / $totalExercises) * 100) : 0;

        return [
            'total_sessions' => $totalSessions,
            'unique_devices' => $uniqueDevices,
            'avg_session_duration' => $avgSessionDuration,
            'completion_rate' => $completionRate
        ];
    }

    private function calculateChange($current, $previous): float
    {
        if ($previous === 0) return 0;
        return round((($current - $previous) / $previous) * 100);
    }

    private function getDateFilter(string $timeRange): Carbon
    {
        return match ($timeRange) {
            '7days' => Carbon::now()->subDays(7),
            '30days' => Carbon::now()->subDays(30),
            '6months' => Carbon::now()->subMonths(6),
            '1year' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30),
        };
    }

    private function getPreviousDateFilter(string $timeRange): Carbon
    {
        $currentStart = $this->getDateFilter($timeRange);
        $periodDays = now()->diffInDays($currentStart);
        return $currentStart->copy()->subDays($periodDays);
    }
}