<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $project->name }} — Sections</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>body{font-family:system-ui,arial;padding:20px} .grid{display:grid;gap:12px;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));} a.card{display:block;padding:16px;border:1px solid #ddd;border-radius:10px;text-decoration:none;color:#111;background:#fafafa} .meta{color:#666;font-size:.9rem}</style>
</head>
<body>
  <h1>{{ $project->name }}</h1>
  <p class="meta">Grade {{ $project->grade }} • Theme: {{ $project->theme }}</p>

  <div class="grid">
    @foreach($project->sections()->orderBy('sort_order')->get() as $s)
      <a class="card" href="{{ $s->slug }}/index.html">
        <strong>{{ $s->title }}</strong>
        <div class="meta">{{ $s->units()->count() }} units</div>
      </a>
    @endforeach
  </div>
</body>
</html>
