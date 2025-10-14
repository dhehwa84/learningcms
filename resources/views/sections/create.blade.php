@extends('layouts.app')
@section('content')
<h1 class="h4 mb-3">New Section — {{ $project->name }}</h1>
<form method="POST" action="{{ route('projects.sections.store',$project) }}" class="card card-body">
@csrf
<div class="row g-3">
  <div class="col-md-8">
    <label class="form-label">Title</label>
    <input name="title" class="form-control" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Sort Order</label>
    <input type="number" name="sort_order" class="form-control" value="0" min="0">
  </div>
  <div class="col-md-6">
    <label class="form-label">Menu Icon Media ID (optional)</label>
    <input type="number" name="menu_icon_media_id" class="form-control" placeholder="e.g. 12">
    <div class="form-text">We’ll add a picker later.</div>
  </div>
</div>
<div class="mt-3">
  <button class="btn btn-primary">Create Section</button>
  <a class="btn btn-link" href="{{ route('projects.show',$project) }}">Cancel</a>
</div>
</form>
@endsection
