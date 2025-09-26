@props([
  'src' => '#',
  'title' => '',
  'author' => '',
  'download' => null,
  'index' => 0,
  'category' => '',
  'series' => '',
  'date' => '',
  'year' => '',
  'yearLink' => null,
  'citation' => '',
])
<button
  type="button"
  class="btn-play inline-flex h-11 w-11 items-center justify-center rounded-full
         border border-success/60 bg-card text-foreground shadow-sm transition
         hover:bg-success/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
  data-audio-src="{{ $src }}"
  data-title="{{ $title }}"
  data-author="{{ $author }}"
  @if($download) data-download="{{ $download }}" @endif
  data-index="{{ $index }}"
  @if($category) data-category="{{ $category }}" @endif
  @if($series) data-series="{{ $series }}" @endif
  @if($date) data-date="{{ $date }}" @endif
  @if($year) data-year="{{ $year }}" @endif
  @if($yearLink) data-year-link="{{ $yearLink }}" @endif
  @if($citation) data-citation="{{ $citation }}" @endif
  aria-label="Reproducir {{ $title ?: 'audio' }}"
>
  <svg class="icon-play h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
  <span class="icon-eq hidden flex items-end gap-[2px]">
    <span class="eqbar h-3 w-[3px] bg-[currentColor] rounded animate-[eq_1s_ease-in-out_infinite]"></span>
    <span class="eqbar h-4 w-[3px] bg-[currentColor] rounded animate-[eq_1s_ease-in-out_infinite_200ms]"></span>
    <span class="eqbar h-2 w-[3px] bg-[currentColor] rounded animate-[eq_1s_ease-in-out_infinite_400ms]"></span>
  </span>
</button>
