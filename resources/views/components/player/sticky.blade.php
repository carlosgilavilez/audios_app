<div id="sticky-player"
     class="hidden opacity-0 translate-y-2 transition-all duration-200
            fixed inset-x-0 bottom-0 z-50 border-t border-black/10 dark:border-white/10
            bg-white/75 dark:bg-gray-900/75 backdrop-blur-md
            text-gray-800 dark:text-gray-100">
  <div class="mx-auto max-w-screen-xl px-4 sm:px-6 py-3">
    <div class="flex items-center gap-4">
      {{-- Controles --}}
      <div class="flex items-center gap-2">
        <button id="pl-prev"  class="p-2 rounded-full hover:bg-black/5 dark:hover:bg-white/10" aria-label="Anterior">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zM14.5 12l-10 6V6z"/></svg>
        </button>

        <button id="pl-play" class="h-10 w-10 rounded-full bg-green-600 hover:bg-green-700
                                    text-white grid place-items-center shadow-md"
                aria-label="Reproducir">
          <svg id="pl-play-icon"  class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
          <svg id="pl-pause-icon" class="h-6 w-6 hidden" viewBox="0 0 24 24" fill="currentColor"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
        </button>

        <button id="pl-next"  class="p-2 rounded-full hover:bg-black/5 dark:hover:bg-white/10" aria-label="Siguiente">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M16 6h2v12h-2zM4.5 12l10 6V6z"/></svg>
        </button>
      </div>

      {{-- Meta --}}
      <div class="min-w-0 flex-1">
        <div class="truncate font-medium" id="pl-title">—</div>
        <div class="truncate text-sm opacity-70" id="pl-author">—</div>
      </div>

      {{-- Seek --}}
      <div class="hidden md:flex items-center gap-3 w-[42%]">
        <span id="pl-current"  class="w-10 text-xs tabular-nums text-right">0:00</span>
        <input id="pl-seek" type="range" min="0" value="0" step="1"
               class="flex-1 appearance-none h-2 rounded-full bg-gray-300/50 dark:bg-white/20
                      outline-none accent-green-600"
               aria-label="Barra de progreso">
        <span id="pl-duration" class="w-12 text-xs tabular-nums">0:00</span>
      </div>

      {{-- Volumen / velocidad / descarga --}}
      <div class="flex items-center gap-3">
        <input id="pl-volume" type="range" min="0" max="1" step="0.01" value="1"
               class="w-24 appearance-none h-2 rounded-full bg-gray-300/50 dark:bg-white/20 accent-green-600"
               aria-label="Volumen">
        <select id="pl-rate" class="rounded-md border bg-white/70 dark:bg-gray-800/70 px-2 py-1 text-sm">
          <option>0.75x</option><option selected>1x</option><option>1.25x</option><option>1.5x</option><option>1.75x</option><option>2x</option>
        </select>
        {{-- DESCARGA FLECHA ABAJO --}}
        <a id="pl-download" class="p-2 rounded-md hover:bg-black/5 dark:hover:bg-white/10" aria-label="Descargar">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M5 20h14v-2H5v2zM12 3v10l4-4h-3V3h-2v6H8l4 4z"/> {{-- flecha hacia abajo --}}
          </svg>
        </a>
      </div>
    </div>
  </div>

  <audio id="pl-audio" preload="metadata" crossorigin="anonymous"></audio>
</div>
