<?php

namespace App\Http\Controllers;

use App\Models\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $q = MediaLibrary::query()->latest();
        if ($request->filled('type')) $q->where('type', $request->input('type'));
        if ($request->filled('tag'))  $q->whereJsonContains('tags', $request->input('tag'));
        $media = $q->paginate(30);
        return view('media.index', compact('media'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|max:20480',
            'type' => 'required|string|in:image,audio,video,icon,bg,doc',
            'title'=> 'nullable|string|max:255',
            'tags' => 'nullable|array'
        ]);
        $path = $request->file('file')->store('media', 'public');
        $media = MediaLibrary::create([
            'project_id' => null,
            'path' => $path,
            'type' => $data['type'],
            'title'=> $data['title'] ?? null,
            'tags' => $data['tags'] ?? [],
        ]);
        return back()->with('ok', 'Uploaded')->with('media_id', $media->id);
    }
}
