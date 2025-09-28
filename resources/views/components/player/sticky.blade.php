<div id="sticky-player"
     data-player-expanded="false"
     class="hidden opacity-0 translate-y-2 transition-all duration-200
            fixed inset-x-0 bottom-0 z-50 border-t border-border/60
            bg-card/85 backdrop-blur-md supports-[backdrop-filter]:bg-card/80
            text-foreground">
  <div class="mx-auto max-w-screen-xl px-3 sm:px-6 py-2 md:py-3">
    

    <div class="player-layout flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div class="player-controls-primary flex flex-wrap items-center gap-2 md:gap-3">
        <button id="pl-prev" class="inline-flex items-center justify-center rounded-full border border-border bg-card/80 text-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Pista anterior">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zM4.5 12l10 6V6z"/></svg>
        </button>
        <button id="pl-back-10" class="inline-flex items-center justify-center rounded-full border border-border bg-card/80 text-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Retroceder diez segundos">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M11 6V4l-4 4 4 4V9c2.76 0 5 2.24 5 5 0 1.42-.59 2.7-1.53 3.6l1.42 1.42C17.18 17.74 18 15.95 18 14c0-3.87-3.13-7-7-7z"/></svg>
        </button>
        <button id="pl-play" class="inline-flex items-center justify-center rounded-full border border-success/60 bg-success text-success-foreground shadow-sm transition hover:bg-success-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Reproducir">
          <svg id="pl-play-icon"  class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
          <svg id="pl-pause-icon" class="h-6 w-6 hidden" viewBox="0 0 24 24" fill="currentColor"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
        </button>
        <button id="pl-forward-10" class="inline-flex items-center justify-center rounded-full border border-border bg-card/80 text-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Avanzar diez segundos">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 6V4l4 4-4 4V9c-2.76 0-5 2.24-5 5 0 1.42.59 2.7 1.53 3.6l-1.42 1.42C6.82 17.74 6 15.95 6 14c0-3.87 3.13-7 7-7z"/></svg>
        </button>
        <button id="pl-next"  class="inline-flex items-center justify-center rounded-full border border-border bg-card/80 text-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Pista siguiente">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M16 6h2v12h-2zM14.5 12l-10 6V6z"/></svg>
        </button>
      </div>

      <div class="player-meta min-w-0 flex-1 md:max-w-xl">
        <div class="player-title-marquee" aria-live="polite">
          <span id="pl-title" data-marquee="-">-</span>
        </div>
        <div class="truncate text-sm opacity-70" id="pl-author" aria-live="polite">-</div>
      </div>

      <div class="player-progress flex flex-1 items-center gap-3 md:max-w-lg">
        <span id="pl-current"  class="hidden text-xs tabular-nums text-right">0:00</span>
        <input id="pl-seek" type="range" min="0" value="0" step="1"
               class="flex-1 appearance-none h-2 rounded-full bg-muted/50 outline-none accent-primary"
               aria-label="Barra de progreso">
        <span id="pl-duration" class="hidden text-xs tabular-nums">0:00</span>
      </div>

      <div class="player-advanced flex items-center gap-3 md:justify-end">
        <button id="pl-volume-toggle" class="inline-flex items-center justify-center rounded-full border border-border bg-card/80 text-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Silenciar">
          <i data-lucide="volume-2" class="h-5 w-5"></i>
        </button>
        <input id="pl-volume" type="range" min="0" max="1" step="0.01" value="1"
               class="w-24 appearance-none h-2 rounded-full bg-muted/50 accent-primary"
               aria-label="Volumen">
        <select id="pl-rate" class="hidden rounded-md border bg-card/70 px-2 py-1 text-sm"
                aria-label="Velocidad de reproducción">
          <option>0.75x</option><option selected>1x</option><option>1.25x</option><option>1.5x</option><option>1.75x</option><option>2x</option>
        </select>
        <a id="pl-download" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border bg-card/80 text-foreground transition hover:bg-muted focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Descargar" download>
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M5 20h14v-2H5v2zM12 3v10l4-4h-3V3h-2v6H8l4 4z"/>
          </svg>
        </a>
      </div>
    </div>

    <div class="player-extra flex flex-col gap-1 md:flex">
      <div class="player-extra-item" id="pl-category-wrapper" hidden>
        <span id="pl-category"></span>
      </div>
      <div class="player-extra-item" id="pl-series-wrapper" hidden>
        <span id="pl-series"></span>
      </div>
      <div class="player-extra-item" id="pl-date-wrapper" hidden>
        <span id="pl-date"></span>
      </div>
      <div class="player-extra-item" id="pl-citation-wrapper" hidden>
        <span id="pl-citation"></span>
      </div>
    </div>
  </div>

  <audio id="pl-audio" preload="metadata" crossorigin="anonymous"></audio>
</div>


