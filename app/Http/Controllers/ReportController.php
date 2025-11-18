<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function getDashboardSummary(Request $request): JsonResponse
    {
        $timeRange = $request->get('timeRange', '30days');
        
        $summary = [
            'totalUsers' => [
                'count' => User::count(),
                'changePercent' => 12.5,
                'trend' => 'up'
            ],
            'activeProjects' => [
                'count' => Project::whereHas('teamMembers')->count(),
                'changePercent' => 8.3,
                'trend' => 'up'
            ],
            'totalUnits' => [
                'count' => Unit::count(),
                'changePercent' => 15.2,
                'trend' => 'up'
            ],
            'avgSessionTime' => [
                'minutes' => 24.5,
                'changeMinutes' => 2.1,
                'trend' => 'up'
            ]
        ];

        return response()->json($summary);
    }

    public function getUserAnalytics(Request $request): JsonResponse
    {
        $timeRange = $request->get('timeRange', '30days');
        $granularity = $request->get('granularity', 'month');

        $analytics = [
            'activityTrend' => [
                ['period' => 'Jan 2024', 'active' => 45, 'new' => 12, 'inactive' => 3],
                ['period' => 'Feb 2024', 'active' => 52, 'new' => 8, 'inactive' => 2],
                ['period' => 'Mar 2024', 'active' => 61, 'new' => 15, 'inactive' => 1],
            ],
            'roleDistribution' => [
                'admin' => User::where('role', 'admin')->count(),
                'editor' => User::where('role', 'editor')->count(),
                'viewer' => User::where('role', 'viewer')->count(),
            ],
            'statusDistribution' => [
                'active' => User::where('status', 'active')->count(),
                'inactive' => User::where('status', 'inactive')->count(),
            ],
            'loginStats' => [
                'today' => 23,
                'thisWeek' => 156,
                'thisMonth' => 542
            ]
        ];

        return response()->json($analytics);
    }

    public function getProjectStatistics(Request $request): JsonResponse
    {
        $stats = [
            'projectTrend' => [
                ['period' => 'Jan 2024', 'created' => 5, 'completed' => 2, 'active' => 12],
                ['period' => 'Feb 2024', 'created' => 8, 'completed' => 3, 'active' => 17],
                ['period' => 'Mar 2024', 'created' => 12, 'completed' => 5, 'active' => 24],
            ],
            'topProjects' => Project::withCount(['sections', 'units'])
                ->orderBy('units_count', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'name' => $project->name,
                        'units' => $project->units_count,
                        'sections' => $project->sections_count,
                        'views' => rand(100, 500),
                        'status' => 'active'
                    ];
                }),
            'languageDistribution' => Project::select('language', DB::raw('count(*) as count'))
                ->groupBy('language')
                ->pluck('count', 'language')
                ->toArray()
        ];

        return response()->json($stats);
    }

    public function getActivityLog(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 50);
        $offset = $request->get('offset', 0);

        // In a real app, you'd have an Activity model
        $activities = [
            [
                'id' => 'act_1',
                'userId' => 'user_1',
                'userName' => 'John Doe',
                'action' => 'Created project',
                'targetType' => 'project',
                'targetId' => 'proj_123',
                'targetName' => 'Siswati Grade 12',
                'actionType' => 'create',
                'timestamp' => '2024-03-15T10:30:00Z',
                'metadata' => ['language' => 'siswati']
            ],
            [
                'id' => 'act_2',
                'userId' => 'user_2',
                'userName' => 'Jane Smith',
                'action' => 'Updated unit',
                'targetType' => 'unit',
                'targetId' => 'unit_456',
                'targetName' => 'Unit 1: Reading Comprehension',
                'actionType' => 'update',
                'timestamp' => '2024-03-15T09:15:00Z',
                'metadata' => ['changes' => ['title', 'content']]
            ]
        ];

        return response()->json([
            'activities' => array_slice($activities, $offset, $limit),
            'pagination' => [
                'total' => count($activities),
                'limit' => $limit,
                'offset' => $offset
            ]
        ]);
    }
}