<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $section->title }} — Units</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,arial;padding:20px}
    .nums{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}
    .nums a{display:inline-block;padding:10px 14px;border:1px solid #ddd;border-radius:8px;text-decoration:none;color:#111;background:#fff}
    .back{margin-bottom:10px;display:inline-block}
  </style>
</head>
<body>
  <a class="back" href="../index.html">← Back to project</a>
  <h1>{{ $section->title }}</h1>

  <div class="nums">
    @foreach($section->units()->orderBy('number')->get() as $u)
      <a href="{{ $u->number }}/index.html">{{ $u->number }}</a>
    @endforeach
  </div>
</body>
</html>
