@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Sections — {{ $project->name }}</h1>
  <a class="btn btn-primary" href="{{ route('projects.sections.create',$project) }}">+ New Section</a>
</div>

<ul class="list-group">
@foreach($sections as $s)
  <li class="list-group-item d-flex justify-content-between align-items-center">
    <a href="{{ route('projects.sections.show',[$project,$s]) }}">{{ $s->title }}</a>
    <span class="badge bg-secondary">{{ $s->units_count }} units</span>
  </li>
@endforeach
</ul>
@endsection
