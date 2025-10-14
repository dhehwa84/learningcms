<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Unit;
use App\Services\UnitExporter;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportProject(Request $request, Project $project)
    {
        $exporter = new UnitExporter();
        $count = $exporter->exportProject($project);
        return back()->with('ok', "Exported {$count} units.");
    }

    public function exportUnit(Request $request, Unit $unit)
    {
        $exporter = new UnitExporter();
        $exporter->exportUnit($unit);
        return back()->with('ok', "Unit #{$unit->number} exported.");
    }
}
