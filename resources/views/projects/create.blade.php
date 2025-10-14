@extends('layouts.app')
@section('content')
<h1 class="h4 mb-3">New Project</h1>
<form method="POST" action="{{ route('projects.store') }}" class="card card-body">
@csrf
<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Name</label>
    <input name="name" class="form-control" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Grade</label>
    <input name="grade" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">Theme</label>
    <input name="theme" class="form-control">
  </div>
  <div class="col-md-6">
    <label class="form-label">School</label>
    <input name="school_name" class="form-control">
  </div>
  <div class="col-md-6">
    <label class="form-label">Webhook URL</label>
    <input name="webhook_url" class="form-control" placeholder="https://example.com/submit">
  </div>
</div>
<div class="mt-3">
  <button class="btn btn-primary">Create</button>
  <a class="btn btn-link" href="{{ route('home') }}">Cancel</a>
</div>
</form>
@endsection
