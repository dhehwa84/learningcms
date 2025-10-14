{{-- resources/views/export/unit.blade.php --}}
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $unit->title }}</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="unit.css">
  <script>
    window.__UNIT_DATA__ = @json([
      'project' => [
        'id'=>$unit->section->project->id,
        'name'=>$unit->section->project->name,
        'grade'=>$unit->section->project->grade,
        'theme'=>$unit->section->project->theme,
        'webhook_url'=>$unit->section->project->webhook_url,
      ],
      'section' => ['id'=>$unit->section->id,'title'=>$unit->section->title,'slug'=>$unit->section->slug],
      'unit'    => ['id'=>$unit->id,'number'=>$unit->number,'title'=>$unit->title],
      'labels'  => ['mark'=>'✓'] // no hardcoded English words in UI
    ]);
  </script>
  <script defer src="unit-runtime.js"></script>
</head>
<body>
  <header class="topbar">
    <a href="../../index.html" title="Home">🏠</a>
    <a href="../index.html" title="Section">📚</a>
    <nav class="numbers">
      @foreach($siblings as $sib)
        <a class="{{ $sib->id===$unit->id ? 'active':'' }}" href="../{{ $sib->number }}/index.html">{{ $sib->number }}</a>
      @endforeach
    </nav>
  </header>

  <h1>{{ $unit->title }}</h1>
  @if($unit->subtitle)<p class="subtitle">{{ $unit->subtitle }}</p>@endif

  {{-- Intro: left = your authored HTML, right = 0..n media items --}}
  <section class="intro">
    <aside class="left">
      {!! $unit->overview !!} {{-- author controls language & headings --}}
    </aside>

    @php($intro = $unit->introMedia)
    @if($intro->count())
      <aside class="right">
        @foreach($intro as $im)
          @php($m = $im->media)
          @if($m && $m->type === 'image')
            <figure class="mb-2">
              <img src="media/{{ basename($m->path) }}" alt="">
              @if($im->caption)<figcaption>{{ $im->caption }}</figcaption>@endif
            </figure>
          @elseif($m && $m->type === 'audio')
            <figure class="mb-2">
              <audio controls src="media/{{ basename($m->path) }}"></audio>
              @if($im->caption)<figcaption>{{ $im->caption }}</figcaption>@endif
            </figure>
          @elseif($m && $m->type === 'video')
            <figure class="mb-2">
              <video controls src="media/{{ basename($m->path) }}"></video>
              @if($im->caption)<figcaption>{{ $im->caption }}</figcaption>@endif
            </figure>
          @endif
        @endforeach
      </aside>
    @endif
  </section>

  {{-- Accordions & exercises will render here later --}}
  <section id="accordions">
    <details open><summary>—</summary>
      <div class="exercise">
        <button class="btn-mark" aria-label="Mark">{{ '✓' }}</button>
        <span class="score"></span>
      </div>
    </details>
  </section>

  <footer><small>{{ $unit->section->project->school_name }}</small></footer>
</body>
</html>
