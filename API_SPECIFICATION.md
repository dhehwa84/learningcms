# Learning CMS - Complete API Specification for Laravel Backend

## Overview
This document provides complete API specifications for building the Laravel backend. Every field, every configurable element, and every relationship is documented here.

## Base URL
\`\`\`
https://api.yourdomain.com/api/v1
\`\`\`

## Authentication
All endpoints require Bearer token authentication.

\`\`\`
Authorization: Bearer {token}
\`\`\`

---

## 1. Projects API

### GET /projects
Get all projects for authenticated user

**Response:**
\`\`\`json
{
  "data": [
    {
      "id": "1",
      "name": "Siswati Grade 12",
      "description": "Complete curriculum for grade 12",
      "language": "siswati",
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z",
      "sections_count": 5
    }
  ]
}
\`\`\`

### POST /projects
Create new project

**Request:**
\`\`\`json
{
  "name": "New Project",
  "description": "Project description",
  "language": "en"
}
\`\`\`

### GET /projects/{id}
Get single project with all sections

### PUT /projects/{id}
Update project

**Request:**
\`\`\`json
{
  "name": "Updated Project Name",
  "description": "Updated description",
  "language": "siswati"
}
\`\`\`

### DELETE /projects/{id}
Delete project and all nested data

---

## 2. Sections API

### GET /projects/{projectId}/sections
Get all sections in a project

**Response:**
\`\`\`json
{
  "data": [
    {
      "id": "s1",
      "project_id": "1",
      "name": "Libanga 12",
      "description": "Section description",
      "order": 1,
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z",
      "units_count": 10
    }
  ]
}
\`\`\`

### POST /projects/{projectId}/sections
Create new section

**Request:**
\`\`\`json
{
  "name": "New Section",
  "description": "Section description",
  "order": 1
}
\`\`\`

### PUT /sections/{id}
Update section (including reordering)

**Request:**
\`\`\`json
{
  "name": "Updated Section",
  "description": "Updated description",
  "order": 2
}
\`\`\`

### DELETE /sections/{id}
Delete section and all units

---

## 3. Units API (COMPLETE)

### GET /sections/{sectionId}/units
Get all units in a section (summary view)

**Response:**
\`\`\`json
{
  "data": [
    {
      "id": "u1",
      "section_id": "s1",
      "number": 1,
      "title": "Umsebenti we-1",
      "grade": "Libanga 12",
      "theme": "Tinhloso",
      "order": 1,
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-01T00:00:00Z"
    }
  ]
}
\`\`\`

### GET /units/{id}
Get complete unit with ALL content and nested structures

**Response (COMPLETE STRUCTURE):**
\`\`\`json
{
  "id": "u1",
  "section_id": "s1",
  "number": 1,
  "title": "Umsebenti we-1",
  "grade": "Libanga 12",
  "theme": "Tinhloso",
  "order": 1,
  
  // Header Media Configuration
  "header_media": {
    "id": "hm1",
    "type": "video",
    "url": "/media/videos/intro.mp4",
    "alt": "Introduction video",
    "caption": "Watch this introduction"
  },
  
  // Objectives List (draggable/reorderable)
  "objectives": [
    {
      "id": "obj1",
      "text": "Kufundza ngekucophelela nangekuvisisa",
      "order": 1
    },
    {
      "id": "obj2",
      "text": "Kuhlunga, kubona emaphuzu lamcoka endzabeni",
      "order": 2
    }
  ],
  
  // Accordions (Content Sections) - draggable/reorderable
  "accordions": [
    {
      "id": "acc1",
      "title": "Ngembikwekufundza",
      "icon_url": "/media/icons/book.svg",
      "order": 1,
      
      // Content Blocks inside accordion - draggable/reorderable
      "content": [
        {
          "id": "cb1",
          "type": "text",
          "order": 1,
          "content": "Simple text content here"
        },
        {
          "id": "cb2",
          "type": "richtext",
          "order": 2,
          "content": "<h2>Rich Text Content</h2><p>With <strong>formatting</strong> and <a href='#'>links</a></p><table><tr><td>Table data</td></tr></table>"
        },
        {
          "id": "cb3",
          "type": "image",
          "order": 3,
          "media": {
            "id": "m1",
            "type": "image",
            "url": "/media/images/diagram.jpg",
            "alt": "Diagram showing concept",
            "caption": "Figure 1: Important diagram"
          }
        },
        {
          "id": "cb4",
          "type": "video",
          "order": 4,
          "media": {
            "id": "m2",
            "type": "video",
            "url": "/media/videos/lesson.mp4",
            "alt": "Lesson video",
            "caption": "Watch this lesson"
          }
        },
        {
          "id": "cb5",
          "type": "audio",
          "order": 5,
          "media": {
            "id": "m3",
            "type": "audio",
            "url": "/media/audio/pronunciation.mp3",
            "alt": "Pronunciation guide"
          }
        },
        {
          "id": "cb6",
          "type": "grid",
          "order": 6,
          "layout": "two-column",
          "grid_items": [
            {
              "id": "gi1",
              "type": "image",
              "url": "/media/images/img1.jpg",
              "alt": "Image 1"
            },
            {
              "id": "gi2",
              "type": "image",
              "url": "/media/images/img2.jpg",
              "alt": "Image 2"
            }
          ]
        }
      ]
    },
    {
      "id": "acc2",
      "title": "Nakufundvwa",
      "icon_url": "/media/icons/exercise.svg",
      "order": 2,
      "content": [],
      
      // Exercises inside accordion - can have multiple
      "exercises": [
        {
          "id": "ex1",
          "type": "mixed",
          "title": "Dvonsa ucondzanise libintana lelisetjentiswa endzabeni nenchazelo lonikwe yona",
          "order": 1,
          "question_numbering": "123",
          
          // Configurable button labels and messages
          "labels": {
            "submit_button": "Timphendvulo letiphakanyisiwe",
            "clear_button": "Cisha",
            "correct_message": "Kulungile! Uphendvule kahle",
            "incorrect_message": "Lutsa kutsi akusiko kahle",
            "incomplete_message": "Sicela uphendvule yonkhe imibutso"
          },
          
          // Multiple questions in one exercise - draggable/reorderable
          "questions": [
            {
              "id": "q1",
              "question": "Bantfu labanetinhlitivo letingangetendlovu",
              "type": "dropdown",
              "order": 1,
              "options": [
                {"id": "opt1", "text": "Khetsa imphendvulo", "is_correct": false},
                {"id": "opt2", "text": "Correct answer", "is_correct": true},
                {"id": "opt3", "text": "Wrong answer", "is_correct": false}
              ],
              "correct_answer": "opt2"
            },
            {
              "id": "q2",
              "question": "Kumba ecolo",
              "type": "dropdown",
              "order": 2,
              "options": [
                {"id": "opt1", "text": "Khetsa imphendvulo", "is_correct": false},
                {"id": "opt2", "text": "Option 1", "is_correct": false},
                {"id": "opt3", "text": "Correct option", "is_correct": true}
              ],
              "correct_answer": "opt3"
            },
            {
              "id": "q3",
              "question": "What is the capital?",
              "type": "radio",
              "order": 3,
              "options": [
                {"id": "opt1", "text": "London", "is_correct": false},
                {"id": "opt2", "text": "Paris", "is_correct": true},
                {"id": "opt3", "text": "Berlin", "is_correct": false}
              ],
              "correct_answer": "opt2"
            },
            {
              "id": "q4",
              "question": "Select all correct answers",
              "type": "checkbox",
              "order": 4,
              "options": [
                {"id": "opt1", "text": "Option A", "is_correct": true},
                {"id": "opt2", "text": "Option B", "is_correct": false},
                {"id": "opt3", "text": "Option C", "is_correct": true}
              ],
              "correct_answers": ["opt1", "opt3"]
            },
            {
              "id": "q5",
              "question": "Enter your answer:",
              "type": "text",
              "order": 5,
              "correct_answer": "Expected text answer"
            },
            {
              "id": "q6",
              "question": "Enter a number:",
              "type": "number",
              "order": 6,
              "correct_answer": "42"
            }
          ]
        },
        {
          "id": "ex2",
          "type": "drag-match",
          "title": "Match the items by dragging from left to right",
          "order": 2,
          "question_numbering": "abc",
          "labels": {
            "submit_button": "Check Answers",
            "clear_button": "Reset",
            "correct_message": "Perfect! All matches are correct",
            "incorrect_message": "Some matches are incorrect",
            "incomplete_message": "Please match all items"
          },
          
          // Drag and match pairs
          "drag_match_items": [
            {
              "id": "dm1",
              "order": 1,
              "left_side": {
                "type": "text",
                "value": "Apple",
                "alt": null
              },
              "right_side": {
                "type": "text",
                "value": "A fruit",
                "alt": null
              }
            },
            {
              "id": "dm2",
              "order": 2,
              "left_side": {
                "type": "image",
                "value": "/media/images/cat.jpg",
                "alt": "Picture of a cat"
              },
              "right_side": {
                "type": "text",
                "value": "A domestic animal",
                "alt": null
              }
            },
            {
              "id": "dm3",
              "order": 3,
              "left_side": {
                "type": "text",
                "value": "Paris",
                "alt": null
              },
              "right_side": {
                "type": "image",
                "value": "/media/images/eiffel.jpg",
                "alt": "Eiffel Tower"
              }
            }
          ]
        }
      ]
    }
  ],
  
  "created_at": "2025-01-01T00:00:00Z",
  "updated_at": "2025-01-01T00:00:00Z"
}
\`\`\`

### POST /sections/{sectionId}/units
Create new unit with complete structure

**Request:** Same structure as GET response above

### PUT /units/{id}
Update complete unit - THIS IS THE KEY ENDPOINT
This endpoint receives the entire unit structure and updates everything:
- Unit metadata (title, grade, theme)
- Header media
- Objectives (with reordering)
- Accordions (with reordering)
- Content blocks within accordions (with reordering)
- Exercises (with reordering)
- Questions within exercises (with reordering)
- All labels and configurations

**Request:** Same complete structure as GET response

**Implementation Note:** Laravel should handle this transactionally - update all nested relationships in one database transaction.

### DELETE /units/{id}
Delete unit and all nested data

---

## 4. Media Library API

### GET /media
Get all media files for user with filtering

**Query Parameters:**
- `type`: Filter by type (image/audio/video)
- `search`: Search filename
- `page`: Pagination

**Response:**
\`\`\`json
{
  "data": [
    {
      "id": "m1",
      "filename": "diagram.jpg",
      "type": "image",
      "url": "/storage/media/diagram.jpg",
      "thumbnail": "/storage/media/thumbs/diagram.jpg",
      "alt": "Diagram description",
      "size": 102400,
      "mime_type": "image/jpeg",
      "dimensions": {
        "width": 1920,
        "height": 1080
      },
      "created_at": "2025-01-01T00:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50
  }
}
\`\`\`

### POST /media
Upload media file

**Request:** multipart/form-data
\`\`\`
file: [binary]
type: "image" | "audio" | "video"
alt: "Optional description"
\`\`\`

**Response:**
\`\`\`json
{
  "id": "m1",
  "filename": "diagram.jpg",
  "type": "image",
  "url": "/storage/media/diagram.jpg",
  "thumbnail": "/storage/media/thumbs/diagram.jpg",
  "size": 102400
}
\`\`\`

### DELETE /media/{id}
Delete media file

---

## 5. Default Images/Icons API

### GET /default-images
Get system default images categorized

**Response:**
\`\`\`json
{
  "icons": [
    {
      "id": "icon1",
      "url": "/defaults/icons/book.svg",
      "alt": "Book icon",
      "category": "icon"
    }
  ],
  "illustrations": [
    {
      "id": "ill1",
      "url": "/defaults/illustrations/student.svg",
      "alt": "Student illustration",
      "category": "illustration"
    }
  ]
}
\`\`\`

---

## 6. Labels (UI Customization) API

### GET /labels
Get all customizable labels for current user

**Response:**
\`\`\`json
{
  "default_exercise_labels": {
    "submit_button": "Timphendvulo letiphakanyisiwe",
    "clear_button": "Cisha",
    "correct_message": "Kulungile!",
    "incorrect_message": "Lutsa kutsi",
    "incomplete_message": "Sicela uphendvule"
  },
  "ui_labels": {
    "objectives_title": "Ekupheleni kwalomsebenti utawuzuza loku",
    "media_player_title": "Cindzetela inkinobho ulalele",
    "exercise_title": "Nakufundvwa"
  }
}
\`\`\`

### PUT /labels
Update labels

**Request:**
\`\`\`json
{
  "default_exercise_labels": {
    "submit_button": "Submit",
    "clear_button": "Clear"
  }
}
\`\`\`

---

## Database Schema (Complete)

### 1. projects
\`\`\`sql
CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    language VARCHAR(10) DEFAULT 'en',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);
\`\`\`

### 2. sections
\`\`\`sql
CREATE TABLE sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project_id (project_id),
    INDEX idx_order (project_id, order)
);
\`\`\`

### 3. units
\`\`\`sql
CREATE TABLE units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id BIGINT UNSIGNED NOT NULL,
    number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    grade VARCHAR(100),
    theme VARCHAR(255),
    order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    INDEX idx_section_id (section_id),
    INDEX idx_order (section_id, order)
);
\`\`\`

### 4. unit_header_media
\`\`\`sql
CREATE TABLE unit_header_media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unit_id BIGINT UNSIGNED NOT NULL UNIQUE,
    type ENUM('audio', 'video', 'image') NOT NULL,
    url VARCHAR(500) NOT NULL,
    alt VARCHAR(255),
    caption TEXT,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
);
\`\`\`

### 5. objectives
\`\`\`sql
CREATE TABLE objectives (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unit_id BIGINT UNSIGNED NOT NULL,
    text TEXT NOT NULL,
    order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    INDEX idx_unit_order (unit_id, order)
);
\`\`\`

### 6. accordions
\`\`\`sql
CREATE TABLE accordions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unit_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    icon_url VARCHAR(500),
    order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    INDEX idx_unit_order (unit_id, order)
);
\`\`\`

### 7. content_blocks
\`\`\`sql
CREATE TABLE content_blocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    accordion_id BIGINT UNSIGNED NOT NULL,
    type ENUM('text', 'richtext', 'image', 'video', 'audio', 'grid') NOT NULL,
    order INT NOT NULL DEFAULT 0,
    content LONGTEXT, -- For text/richtext
    layout VARCHAR(50), -- For grid: 'single', 'two-column', 'grid'
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (accordion_id) REFERENCES accordions(id) ON DELETE CASCADE,
    INDEX idx_accordion_order (accordion_id, order)
);
\`\`\`

### 8. content_block_media
\`\`\`sql
CREATE TABLE content_block_media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_block_id BIGINT UNSIGNED NOT NULL,
    type ENUM('image', 'video', 'audio') NOT NULL,
    url VARCHAR(500) NOT NULL,
    alt VARCHAR(255),
    caption TEXT,
    order INT DEFAULT 0, -- For grid items
    FOREIGN KEY (content_block_id) REFERENCES content_blocks(id) ON DELETE CASCADE
);
\`\`\`

### 9. exercises
\`\`\`sql
CREATE TABLE exercises (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    accordion_id BIGINT UNSIGNED NOT NULL,
    type ENUM('multiple-choice', 'radio', 'checkbox', 'text', 'number', 'mixed', 'drag-match') NOT NULL,
    title VARCHAR(500),
    order INT NOT NULL DEFAULT 0,
    question_numbering ENUM('123', 'abc', 'ABC') DEFAULT '123',
    labels JSON, -- Store submit_button, clear_button, messages
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (accordion_id) REFERENCES accordions(id) ON DELETE CASCADE,
    INDEX idx_accordion_order (accordion_id, order)
);
\`\`\`

### 10. questions
\`\`\`sql
CREATE TABLE questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exercise_id BIGINT UNSIGNED NOT NULL,
    question TEXT,
    type ENUM('dropdown', 'radio', 'checkbox', 'text', 'number', 'multiple-choice') NOT NULL,
    order INT NOT NULL DEFAULT 0,
    options JSON, -- Array of {id, text, is_correct}
    correct_answer TEXT, -- For single answer questions
    correct_answers JSON, -- For multiple answer questions (checkbox)
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE,
    INDEX idx_exercise_order (exercise_id, order)
);
\`\`\`

### 11. drag_match_items
\`\`\`sql
CREATE TABLE drag_match_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exercise_id BIGINT UNSIGNED NOT NULL,
    order INT NOT NULL DEFAULT 0,
    left_type ENUM('text', 'image') NOT NULL,
    left_value TEXT NOT NULL, -- text content or image URL
    left_alt VARCHAR(255),
    right_type ENUM('text', 'image') NOT NULL,
    right_value TEXT NOT NULL,
    right_alt VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE,
    INDEX idx_exercise_order (exercise_id, order)
);
\`\`\`

### 12. media
\`\`\`sql
CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    filename VARCHAR(255) NOT NULL,
    type ENUM('image', 'audio', 'video') NOT NULL,
    url VARCHAR(500) NOT NULL,
    thumbnail VARCHAR(500),
    alt VARCHAR(255),
    size INT,
    mime_type VARCHAR(100),
    width INT, -- For images
    height INT, -- For images
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type)
);
\`\`\`

### 13. default_images
\`\`\`sql
CREATE TABLE default_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    alt VARCHAR(255),
    category ENUM('icon', 'illustration', 'photo', 'decoration') NOT NULL,
    created_at TIMESTAMP,
    INDEX idx_category (category)
);
\`\`\`

### 14. labels
\`\`\`sql
CREATE TABLE labels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    key VARCHAR(100) NOT NULL,
    value TEXT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_key (user_id, key)
);
\`\`\`

---

## Laravel Models with Relationships

### Unit.php
\`\`\`php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'section_id', 'number', 'title', 'grade', 'theme', 'order'
    ];

    protected $with = ['headerMedia', 'objectives', 'accordions'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function headerMedia()
    {
        return $this->hasOne(UnitHeaderMedia::class);
    }

    public function objectives()
    {
        return $this->hasMany(Objective::class)->orderBy('order');
    }

    public function accordions()
    {
        return $this->hasMany(Accordion::class)->orderBy('order');
    }
}
\`\`\`

### Accordion.php
\`\`\`php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accordion extends Model
{
    protected $fillable = [
        'unit_id', 'title', 'icon_url', 'order'
    ];

    protected $with = ['content', 'exercises'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function content()
    {
        return $this->hasMany(ContentBlock::class)->orderBy('order');
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class)->orderBy('order');
    }
}
\`\`\`

### Exercise.php
\`\`\`php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $fillable = [
        'accordion_id', 'type', 'title', 'order', 
        'question_numbering', 'labels'
    ];

    protected $casts = [
        'labels' => 'array'
    ];

    protected $with = ['questions', 'dragMatchItems'];

    public function accordion()
    {
        return $this->belongsTo(Accordion::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function dragMatchItems()
    {
        return $this->hasMany(DragMatchItem::class)->orderBy('order');
    }
}
\`\`\`

### Question.php
\`\`\`php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'exercise_id', 'question', 'type', 'order',
        'options', 'correct_answer', 'correct_answers'
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array'
    ];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
\`\`\`

---

## API Controller Examples

### UnitController.php
\`\`\`php
<?php

namespace App\Http\Controllers\API;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    /**
     * Get complete unit with all nested data
     */
    public function show($id)
    {
        $unit = Unit::with([
            'headerMedia',
            'objectives',
            'accordions.content.media',
            'accordions.exercises.questions',
            'accordions.exercises.dragMatchItems'
        ])->findOrFail($id);

        return response()->json($unit);
    }

    /**
     * Update complete unit with all nested structures
     * This handles all drag-drop reordering and content updates
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'grade' => 'nullable|string',
            'theme' => 'nullable|string',
            'header_media' => 'nullable|array',
            'objectives' => 'array',
            'accordions' => 'array',
        ]);

        DB::transaction(function () use ($id, $validated) {
            $unit = Unit::findOrFail($id);
            
            // Update unit basic fields
            $unit->update([
                'title' => $validated['title'],
                'grade' => $validated['grade'] ?? null,
                'theme' => $validated['theme'] ?? null,
            ]);

            // Update header media
            if (isset($validated['header_media'])) {
                $unit->headerMedia()->updateOrCreate(
                    ['unit_id' => $unit->id],
                    $validated['header_media']
                );
            }

            // Sync objectives (with order)
            $unit->objectives()->delete();
            foreach ($validated['objectives'] ?? [] as $obj) {
                $unit->objectives()->create($obj);
            }

            // Sync accordions with nested content and exercises
            $unit->accordions()->delete();
            foreach ($validated['accordions'] ?? [] as $acc) {
                $accordion = $unit->accordions()->create([
                    'title' => $acc['title'],
                    'icon_url' => $acc['icon_url'] ?? null,
                    'order' => $acc['order']
                ]);

                // Sync content blocks
                foreach ($acc['content'] ?? [] as $content) {
                    $contentBlock = $accordion->content()->create([
                        'type' => $content['type'],
                        'order' => $content['order'],
                        'content' => $content['content'] ?? null,
                        'layout' => $content['layout'] ?? null,
                    ]);

                    // Add media if exists
                    if (isset($content['media'])) {
                        $contentBlock->media()->create($content['media']);
                    }
                    if (isset($content['grid_items'])) {
                        foreach ($content['grid_items'] as $idx => $item) {
                            $contentBlock->media()->create([...$item, 'order' => $idx]);
                        }
                    }
                }

                // Sync exercises with questions
                foreach ($acc['exercises'] ?? [] as $ex) {
                    $exercise = $accordion->exercises()->create([
                        'type' => $ex['type'],
                        'title' => $ex['title'] ?? null,
                        'order' => $ex['order'],
                        'question_numbering' => $ex['question_numbering'] ?? '123',
                        'labels' => $ex['labels'] ?? null,
                    ]);

                    // Add questions
                    foreach ($ex['questions'] ?? [] as $q) {
                        $exercise->questions()->create($q);
                    }

                    // Add drag-match items
                    foreach ($ex['drag_match_items'] ?? [] as $dm) {
                        $exercise->dragMatchItems()->create($dm);
                    }
                }
            }
        });

        return response()->json(['message' => 'Unit updated successfully']);
    }
}
\`\`\`

---

## Validation Rules

### Unit Update Validation
\`\`\`php
$rules = [
    'title' => 'required|string|max:255',
    'grade' => 'nullable|string|max:100',
    'theme' => 'nullable|string|max:255',
    'order' => 'integer|min:0',
    
    // Header Media
    'header_media.type' => 'nullable|in:audio,video,image',
    'header_media.url' => 'required_with:header_media.type|string|max:500',
    'header_media.alt' => 'nullable|string|max:255',
    'header_media.caption' => 'nullable|string',
    
    // Objectives
    'objectives' => 'array',
    'objectives.*.text' => 'required|string',
    'objectives.*.order' => 'required|integer|min:0',
    
    // Accordions
    'accordions' => 'array',
    'accordions.*.title' => 'required|string|max:255',
    'accordions.*.icon_url' => 'nullable|string|max:500',
    'accordions.*.order' => 'required|integer|min:0',
    
    // Content Blocks
    'accordions.*.content' => 'array',
    'accordions.*.content.*.type' => 'required|in:text,richtext,image,video,audio,grid',
    'accordions.*.content.*.order' => 'required|integer|min:0',
    'accordions.*.content.*.content' => 'nullable|string',
    'accordions.*.content.*.layout' => 'nullable|in:single,two-column,grid',
    
    // Exercises
    'accordions.*.exercises' => 'array',
    'accordions.*.exercises.*.type' => 'required|in:multiple-choice,radio,checkbox,text,number,mixed,drag-match',
    'accordions.*.exercises.*.title' => 'nullable|string|max:500',
    'accordions.*.exercises.*.order' => 'required|integer|min:0',
    'accordions.*.exercises.*.question_numbering' => 'nullable|in:123,abc,ABC',
    
    // Exercise Labels
    'accordions.*.exercises.*.labels.submit_button' => 'nullable|string|max:100',
    'accordions.*.exercises.*.labels.clear_button' => 'nullable|string|max:100',
    'accordions.*.exercises.*.labels.correct_message' => 'nullable|string|max:255',
    'accordions.*.exercises.*.labels.incorrect_message' => 'nullable|string|max:255',
    'accordions.*.exercises.*.labels.incomplete_message' => 'nullable|string|max:255',
    
    // Questions
    'accordions.*.exercises.*.questions' => 'array',
    'accordions.*.exercises.*.questions.*.question' => 'required|string',
    'accordions.*.exercises.*.questions.*.type' => 'required|in:dropdown,radio,checkbox,text,number,multiple-choice',
    'accordions.*.exercises.*.questions.*.order' => 'required|integer|min:0',
    'accordions.*.exercises.*.questions.*.options' => 'nullable|array',
    'accordions.*.exercises.*.questions.*.correct_answer' => 'nullable|string',
    'accordions.*.exercises.*.questions.*.correct_answers' => 'nullable|array',
    
    // Drag Match Items
    'accordions.*.exercises.*.drag_match_items' => 'nullable|array',
    'accordions.*.exercises.*.drag_match_items.*.order' => 'required|integer|min:0',
    'accordions.*.exercises.*.drag_match_items.*.left_side.type' => 'required|in:text,image',
    'accordions.*.exercises.*.drag_match_items.*.left_side.value' => 'required|string',
    'accordions.*.exercises.*.drag_match_items.*.right_side.type' => 'required|in:text,image',
    'accordions.*.exercises.*.drag_match_items.*.right_side.value' => 'required|string',
];
\`\`\`

---

## Key Implementation Notes

1. **Instant Updates**: The frontend will call `PUT /units/{id}` after every drag-drop or content change
2. **Transactional Updates**: Use database transactions to ensure data consistency
3. **Order Management**: Every draggable item has an `order` field that updates on reorder
4. **Nested Relationships**: The Unit update handles 4 levels of nesting (Unit → Accordions → Content/Exercises → Questions)
5. **File Uploads**: Media files upload separately to `/media` endpoint, then URL stored in unit data
6. **Soft Validation**: Frontend does most validation; backend does security and data integrity checks

---

## Testing with cURL

### Create a complete unit
\`\`\`bash
curl -X POST http://api.test/api/v1/sections/1/units \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d @complete-unit.json
\`\`\`

### Update unit (after drag-drop reorder)
\`\`\`bash
curl -X PUT http://api.test/api/v1/units/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Title",
    "objectives": [
      {"id": "obj1", "text": "Objective 1", "order": 0},
      {"id": "obj2", "text": "Objective 2", "order": 1}
    ]
  }'
\`\`\`

### Upload media
\`\`\`bash
curl -X POST http://api.test/api/v1/media \
  -H "Authorization: Bearer {token}" \
  -F "file=@image.jpg" \
  -F "type=image" \
  -F "alt=Image description"
