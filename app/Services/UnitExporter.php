<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Section;
use App\Models\Unit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UnitExporter
{
    protected string $disk;
    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?: 'exports';
    }

    public function exportProject(Project $project): int
    {
        $count = 0;
        $project->load(['sections.units']);
        // Project landing
        $this->write(
            "{$project->slug}/index.html",
            view('export.project', compact('project'))->render()
        );

        foreach ($project->sections as $section) {
            $count += $this->exportSection($section);
        }
        return $count;
    }

    public function exportSection(Section $section): int
    {
        $project = $section->project;
        $section->load(['units' => fn($q)=>$q->orderBy('number')]);

        $base = "{$project->slug}/{$section->slug}";
        $this->write("{$base}/index.html", view('export.section', compact('project','section'))->render());

        $count = 0;
        foreach ($section->units as $unit) {
            $this->exportUnit($unit);
            $count++;
        }
        return $count;
    }

    public function exportUnit(Unit $unit): void
    {
        $unit->load([
            'section.project',
            'accordions.blocks.exercise.questions',
            'heroMedia','audioMedia','videoMedia'
        ]);

        $project = $unit->section->project;
        $section = $unit->section;
        $siblings = $section->units()->orderBy('number')->get(['id','number']);
        $folder = "{$project->slug}/{$section->slug}/{$unit->number}";

        // 1) Write unit index.html (uses embedded JSON + relative asset paths)
        $html = view('export.unit', [
            'project'  => $project,
            'section'  => $section,
            'unit'     => $unit,
            'siblings' => $siblings,
        ])->render();

        $this->write("$folder/index.html", $html);

        // 2) Copy per-unit runtime + css
        $this->write("$folder/unit-runtime.js", view('export.partials.runtime')->render());
        $this->write("$folder/unit.css", view('export.partials.unitcss')->render());

        // 3) Collect and copy unit media into /media
        $mediaPaths = $this->collectUnitMedia($unit);
        foreach ($mediaPaths as $src => $destFilename) {
            $this->copyPublicToExports("media/{$destFilename}", $src, "$folder/media");
        }
        foreach ($unit->introMedia as $im) {
            $m = $im->media;
            if ($m) { $push($m); }
        }
    }

    protected function write(string $path, string $contents): void
    {
        Storage::disk($this->disk)->put($path, $contents);
    }

    protected function copyPublicToExports(string $filename, string $publicPath, string $destFolder): void
    {
        $full = public_path('storage/'.$publicPath);
        if (!is_file($full)) return;
        $data = file_get_contents($full);
        Storage::disk($this->disk)->put("{$destFolder}/{$filename}", $data);
    }

    // Ensure ONLY unit-used files are copied to its own /media
    protected function collectUnitMedia(Unit $unit): array
    {
        $map = [];

        $push = function ($media) use (&$map) {
            if (!$media) return;
            $src = $media->path; // relative to public/storage
            $name = basename($src);
            if (!isset($map[$src])) $map[$src] = $name;
        };

        $push($unit->heroMedia);
        $push($unit->audioMedia);
        $push($unit->videoMedia);

        foreach ($unit->accordions as $acc) {
            if ($acc->iconMedia) $push($acc->iconMedia);
            foreach ($acc->blocks as $b) {
                if (in_array($b->type, ['image','audio','video'])) {
                    $m = optional((object)$b->payload)->media_id ? \App\Models\MediaLibrary::find($b->payload['media_id']) : null;
                    $push($m);
                }
                if ($b->type === 'gallery') {
                    foreach (($b->payload['media_ids'] ?? []) as $mid) {
                        $m = \App\Models\MediaLibrary::find($mid);
                        $push($m);
                    }
                }
            }
        }

        return $map; // [ 'uploads/xyz.jpg' => 'xyz.jpg', ... ]
    }
}
