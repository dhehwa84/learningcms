<?php
// database/seeders/DefaultImagesSeeder.php

namespace Database\Seeders;

use App\Models\DefaultImage;
use Illuminate\Database\Seeder;

class DefaultImagesSeeder extends Seeder
{
    public function run(): void
    {
        $defaultImages = [
            // Icons
            ['filename' => 'book.svg', 'url' => '/defaults/icons/book.svg', 'alt' => 'Book icon', 'category' => 'icon'],
            ['filename' => 'exercise.svg', 'url' => '/defaults/icons/exercise.svg', 'alt' => 'Exercise icon', 'category' => 'icon'],
            ['filename' => 'video.svg', 'url' => '/defaults/icons/video.svg', 'alt' => 'Video icon', 'category' => 'icon'],
            ['filename' => 'audio.svg', 'url' => '/defaults/icons/audio.svg', 'alt' => 'Audio icon', 'category' => 'icon'],
            
            // Illustrations
            ['filename' => 'student.svg', 'url' => '/defaults/illustrations/student.svg', 'alt' => 'Student illustration', 'category' => 'illustration'],
            ['filename' => 'teacher.svg', 'url' => '/defaults/illustrations/teacher.svg', 'alt' => 'Teacher illustration', 'category' => 'illustration'],
            ['filename' => 'classroom.svg', 'url' => '/defaults/illustrations/classroom.svg', 'alt' => 'Classroom illustration', 'category' => 'illustration'],
        ];

        foreach ($defaultImages as $image) {
            DefaultImage::create($image);
        }
    }
}