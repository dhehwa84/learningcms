<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = [
            'general' => [
                'siteName' => config('app.name', 'Content Management System'),
                'siteUrl' => config('app.url', 'http://localhost'),
                'supportEmail' => config('mail.support_email', 'support@example.com'),
                'language' => config('app.locale', 'en'),
                'timezone' => config('app.timezone', 'UTC'),
            ],
            'email' => [
                'smtpHost' => config('mail.mailers.smtp.host', ''),
                'smtpPort' => config('mail.mailers.smtp.port', '587'),
                'smtpUser' => config('mail.mailers.smtp.username', ''),
                'fromEmail' => config('mail.from.address', 'noreply@example.com'),
                'fromName' => config('mail.from.name', 'CMS System'),
            ],
            'security' => [
                'enforceStrongPasswords' => true,
                'minimumPasswordLength' => 8,
                'sessionTimeout' => 120,
                'allowMultipleSessions' => true,
                'twoFactorAuth' => false,
            ],
            'content' => [
                'defaultLanguage' => 'en',
                'allowedFileTypes' => ['jpg', 'png', 'pdf', 'doc', 'docx'],
                'maxFileSize' => 10,
                'autoSaveInterval' => 60,
            ],
            'appearance' => [
                'theme' => 'light',
                'primaryColor' => '#3b82f6',
                'accentColor' => '#f59e0b',
            ],
            'database' => [
                'backupEnabled' => true,
                'backupFrequency' => 'daily',
                'retentionDays' => 30,
            ]
        ];

        return response()->json($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'general' => 'sometimes|array',
            'general.siteName' => 'sometimes|string|max:255',
            'general.siteUrl' => 'sometimes|url',
            'general.supportEmail' => 'sometimes|email',
            'general.language' => 'sometimes|string|max:10',
            'general.timezone' => 'sometimes|timezone',
            
            'email' => 'sometimes|array',
            'email.smtpHost' => 'sometimes|string|max:255',
            'email.smtpPort' => 'sometimes|integer',
            'email.smtpUser' => 'sometimes|string|max:255',
            'email.fromEmail' => 'sometimes|email',
            'email.fromName' => 'sometimes|string|max:255',
            
            'security' => 'sometimes|array',
            'security.enforceStrongPasswords' => 'sometimes|boolean',
            'security.minimumPasswordLength' => 'sometimes|integer|min:6',
            'security.sessionTimeout' => 'sometimes|integer|min:5',
            'security.allowMultipleSessions' => 'sometimes|boolean',
            'security.twoFactorAuth' => 'sometimes|boolean',
            
            'content' => 'sometimes|array',
            'content.defaultLanguage' => 'sometimes|string|max:10',
            'content.allowedFileTypes' => 'sometimes|array',
            'content.maxFileSize' => 'sometimes|integer|min:1',
            'content.autoSaveInterval' => 'sometimes|integer|min:10',
            
            'appearance' => 'sometimes|array',
            'appearance.theme' => 'sometimes|in:light,dark,auto',
            'appearance.primaryColor' => 'sometimes|string|max:7',
            'appearance.accentColor' => 'sometimes|string|max:7',
            
            'database' => 'sometimes|array',
            'database.backupEnabled' => 'sometimes|boolean',
            'database.backupFrequency' => 'sometimes|in:daily,weekly,monthly',
            'database.retentionDays' => 'sometimes|integer|min:1',
        ]);

        // In a real app, you'd save these to database or config files
        foreach ($validated as $category => $settings) {
            foreach ($settings as $key => $value) {
                Cache::put("settings.{$category}.{$key}", $value);
            }
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'updatedAt' => now()->toISOString()
        ]);
    }

    public function updateCategory(Request $request, string $category): JsonResponse
    {
        $validated = $request->validate([
            '*' => 'required' // Validate all fields in the category
        ]);

        foreach ($validated as $key => $value) {
            Cache::put("settings.{$category}.{$key}", $value);
        }

        return response()->json([
            'message' => 'Category settings updated successfully',
            'category' => $category,
            'updatedAt' => now()->toISOString()
        ]);
    }

    public function validateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'settings' => 'required|array',
        ]);

        $errors = [];

        // Example validation for email settings
        if ($validated['category'] === 'email') {
            if (empty($validated['settings']['smtpHost'])) {
                $errors[] = [
                    'field' => 'smtpHost',
                    'message' => 'SMTP host is required'
                ];
            }
            
            if (!filter_var($validated['settings']['fromEmail'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = [
                    'field' => 'fromEmail',
                    'message' => 'From email must be a valid email address'
                ];
            }
        }

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors
        ]);
    }
}