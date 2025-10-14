@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Projects</h1>
  <a class="btn btn-primary" href="{{ route('projects.create') }}">+ New Project</a>
</div>

<div class="row g-3">
@foreach($projects as $p)
  <div class="col-md-6 col-lg-4">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title mb-1">
          <a href="{{ route('projects.show',$p) }}">{{ $p->name }}</a>
        </h5>
        <div class="text-muted small mb-2">Grade {{ $p->grade ?? '—' }} · {{ $p->theme ?? '—' }}</div>
        <div class="small text-muted">{{ $p->sections_count }} sections</div>
      </div>
      <div class="card-footer bg-transparent">
        <form class="d-inline" method="POST" action="{{ route('export.project',$p) }}">@csrf
          <button class="btn btn-sm btn-outline-success">Export Project</button>
        </form>
      </div>
    </div>
  </div>
@endforeach
</div>

<div class="mt-3">{{ $projects->links() }}</div>
@endsection
