<?php
// routes/api.php

use App\Http\Controllers\AccordionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulkUpdateController;
use App\Http\Controllers\ContentBlockController;
use App\Http\Controllers\ContentBlockMediaController;
use App\Http\Controllers\DragMatchItemController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\ObjectiveController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReorderController;
use App\Http\Controllers\UnitHeaderMediaController;
use App\Http\Controllers\ProjectStatusController;
use App\Http\Controllers\ProjectTeamController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // ==================== PROJECT MANAGEMENT ====================
        // Project Statuses
        Route::get('/project-statuses', [ProjectStatusController::class, 'index']);
        
        // Enhanced Projects with team management
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

        // Project Team Management
        Route::prefix('projects/{project}')->group(function () {
            Route::post('/team', [ProjectTeamController::class, 'addMember']);
            Route::patch('/team/{teamMember}', [ProjectTeamController::class, 'updateMemberRole']);
            Route::delete('/team/{teamMember}', [ProjectTeamController::class, 'removeMember']);
            Route::patch('/sign-off', [ProjectTeamController::class, 'updateSignOffPerson']);
            Route::get('/access', [ProjectTeamController::class, 'checkAccess']);
        });

        // ==================== CONTENT MANAGEMENT ====================
        // Sections
        Route::get('/projects/{project}/sections', [SectionController::class, 'index']);
        Route::post('/projects/{project}/sections', [SectionController::class, 'store']);
        Route::put('/sections/{section}', [SectionController::class, 'update']);
        Route::delete('/sections/{section}', [SectionController::class, 'destroy']);

        // Units
        Route::get('/sections/{section}/units', [UnitController::class, 'index']);
        Route::post('/sections/{section}/units', [UnitController::class, 'store']);
        Route::get('/units/{unit}', [UnitController::class, 'show']);
        Route::put('/units/{unit}', [UnitController::class, 'update']);
        Route::delete('/units/{unit}', [UnitController::class, 'destroy']);

        // Media Library
        Route::get('/media', [MediaController::class, 'index']);
        Route::post('/media', [MediaController::class, 'store']);
        Route::delete('/media/{media}', [MediaController::class, 'destroy']);

        // Default Images
        Route::get('/default-images', [MediaController::class, 'defaultImages']);

        // Labels
        Route::get('/labels', [LabelController::class, 'index']);
        Route::put('/labels', [LabelController::class, 'update']);

        // Reorder endpoints
        Route::put('/units/{unit}/objectives/reorder', [ReorderController::class, 'reorderObjectives']);
        Route::put('/units/{unit}/accordions/reorder', [ReorderController::class, 'reorderAccordions']);
        Route::put('/accordions/{accordion}/content/reorder', [ReorderController::class, 'reorderContentBlocks']);
        Route::put('/accordions/{accordion}/exercises/reorder', [ReorderController::class, 'reorderExercises']);
        Route::put('/exercises/{exercise}/questions/reorder', [ReorderController::class, 'reorderQuestions']);

        // Individual update endpoints
        Route::patch('/objectives/{objective}', [ObjectiveController::class, 'update']);
        Route::delete('/objectives/{objective}', [ObjectiveController::class, 'destroy']);
        
        Route::patch('/accordions/{accordion}', [AccordionController::class, 'update']);
        Route::delete('/accordions/{accordion}', [AccordionController::class, 'destroy']);
        
        Route::patch('/exercises/{exercise}', [ExerciseController::class, 'update']);
        Route::delete('/exercises/{exercise}', [ExerciseController::class, 'destroy']);
        
        Route::patch('/questions/{question}', [QuestionController::class, 'update']);
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy']);

        Route::patch('/content-blocks/{contentBlock}', [ContentBlockController::class, 'update']);
        Route::delete('/content-blocks/{contentBlock}', [ContentBlockController::class, 'destroy']);

        Route::patch('/drag-match-items/{dragMatchItem}', [DragMatchItemController::class, 'update']);
        Route::delete('/drag-match-items/{dragMatchItem}', [DragMatchItemController::class, 'destroy']);

        Route::patch('/units/{unit}/bulk-update', [BulkUpdateController::class, 'updateUnitStructure']);

        Route::patch('/content-blocks/{contentBlock}', [ContentBlockController::class, 'update']);
        Route::delete('/content-blocks/{contentBlock}', [ContentBlockController::class, 'destroy']);

        Route::patch('/content-block-media/{contentBlockMedia}', [ContentBlockMediaController::class, 'update']);
        Route::delete('/content-block-media/{contentBlockMedia}', [ContentBlockMediaController::class, 'destroy']);

        Route::patch('/unit-header-media/{unitHeaderMedia}', [UnitHeaderMediaController::class, 'update']);
        Route::delete('/unit-header-media/{unitHeaderMedia}', [UnitHeaderMediaController::class, 'destroy']);

      // ==================== ADMIN ROUTES ====================
        Route::middleware('admin')->prefix('admin')->group(function () {
            // User Management
            Route::apiResource('users', UserManagementController::class);
            Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword']);
            Route::patch('/users/{user}/role', [UserManagementController::class, 'updateRole']);

            // Project Status Management (Admin only)
            Route::apiResource('project-statuses', ProjectStatusController::class)->except(['index', 'show']);
            Route::post('/project-statuses', [ProjectStatusController::class, 'store']);
            Route::put('/project-statuses/{projectStatus}', [ProjectStatusController::class, 'update']);
            Route::delete('/project-statuses/{projectStatus}', [ProjectStatusController::class, 'destroy']);

            // Settings Management
            Route::prefix('settings')->group(function () {
                Route::get('/', [SettingsController::class, 'index']);
                Route::put('/', [SettingsController::class, 'update']);
                Route::patch('/{category}', [SettingsController::class, 'updateCategory']);
                Route::post('/validate', [SettingsController::class, 'validateSettings']);
            });

            // Reporting & Analytics
            Route::prefix('reports')->group(function () {
                Route::get('/summary', [ReportController::class, 'getDashboardSummary']);
                Route::get('/users', [ReportController::class, 'getUserAnalytics']);
                Route::get('/projects', [ReportController::class, 'getProjectStatistics']);
                Route::get('/content', [ReportController::class, 'getContentAnalytics']);
                Route::get('/activity', [ReportController::class, 'getActivityLog']);
                Route::get('/usage', [ReportController::class, 'getUsageStatistics']);
                Route::post('/export', [ReportController::class, 'exportReport']);
                Route::post('/custom', [ReportController::class, 'getCustomReport']);
            });

            // System Management
            // Route::prefix('system')->group(function () {
            //     Route::get('/health', [SystemController::class, 'healthCheck']);
            //     Route::post('/backup', [SystemController::class, 'createBackup']);
            //     Route::get('/logs', [SystemController::class, 'getSystemLogs']);
            //     Route::post('/cache/clear', [SystemController::class, 'clearCache']);
            // });
        });
    });
});