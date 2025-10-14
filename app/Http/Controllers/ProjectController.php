<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::withCount('sections')->latest()->paginate(20);
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'nullable|string|max:50',
            'theme' => 'nullable|string|max:100',
            'school_name' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|url'
        ]);

        $data['slug'] = Str::slug($data['name']).'-'.Str::random(6);
        $data['created_by'] = $request->user()->id;

        $project = Project::create($data);
        return redirect()->route('projects.show', $project)->with('ok', 'Project created.');
    }

    public function show(Project $project)
    {
        $project->load(['sections.units' => fn($q)=>$q->orderBy('number')]);
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'nullable|string|max:50',
            'theme' => 'nullable|string|max:100',
            'school_name' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|url'
        ]);
        $project->update($data);
        return back()->with('ok', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('ok', 'Project deleted.');
    }
}
