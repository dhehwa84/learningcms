<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SectionController extends Controller
{
    public function index(Project $project)
    {
        $sections = $project->sections()->withCount('units')->orderBy('sort_order')->get();
        return view('sections.index', compact('project','sections'));
    }

    public function create(Project $project)
    {
        return view('sections.create', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'menu_icon_media_id' => 'nullable|exists:media_library,id'
        ]);
        $data['slug'] = Str::slug($data['title']).'-'.Str::random(6);
        $section = $project->sections()->create($data);
        return redirect()->route('projects.sections.show', [$project, $section])->with('ok','Section created.');
    }

    public function show(Project $project, Section $section)
    {
        $section->load(['units' => fn($q)=>$q->orderBy('number')]);
        return view('sections.show', compact('project','section'));
    }

    public function edit(Project $project, Section $section)
    {
        return view('sections.edit', compact('project','section'));
    }

    public function update(Request $request, Project $project, Section $section)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'menu_icon_media_id' => 'nullable|exists:media_library,id'
        ]);
        $section->update($data);
        return back()->with('ok','Section updated.');
    }

    public function destroy(Project $project, Section $section)
    {
        $section->delete();
        return redirect()->route('projects.show', $project)->with('ok','Section deleted.');
    }
}
