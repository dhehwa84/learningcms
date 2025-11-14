<?php
// config/filesystems.php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'minio' => [
            'driver' => 's3',
            'key' => env('MINIO_KEY', 'minioadmin'),
            'secret' => env('MINIO_SECRET', 'minioadmin123'),
            'region' => env('MINIO_REGION', 'us-east-1'),
            'bucket' => env('MINIO_BUCKET', 'uploads'),
            'endpoint' => env('MINIO_ENDPOINT', 'http://cmsminio:9000'),
            'use_path_style_endpoint' => env('MINIO_USE_PATH_STYLE_ENDPOINT', true),
            'throw' => false,
            'visibility' => 'public',
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];