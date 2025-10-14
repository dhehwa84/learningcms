@extends('layouts.app')
@section('content')
<h1 class="h4 mb-3">Edit Section — {{ $project->name }}</h1>
<form method="POST" action="{{ route('projects.sections.update',[$project,$section]) }}" class="card card-body">
@csrf @method('PUT')
<div class="row g-3">
  <div class="col-md-8">
    <label class="form-label">Title</label>
    <input name="title" class="form-control" value="{{ old('title',$section->title) }}" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Sort Order</label>
    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order',$section->sort_order) }}" min="0">
  </div>
  <div class="col-md-6">
    <label class="form-label">Menu Icon Media ID</label>
    <input type="number" name="menu_icon_media_id" class="form-control" value="{{ old('menu_icon_media_id',$section->menu_icon_media_id) }}">
  </div>
</div>
<div class="mt-3 d-flex gap-2">
  <button class="btn btn-primary">Save</button>
  <a class="btn btn-link" href="{{ route('projects.sections.show',[$project,$section]) }}">Cancel</a>
  <form method="POST" action="{{ route('projects.sections.destroy',[$project,$section]) }}" onsubmit="return confirm('Delete section?')" class="ms-auto">@csrf @method('DELETE')
    <button class="btn btn-outline-danger">Delete</button>
  </form>
</div>
</form>
@endsection
