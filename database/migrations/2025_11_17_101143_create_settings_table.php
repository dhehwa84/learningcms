<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // general, email, security, content, appearance, database
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, array
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['category', 'key']);
            $table->index('category');
        });

        // Insert default settings
        $defaultSettings = [
            // General Settings
            ['category' => 'general', 'key' => 'site_name', 'value' => 'Content Management System', 'type' => 'string', 'description' => 'Application name displayed throughout the interface'],
            ['category' => 'general', 'key' => 'site_url', 'value' => 'http://localhost:9080', 'type' => 'string', 'description' => 'Base URL of the application'],
            ['category' => 'general', 'key' => 'support_email', 'value' => 'support@example.com', 'type' => 'string', 'description' => 'Email address for support inquiries'],
            ['category' => 'general', 'key' => 'language', 'value' => 'en', 'type' => 'string', 'description' => 'Default interface language'],
            ['category' => 'general', 'key' => 'timezone', 'value' => 'UTC', 'type' => 'string', 'description' => 'Default timezone for date/time display'],
            
            // Security Settings
            ['category' => 'security', 'key' => 'enforce_strong_passwords', 'value' => 'true', 'type' => 'boolean', 'description' => 'Require complex passwords'],
            ['category' => 'security', 'key' => 'minimum_password_length', 'value' => '8', 'type' => 'integer', 'description' => 'Minimum characters for passwords'],
            ['category' => 'security', 'key' => 'session_timeout', 'value' => '120', 'type' => 'integer', 'description' => 'Minutes before session expires'],
            ['category' => 'security', 'key' => 'allow_multiple_sessions', 'value' => 'true', 'type' => 'boolean', 'description' => 'Allow concurrent logins'],
            ['category' => 'security', 'key' => 'two_factor_auth', 'value' => 'false', 'type' => 'boolean', 'description' => 'Require 2FA for all users'],
            
            // Content Settings
            ['category' => 'content', 'key' => 'default_language', 'value' => 'en', 'type' => 'string', 'description' => 'Default content language'],
            ['category' => 'content', 'key' => 'allowed_file_types', 'value' => '["jpg","png","pdf","doc","docx","mp3","mp4"]', 'type' => 'array', 'description' => 'File extensions permitted for upload'],
            ['category' => 'content', 'key' => 'max_file_size', 'value' => '10', 'type' => 'integer', 'description' => 'Maximum upload size in MB'],
            ['category' => 'content', 'key' => 'auto_save_interval', 'value' => '60', 'type' => 'integer', 'description' => 'Seconds between auto-saves'],
            
            // Appearance Settings
            ['category' => 'appearance', 'key' => 'theme', 'value' => 'light', 'type' => 'string', 'description' => 'Color scheme (light, dark, or auto)'],
            ['category' => 'appearance', 'key' => 'primary_color', 'value' => '#3b82f6', 'type' => 'string', 'description' => 'Hex color for primary UI elements'],
            ['category' => 'appearance', 'key' => 'accent_color', 'value' => '#f59e0b', 'type' => 'string', 'description' => 'Hex color for accent elements'],
            
            // Database Settings
            ['category' => 'database', 'key' => 'backup_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable automatic backups'],
            ['category' => 'database', 'key' => 'backup_frequency', 'value' => 'daily', 'type' => 'string', 'description' => 'How often to backup'],
            ['category' => 'database', 'key' => 'retention_days', 'value' => '30', 'type' => 'integer', 'description' => 'Days to keep backups'],
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};