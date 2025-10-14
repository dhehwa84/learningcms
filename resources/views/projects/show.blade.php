@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center">
  <div>
    <h1 class="h4 mb-1">{{ $project->name }}</h1>
    <div class="text-muted small">Grade {{ $project->grade ?? '—' }} · {{ $project->theme ?? '—' }}</div>
  </div>
  <div>
    <a href="{{ route('projects.edit',$project) }}" class="btn btn-outline-secondary">Edit</a>
    <form method="POST" action="{{ route('export.project',$project) }}" class="d-inline">@csrf
      <button class="btn btn-success">Export Project</button>
    </form>
  </div>
</div>

<hr>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h2 class="h5 mb-0">Sections</h2>
  <a class="btn btn-primary" href="{{ route('projects.sections.create',$project) }}">+ New Section</a>
</div>

<div class="list-group">
@foreach($project->sections as $s)
  <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
     href="{{ route('projects.sections.show',[$project,$s]) }}">
    <span>{{ $s->title }}</span>
    <span class="badge bg-secondary">{{ $s->units->count() }} units</span>
  </a>
@endforeach
</div>
@endsection
