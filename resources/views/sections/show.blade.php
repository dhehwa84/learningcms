@extends('layouts.app')
@section('content')
<a class="btn btn-link p-0 mb-2" href="{{ route('projects.show',$project) }}">← Back to Project</a>

<div class="d-flex justify-content-between align-items-center">
  <h1 class="h4 mb-0">{{ $section->title }}</h1>
  <div>
    <a class="btn btn-outline-secondary" href="{{ route('projects.sections.edit',[$project,$section]) }}">Edit</a>
    <a class="btn btn-primary" href="{{ route('sections.units.create',$section) }}">+ New Unit</a>
  </div>
</div>

<hr>

<h2 class="h6">Units</h2>
<div class="d-flex flex-wrap gap-2">
@foreach($section->units as $u)
  <div class="card" style="width: 11rem;">
    <div class="card-body">
      <div class="text-muted small">Unit #{{ $u->number }}</div>
      <strong class="d-block mb-2">{{ $u->title }}</strong>
      <a class="btn btn-sm btn-outline-primary" href="{{ route('sections.units.show',[$section,$u]) }}">Open</a>
      <form method="POST" action="{{ route('export.unit',$u) }}" class="d-inline">@csrf
        <button class="btn btn-sm btn-success">Export</button>
      </form>
    </div>
  </div>
@endforeach
</div>
@endsection
