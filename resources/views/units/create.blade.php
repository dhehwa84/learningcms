@extends('layouts.app')
@section('content')
<h1 class="h4 mb-3">New Unit — {{ $section->title }}</h1>
<form method="POST" action="{{ route('sections.units.store',$section) }}" class="card card-body">
@csrf
<div class="row g-3">
  <div class="col-md-2">
    <label class="form-label">Number</label>
    <input type="number" name="number" class="form-control" min="1" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Title</label>
    <input name="title" class="form-control" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Subtitle</label>
    <input name="subtitle" class="form-control">
  </div>
  <div class="col-12">
    <label class="form-label">Overview / Objectives (HTML allowed for now)</label>
    <textarea name="overview" class="form-control" rows="4"></textarea>
  </div>
  <div class="col-md-3">
    <label class="form-label">Hero Media ID</label>
    <input type="number" name="hero_media_id" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">Audio Media ID</label>
    <input type="number" name="audio_media_id" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">Video Media ID</label>
    <input type="number" name="video_media_id" class="form-control">
  </div>
  <div class="col-md-3 d-flex align-items-end">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published">
      <label class="form-check-label" for="is_published">Published</label>
    </div>
  </div>

  <div class="col-12">
  <label class="form-label">Intro Media IDs (comma-separated, optional)</label>
  <input name="intro_media_ids" class="form-control" placeholder="e.g. 12,7,18">
  <div class="form-text">You can mix images, audio, or video. Order matters.</div>
</div>
<div class="col-12">
  <label class="form-label">Captions (optional, one per media line)</label>
  <textarea name="intro_media_captions" rows="2" class="form-control"
    placeholder="One caption per line, same order as IDs"></textarea>
</div>
</div>
<div class="mt-3">
  <button class="btn btn-primary">Create Unit</button>
  <a class="btn btn-link" href="{{ route('projects.sections.show',[$section->project,$section]) }}">Cancel</a>
</div>
</form>
@endsection
