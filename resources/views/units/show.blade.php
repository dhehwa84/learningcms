@extends('layouts.app')
@section('content')
<a class="btn btn-link p-0 mb-2" href="{{ route('projects.sections.show',[$section->project,$section]) }}">← Back to Section</a>

<div class="d-flex justify-content-between align-items-center">
  <h1 class="h4 mb-0">Unit #{{ $unit->number }} — {{ $unit->title }}</h1>
  <form method="POST" action="{{ route('export.unit',$unit) }}">@csrf
    <button class="btn btn-success">Export Unit</button>
  </form>
</div>

<hr>

<div class="row g-3">
  <div class="col-md-8">
    <div class="card card-body">
      <h6 class="text-muted">Overview / Objectives</h6>
      <div>{!! $unit->overview !!}</div>
    </div>

    <div class="card card-body mt-3">
      <h6 class="text-muted">Accordions & Blocks</h6>
      <p class="mb-0">Editor coming next. For now this is a placeholder.</p>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-body">
      <h6 class="text-muted">Media</h6>
      @if($unit->audioMedia)
        <audio controls class="w-100" src="{{ asset('storage/'.$unit->audioMedia->path) }}"></audio>
      @elseif($unit->videoMedia)
        <video controls class="w-100" src="{{ asset('storage/'.$unit->videoMedia->path) }}"></video>
      @elseif($unit->heroMedia)
        <img class="img-fluid rounded" src="{{ asset('storage/'.$unit->heroMedia->path) }}">
      @else
        <div class="text-muted">No media set.</div>
      @endif
    </div>
  </div>
</div>
@endsection
