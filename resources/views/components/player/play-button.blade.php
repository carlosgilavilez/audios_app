@props([
  'src' => '#', 'title' => '', 'author' => '', 'download' => null, 'index' => 0
])
<button
  class="btn-play h-9 w-9 rounded-full bg-card border border-success/40
         text-success grid place-items-center shadow-md transition
         hover:shadow-lg hover:bg-success/5
         focus:outline-none focus:ring-2 focus:ring-success"
  data-audio-src="{{ $src }}"
  data-title="{{ $title }}"
  data-author="{{ $author }}"
  data-download="{{ $download ?? $src }}"
  data-index="{{ $index }}"
  aria-label="Reproducir {{ $title }}"
>
  {{-- Ícono play (triángulo) usa el color de texto: verde en reposo, BLANCO cuando está activo --}}
  <svg class="icon-play h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>

  {{-- Barras animadas (se muestran SOLO al reproducir) --}}
  <span class="icon-eq hidden flex items-end gap-[2px]">
    <span class="eqbar h-3 w-[3px] bg-[currentColor] rounded animate-[eq_1s_ease-in-out_infinite]"></span>
    <span class="eqbar h-4 w-[3px] bg-[currentColor] rounded animate-[eq_1s_ease-in-out_infinite_200ms]"></span>
    <span class="eqbar h-2 w-[3px] bg-[currentColor] rounded animate-[eq_1s_ease-in-out_infinite_400ms]"></span>
  </span>
</button>
