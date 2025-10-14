<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Unit;
use App\Services\UnitExporter;

class ExportProject extends Command
{
    protected $signature = 'cms:export {--project=} {--unit=}';
    protected $description = 'Export a whole project or a single unit into offline static folders';

    public function handle()
    {
        $exporter = new UnitExporter();

        if ($unitId = $this->option('unit')) {
            $unit = Unit::findOrFail($unitId);
            $exporter->exportUnit($unit);
            $this->info("Exported unit #{$unit->number}");
            return self::SUCCESS;
        }

        $projectId = $this->option('project');
        $project = Project::findOrFail($projectId);
        $count = $exporter->exportProject($project);
        $this->info("Exported {$count} units for project {$project->name}");
        return self::SUCCESS;
    }
}
