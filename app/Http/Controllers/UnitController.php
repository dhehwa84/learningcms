<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Models\UnitIntroMedia;

class UnitController extends Controller
{
    public function index(Section $section)
    {
        $units = $section->units()->orderBy('number')->get();
        return view('units.index', compact('section','units'));
    }

    public function create(Section $section)
    {
        return view('units.create', compact('section'));
    }

    public function store(Request $request, Section $section)
    {
        $data = $request->validate([
            'number' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'overview' => 'nullable|string',
            'level' => 'nullable|string|max:50',
            'theme' => 'nullable|string|max:100',
            'is_published' => 'sometimes|boolean',
            'intro_media_ids' => 'nullable|string',
            'intro_media_captions' => 'nullable|string',
        ]);

        // drop old single-media fields (hero/audio/video) – they’re now optional:
        unset($data['hero_media_id'],$data['audio_media_id'],$data['video_media_id']);

        $unit = $section->units()->create($data);

        // Build intro gallery (optional)
        $ids = collect(preg_split('/[,\s]+/', (string)$request->input('intro_media_ids'), -1, PREG_SPLIT_NO_EMPTY))
                ->filter()->values();
        $captions = preg_split("/\r\n|\n|\r/", (string)$request->input('intro_media_captions'));
        foreach ($ids as $i => $id) {
            UnitIntroMedia::create([
                'unit_id' => $unit->id,
                'media_id' => (int)$id,
                'sort_order' => $i,
                'caption' => $captions[$i] ?? null,
            ]);
        }

        return redirect()->route('sections.units.show', [$section, $unit])->with('ok','Unit created.');
    }


    public function show(Section $section, Unit $unit)
    {
        $unit->load('accordions.blocks'); // editor to be built next
        $siblings = $section->units()->select('id','number')->orderBy('number')->get();
        return view('units.show', compact('section','unit','siblings'));
    }

    public function edit(Section $section, Unit $unit)
    {
        return view('units.edit', compact('section','unit'));
    }

    public function update(Request $request, Section $section, Unit $unit)
    {
        $data = $request->validate([
            'number' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'overview' => 'nullable|string',
            'level' => 'nullable|string|max:50',
            'theme' => 'nullable|string|max:100',
            'hero_media_id' => 'nullable|exists:media_library,id',
            'audio_media_id' => 'nullable|exists:media_library,id',
            'video_media_id' => 'nullable|exists:media_library,id',
            'is_published' => 'sometimes|boolean'
        ]);
        $unit->update($data);
        return back()->with('ok','Unit updated.');
    }

    public function destroy(Section $section, Unit $unit)
    {
        $unit->delete();
        return redirect()->route('projects.sections.show', [$section->project, $section])->with('ok','Unit deleted.');
    }
}
