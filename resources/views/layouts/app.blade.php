<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Learning CMS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @vite(['resources/js/app.js'])
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="{{ route('home') }}">LearningCMS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Projects</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('media.index') }}">Media</a></li>
      </ul>
      <form method="POST" action="{{ route('logout') }}" class="d-flex">@csrf
        <button class="btn btn-outline-light btn-sm">Logout</button>
      </form>
    </div>
  </div>
</nav>

<main class="container py-4">
  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  @yield('content')
</main>
</body>
</html>
