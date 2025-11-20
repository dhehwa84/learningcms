<?php
// app/Http/Controllers/Api/Internal/AnalyticsController.php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Models\UsageTrackingSession;
use App\Models\UsageTrackingExercise;
use App\Models\UsageTrackingEvent;
use App\Models\UsageTrackingFeedback;
use App\Models\Project;
use App\Models\TrackingApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AnalyticsController extends Controller
{
    /**
     * Get dashboard summary - UPDATED to use device_type
     */
    public function getDashboardSummary(Request $request): JsonResponse
    {
        $timeRange = $request->get('timeRange', '30days');
        $dateFilter = $this->getDateFilter($timeRange);

        $cacheKey = "analytics:dashboard:{$timeRange}";
        $data = Cache::remember($cacheKey, 300, function () use ($dateFilter) {
            // Total unique devices
            $uniqueDevices = UsageTrackingSession::where('started_at', '>=', $dateFilter)
                ->distinct('device_id')
                ->count('device_id');

            // Total sessions
            $totalSessions = UsageTrackingSession::where('started_at', '>=', $dateFilter)->count();

            // Active projects (with tracking data)
            $activeProjects = UsageTrackingSession::where('started_at', '>=', $dateFilter)
                ->distinct('project_id')
                ->count('project_id');

            // Total exercises attempted
            $exercisesAttempted = UsageTrackingExercise::where('started_at', '>=', $dateFilter)->count();

            // Average session duration
            $avgSessionDuration = UsageTrackingSession::where('started_at', '>=', $dateFilter)
                ->whereNotNull('duration_seconds')
                ->avg('duration_seconds');

            // Exercise success rate
            $successRate = UsageTrackingExercise::where('started_at', '>=', $dateFilter)
                ->whereNotNull('is_correct')
                ->selectRaw('AVG(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) * 100 as success_rate')
                ->value('success_rate');

            // Device breakdown - UPDATED to use device_type from device_info
            $deviceBreakdown = UsageTrackingSession::where('started_at', '>=', $dateFilter)
                ->get()
                ->reduce(function ($carry, $session) {
                    $deviceType = $session->device_info['device_type'] ?? 'desktop';
                    $carry[$deviceType] = ($carry[$deviceType] ?? 0) + 1;
                    $carry['total'] = ($carry['total'] ?? 0) + 1;
                    return $carry;
                }, []);

            return [
                'summary' => [
                    'uniqueDevices' => $uniqueDevices,
                    'totalSessions' => $totalSessions,
                    'activeProjects' => $activeProjects,
                    'exercisesAttempted' => $exercisesAttempted,
                    'avgSessionDuration' => round($avgSessionDuration / 60, 1) . ' minutes',
                    'successRate' => round($successRate, 1) . '%',
                ],
                'deviceBreakdown' => [
                    'mobile' => $deviceBreakdown['mobile'] ?? 0,
                    'tablet' => $deviceBreakdown['tablet'] ?? 0,
                    'desktop' => $deviceBreakdown['desktop'] ?? 0,
                    'total' => $deviceBreakdown['total'] ?? 0,
                ]
            ];
        });

        return response()->json($data);
    }

    /**
     * Get device analytics - UPDATED to use device_type
     */
    public function getDeviceAnalytics(Request $request): JsonResponse
    {
        $timeRange = $request->get('timeRange', '30days');
        $projectId = $request->get('projectId');
        
        $cacheKey = "analytics:devices:{$timeRange}:{$projectId}";
        $data = Cache::remember($cacheKey, 600, function () use ($timeRange, $projectId) {
            $dateFilter = $this->getDateFilter($timeRange);

            $query = UsageTrackingSession::where('started_at', '>=', $dateFilter);
            
            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            $sessions = $query->get();

            // Device type analysis - UPDATED
            $deviceTypes = $sessions->reduce(function ($carry, $session) {
                $deviceType = $session->device_info['device_type'] ?? 'desktop';
                $carry[$deviceType . '_sessions'] = ($carry[$deviceType . '_sessions'] ?? 0) + 1;
                $carry['total_sessions'] = ($carry['total_sessions'] ?? 0) + 1;
                return $carry;
            }, []);

            // Performance by device type - UPDATED
            $performanceData = UsageTrackingExercise::where('usage_tracking_exercises.started_at', '>=', $dateFilter)
                ->when($projectId, function ($q) use ($projectId) {
                    $q->where('usage_tracking_exercises.project_id', $projectId);
                })
                ->get()
                ->reduce(function ($carry, $exercise) {
                    $session = $exercise->session;
                    if ($session) {
                        $deviceType = $session->device_info['device_type'] ?? 'desktop';
                        $carry[$deviceType . '_attempts'] = ($carry[$deviceType . '_attempts'] ?? 0) + 1;
                        $carry['total_attempts'] = ($carry['total_attempts'] ?? 0) + 1;
                        
                        // Aggregate scores
                        if ($exercise->score !== null) {
                            $carry['scores'][] = $exercise->score;
                        }
                        if ($exercise->is_correct !== null) {
                            $carry['correct_attempts'] = ($carry['correct_attempts'] ?? 0) + ($exercise->is_correct ? 1 : 0);
                        }
                        if ($exercise->time_spent_seconds !== null) {
                            $carry['time_spent'][] = $exercise->time_spent_seconds;
                        }
                    }
                    return $carry;
                }, []);

            // Calculate averages
            $avgScore = !empty($performanceData['scores']) ? array_sum($performanceData['scores']) / count($performanceData['scores']) : 0;
            $successRate = !empty($performanceData['total_attempts']) ? ($performanceData['correct_attempts'] / $performanceData['total_attempts']) * 100 : 0;
            $avgTimeSpent = !empty($performanceData['time_spent']) ? array_sum($performanceData['time_spent']) / count($performanceData['time_spent']) : 0;

            return [
                'deviceTypes' => [
                    'mobile' => [
                        'sessions' => $deviceTypes['mobile_sessions'] ?? 0,
                        'percentage' => $deviceTypes['total_sessions'] ? round(($deviceTypes['mobile_sessions'] ?? 0) / $deviceTypes['total_sessions'] * 100, 1) : 0,
                        'attempts' => $performanceData['mobile_attempts'] ?? 0,
                    ],
                    'tablet' => [
                        'sessions' => $deviceTypes['tablet_sessions'] ?? 0,
                        'percentage' => $deviceTypes['total_sessions'] ? round(($deviceTypes['tablet_sessions'] ?? 0) / $deviceTypes['total_sessions'] * 100, 1) : 0,
                        'attempts' => $performanceData['tablet_attempts'] ?? 0,
                    ],
                    'desktop' => [
                        'sessions' => $deviceTypes['desktop_sessions'] ?? 0,
                        'percentage' => $deviceTypes['total_sessions'] ? round(($deviceTypes['desktop_sessions'] ?? 0) / $deviceTypes['total_sessions'] * 100, 1) : 0,
                        'attempts' => $performanceData['desktop_attempts'] ?? 0,
                    ],
                ],
                'performance' => [
                    'avgScore' => round($avgScore, 1),
                    'successRate' => round($successRate, 1),
                    'avgTimeSpent' => round($avgTimeSpent / 60, 1) . ' min',
                ]
            ];
        });

        return response()->json($data);
    }

    /**
     * Get project analytics
     */
    public function getProjectAnalytics(Request $request): JsonResponse
    {
        $timeRange = $request->get('timeRange', '30days');
        
        $cacheKey = "analytics:projects:{$timeRange}";
        $data = Cache::remember($cacheKey, 600, function () use ($timeRange) {
            $dateFilter = $this->getDateFilter($timeRange);

            $projects = Project::withCount(['sections', 'units'])
                ->with(['status'])
                ->get();

            $projectAnalytics = [];
            
            foreach ($projects as $project) {
                $sessions = UsageTrackingSession::where('project_id', $project->id)
                    ->where('started_at', '>=', $dateFilter);

                $exercises = UsageTrackingExercise::where('project_id', $project->id)
                    ->where('started_at', '>=', $dateFilter);

                $uniqueDevices = $sessions->clone()->distinct('device_id')->count('device_id');
                $totalSessions = $sessions->clone()->count();
                $exercisesAttempted = $exercises->clone()->count();
                
                $avgScore = $exercises->clone()->whereNotNull('score')->avg('score');
                $successRate = $exercises->clone()->whereNotNull('is_correct')
                    ->selectRaw('AVG(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) * 100 as success_rate')
                    ->value('success_rate');

                $avgSessionDuration = $sessions->clone()->whereNotNull('duration_seconds')
                    ->avg('duration_seconds');

                $projectAnalytics[] = [
                    'projectId' => $project->id,
                    'projectName' => $project->name,
                    'language' => $project->language,
                    'status' => $project->status->name ?? 'Unknown',
                    'sectionsCount' => $project->sections_count,
                    'unitsCount' => $project->units_count,
                    'usage' => [
                        'uniqueDevices' => $uniqueDevices,
                        'totalSessions' => $totalSessions,
                        'exercisesAttempted' => $exercisesAttempted,
                        'avgSessionDuration' => round($avgSessionDuration / 60, 1) . ' min',
                        'avgScore' => round($avgScore ?? 0, 1),
                        'successRate' => round($successRate ?? 0, 1) . '%',
                    ]
                ];
            }

            // Sort by usage
            usort($projectAnalytics, function ($a, $b) {
                return $b['usage']['totalSessions'] <=> $a['usage']['totalSessions'];
            });

            return [
                'projects' => $projectAnalytics,
                'summary' => [
                    'totalProjects' => count($projects),
                    'totalTrackedProjects' => count(array_filter($projectAnalytics, fn($p) => $p['usage']['totalSessions'] > 0)),
                    'mostUsedProject' => $projectAnalytics[0] ?? null,
                ]
            ];
        });

        return response()->json($data);
    }

    /**
     * Get exercise performance analytics - FIXED GROUP BY
     */
    public function getExerciseAnalytics(Request $request): JsonResponse
    {
        $timeRange = $request->get('timeRange', '30days');
        $projectId = $request->get('projectId');
        $exerciseType = $request->get('exerciseType');

        $cacheKey = "analytics:exercises:{$timeRange}:{$projectId}:{$exerciseType}";
        $data = Cache::remember($cacheKey, 600, function () use ($timeRange, $projectId, $exerciseType) {
            $dateFilter = $this->getDateFilter($timeRange);

            $query = UsageTrackingExercise::with(['project', 'section', 'unit'])
                ->where('started_at', '>=', $dateFilter);

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            if ($exerciseType) {
                $query->where('exercise_type', $exerciseType);
            }

            // Overall statistics
            $overall = $query->clone()
                ->selectRaw("
                    COUNT(*) as total_attempts,
                    COUNT(DISTINCT device_id) as unique_devices,
                    AVG(score) as avg_score,
                    AVG(time_spent_seconds) as avg_time_spent,
                    AVG(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) * 100 as success_rate,
                    AVG(attempts) as avg_attempts
                ")
                ->first();

            // By exercise type - FIXED: Include exercise_type in GROUP BY
            $byType = $query->clone()
                ->groupBy('exercise_type')
                ->selectRaw("
                    exercise_type,
                    COUNT(*) as attempts,
                    AVG(score) as avg_score,
                    AVG(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) * 100 as success_rate,
                    AVG(time_spent_seconds) as avg_time_spent
                ")
                ->get();

            // Most challenging exercises (lowest success rate) - FIXED: Include all selected columns in GROUP BY
            $challenging = $query->clone()
                ->groupBy('exercise_id', 'exercise_type') // Added exercise_type to GROUP BY
                ->having('attempts', '>=', 10)
                ->selectRaw("
                    exercise_id,
                    exercise_type,
                    COUNT(*) as attempts,
                    AVG(score) as avg_score,
                    AVG(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) * 100 as success_rate
                ")
                ->orderBy('success_rate')
                ->limit(10)
                ->get();

            // Time analysis
            $timeAnalysis = $query->clone()
                ->selectRaw("
                    AVG(CASE WHEN is_correct = 1 THEN time_spent_seconds ELSE NULL END) as avg_time_correct,
                    AVG(CASE WHEN is_correct = 0 THEN time_spent_seconds ELSE NULL END) as avg_time_incorrect
                ")
                ->first();

            return [
                'overall' => [
                    'totalAttempts' => $overall->total_attempts ?? 0,
                    'uniqueDevices' => $overall->unique_devices ?? 0,
                    'avgScore' => round($overall->avg_score ?? 0, 1),
                    'avgTimeSpent' => round(($overall->avg_time_spent ?? 0) / 60, 1) . ' min',
                    'successRate' => round($overall->success_rate ?? 0, 1) . '%',
                    'avgAttempts' => round($overall->avg_attempts ?? 0, 1),
                ],
                'byExerciseType' => $byType->map(function ($item) {
                    return [
                        'type' => $item->exercise_type,
                        'attempts' => $item->attempts,
                        'avgScore' => round($item->avg_score, 1),
                        'successRate' => round($item->success_rate, 1) . '%',
                        'avgTimeSpent' => round($item->avg_time_spent / 60, 1) . ' min',
                    ];
                }),
                'challengingExercises' => $challenging->map(function ($item) {
                    return [
                        'exerciseId' => $item->exercise_id,
                        'type' => $item->exercise_type,
                        'attempts' => $item->attempts,
                        'avgScore' => round($item->avg_score, 1),
                        'successRate' => round($item->success_rate, 1) . '%',
                    ];
                }),
                'timeAnalysis' => [
                    'avgTimeCorrect' => round(($timeAnalysis->avg_time_correct ?? 0) / 60, 1) . ' min',
                    'avgTimeIncorrect' => round(($timeAnalysis->avg_time_incorrect ?? 0) / 60, 1) . ' min',
                ]
            ];
        });

        return response()->json($data);
    }
    /**
     * Get engagement analytics - FIXED: Remove outer where clause
     */
    public function getEngagementAnalytics(Request $request): JsonResponse
    {
        $timeRange = $request->get('timeRange', '30days');
        $projectId = $request->get('projectId');

        $cacheKey = "analytics:engagement:{$timeRange}:{$projectId}";
        $data = Cache::remember($cacheKey, 600, function () use ($timeRange, $projectId) {
            $dateFilter = $this->getDateFilter($timeRange);

            $query = UsageTrackingSession::where('started_at', '>=', $dateFilter);
            
            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            // Session duration analysis
            $durationStats = $query->clone()
                ->whereNotNull('duration_seconds')
                ->selectRaw("
                    AVG(duration_seconds) as avg_duration,
                    MIN(duration_seconds) as min_duration,
                    MAX(duration_seconds) as max_duration,
                    COUNT(*) as total_sessions
                ")
                ->first();

            // Peak usage hours
            $peakHours = $query->clone()
                ->selectRaw("HOUR(started_at) as hour, COUNT(*) as sessions")
                ->groupBy(DB::raw('HOUR(started_at)'))
                ->orderByDesc('sessions')
                ->limit(6)
                ->get();

            // Daily engagement trend
            $dailyTrend = $query->clone()
                ->selectRaw("DATE(started_at) as date, COUNT(*) as sessions, COUNT(DISTINCT device_id) as unique_devices")
                ->where('started_at', '>=', Carbon::now()->subDays(30))
                ->groupBy(DB::raw('DATE(started_at)'))
                ->orderBy('date')
                ->get();

            // Return rate (devices with multiple sessions) - FIXED: Remove outer where clause
            $returnRate = DB::table(DB::raw('(SELECT device_id, COUNT(*) as session_count 
                                            FROM usage_tracking_sessions 
                                            WHERE started_at >= ? 
                                            GROUP BY device_id) as device_sessions'))
                ->setBindings([$dateFilter])
                ->selectRaw("
                    COUNT(DISTINCT device_id) as total_devices,
                    SUM(CASE WHEN session_count > 1 THEN 1 ELSE 0 END) as returning_devices
                ")
                ->first();

            return [
                'sessionDuration' => [
                    'avg' => round(($durationStats->avg_duration ?? 0) / 60, 1) . ' min',
                    'min' => round(($durationStats->min_duration ?? 0) / 60, 1) . ' min',
                    'max' => round(($durationStats->max_duration ?? 0) / 60, 1) . ' min',
                    'totalSessions' => $durationStats->total_sessions ?? 0,
                ],
                'peakHours' => $peakHours->map(function ($item) {
                    return [
                        'hour' => $item->hour,
                        'label' => $item->hour . ':00',
                        'sessions' => $item->sessions,
                    ];
                }),
                'dailyTrend' => $dailyTrend->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'sessions' => $item->sessions,
                        'uniqueDevices' => $item->unique_devices,
                    ];
                }),
                'returnRate' => [
                    'totalDevices' => $returnRate->total_devices ?? 0,
                    'returningDevices' => $returnRate->returning_devices ?? 0,
                    'returnRate' => $returnRate->total_devices ? round(($returnRate->returning_devices / $returnRate->total_devices) * 100, 1) : 0,
                ]
            ];
        });

        return response()->json($data);
    }

    /**
     * Get feedback analytics
     */
    public function getFeedbackAnalytics(Request $request): JsonResponse
    {
        $timeRange = $request->get('timeRange', '30days');
        
        $cacheKey = "analytics:feedback:{$timeRange}";
        $data = Cache::remember($cacheKey, 600, function () use ($timeRange) {
            $dateFilter = $this->getDateFilter($timeRange);

            $feedback = UsageTrackingFeedback::where('created_at', '>=', $dateFilter)->get();

            $byType = $feedback->groupBy('feedback_type')->map(function ($items, $type) {
                return [
                    'count' => $items->count(),
                    'avgRating' => $items->whereNotNull('rating')->avg('rating'),
                ];
            });

            $byUserType = $feedback->groupBy('user_type')->map->count();

            $recentFeedback = $feedback->sortByDesc('created_at')->take(10)->values();

            $sentiment = [
                'positive' => $feedback->where('rating', '>=', 4)->count(),
                'neutral' => $feedback->where('rating', 3)->count(),
                'negative' => $feedback->where('rating', '<=', 2)->count(),
            ];

            return [
                'summary' => [
                    'total' => $feedback->count(),
                    'avgRating' => round($feedback->whereNotNull('rating')->avg('rating') ?? 0, 1),
                    'byType' => $byType,
                    'byUserType' => $byUserType,
                ],
                'sentiment' => $sentiment,
                'recent' => $recentFeedback->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'userType' => $item->user_type,
                        'feedbackType' => $item->feedback_type,
                        'rating' => $item->rating,
                        'message' => Str::limit($item->message, 100),
                        'project' => $item->project->name ?? 'Unknown',
                        'createdAt' => $item->created_at->toISOString(),
                        'status' => $item->status,
                    ];
                }),
            ];
        });

        return response()->json($data);
    }

    /**
     * Helper method to get date filter based on time range
     */
    private function getDateFilter(string $timeRange): Carbon
    {
        return match ($timeRange) {
            '7days' => Carbon::now()->subDays(7),
            '30days' => Carbon::now()->subDays(30),
            '6months' => Carbon::now()->subMonths(6),
            '1year' => Carbon::now()->subYear(),
            'all' => Carbon::createFromDate(2000, 1, 1), // Very old date
            default => Carbon::now()->subDays(30),
        };
    }
    

    /**
     * Get section-level analytics - FIXED GROUP BY
     */
    public function getSectionAnalytics(Request $request): JsonResponse
    {
        $timeRange = $request->get('timeRange', '30days');
        $projectId = $request->get('projectId');
        
        $cacheKey = "analytics:sections:{$timeRange}:{$projectId}";
        $data = Cache::remember($cacheKey, 600, function () use ($timeRange, $projectId) {
            $dateFilter = $this->getDateFilter($timeRange);

            $sections = DB::table('usage_tracking_events')
                ->join('sections', 'usage_tracking_events.section_id', '=', 'sections.id')
                ->join('projects', 'sections.project_id', '=', 'projects.id')
                ->where('usage_tracking_events.event_type', 'section_access')
                ->where('usage_tracking_events.timestamp', '>=', $dateFilter)
                ->when($projectId, function ($query) use ($projectId) {
                    $query->where('usage_tracking_events.project_id', $projectId);
                })
                ->select(
                    'sections.id',
                    'sections.name as section_name',
                    'projects.name as project_name',
                    DB::raw('COUNT(*) as total_accesses'),
                    DB::raw('COUNT(DISTINCT usage_tracking_events.device_id) as unique_devices'),
                    DB::raw('SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(usage_tracking_events.device_info, "$.device_type")) = "mobile" THEN 1 ELSE 0 END) as mobile_accesses'),
                    DB::raw('SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(usage_tracking_events.device_info, "$.device_type")) = "desktop" THEN 1 ELSE 0 END) as desktop_accesses'),
                    DB::raw('SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(usage_tracking_events.device_info, "$.device_type")) = "tablet" THEN 1 ELSE 0 END) as tablet_accesses')
                )
                ->groupBy('sections.id', 'sections.name', 'projects.name', 'sections.id') // Added sections.id to GROUP BY
                ->orderByDesc('total_accesses')
                ->get();

            return [
                'sections' => $sections,
                'summary' => [
                    'total_sections_accessed' => $sections->count(),
                    'total_section_accesses' => $sections->sum('total_accesses'),
                    'avg_access_per_section' => round($sections->avg('total_accesses') ?? 0, 1)
                ]
            ];
        });

        return response()->json($data);
    }

    /**
     * Get unit-level analytics - FIXED GROUP BY
     */
    public function getUnitAnalytics(Request $request): JsonResponse
    {
        $timeRange = $request->get('timeRange', '30days');
        $projectId = $request->get('projectId');
        $sectionId = $request->get('sectionId');
        
        $cacheKey = "analytics:units:{$timeRange}:{$projectId}:{$sectionId}";
        $data = Cache::remember($cacheKey, 600, function () use ($timeRange, $projectId, $sectionId) {
            $dateFilter = $this->getDateFilter($timeRange);

            $units = DB::table('usage_tracking_events')
                ->join('units', 'usage_tracking_events.unit_id', '=', 'units.id')
                ->join('sections', 'units.section_id', '=', 'sections.id')
                ->join('projects', 'sections.project_id', '=', 'projects.id')
                ->where('usage_tracking_events.event_type', 'unit_access')
                ->where('usage_tracking_events.timestamp', '>=', $dateFilter)
                ->when($projectId, function ($query) use ($projectId) {
                    $query->where('usage_tracking_events.project_id', $projectId);
                })
                ->when($sectionId, function ($query) use ($sectionId) {
                    $query->where('usage_tracking_events.section_id', $sectionId);
                })
                ->select(
                    'units.id',
                    'units.name as unit_name',
                    'sections.name as section_name',
                    'projects.name as project_name',
                    DB::raw('COUNT(*) as total_accesses'),
                    DB::raw('COUNT(DISTINCT usage_tracking_events.device_id) as unique_devices'),
                    DB::raw('SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(usage_tracking_events.device_info, "$.device_type")) = "mobile" THEN 1 ELSE 0 END) as mobile_accesses'),
                    DB::raw('SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(usage_tracking_events.device_info, "$.device_type")) = "desktop" THEN 1 ELSE 0 END) as desktop_accesses'),
                    DB::raw('SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(usage_tracking_events.device_info, "$.device_type")) = "tablet" THEN 1 ELSE 0 END) as tablet_accesses')
                )
                ->groupBy('units.id', 'units.name', 'sections.name', 'projects.name', 'sections.id', 'projects.id') // Added all non-aggregated columns
                ->orderByDesc('total_accesses')
                ->get();

            return [
                'units' => $units,
                'summary' => [
                    'total_units_accessed' => $units->count(),
                    'total_unit_accesses' => $units->sum('total_accesses'),
                    'most_accessed_unit' => $units->first()
                ]
            ];
        });

        return response()->json($data);
    }
}