@extends('layouts.dashboard')

@section('title', 'Audios')

@section('content')
<div class="space-y-6">
  @php
    $bulkUploadLimit = $bulkUploadLimit ?? 50;
    $isAdmin = auth()->user()->role === 'admin';
  @endphp
  @if (session('ok') || session('success') || session('error'))
    <div class="rounded-md p-3 text-sm {{ session('error') ? 'bg-destructive/10 text-destructive border border-destructive/40' : 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300 border border-green-300/50' }}">
      {{ session('ok') ?? session('success') ?? session('error') }}
    </div>
  @endif
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-foreground">Audios</h1>
    <a href="{{ route(auth()->user()->role . '.audios.create') }}"
       class="flex items-center gap-2 px-4 h-10 text-base font-medium rounded-xl bg-green-600 text-white hover:bg-green-700">
       <i data-lucide="upload" class="h-5 w-5"></i> Subir Audio
    </a>
  </div>

  @if(auth()->user()->role === 'admin')
  <div class="rounded-lg border border-dashed border-border/70 bg-muted/30 shadow-sm">
    <div class="p-6 space-y-4">
      <div class="space-y-3">
        <div>
          <h2 class="text-lg font-semibold text-foreground">Carga masiva de audios</h2>
          <p class="text-sm text-muted-foreground">Arrastra y suelta hasta {{ $bulkUploadLimit }} archivos o elige archivos manualmente. Se crearan en estado pendiente y se mostraran en la tabla al finalizar.</p>
        </div>
        <div class="flex items-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-4 py-2 text-sm text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-100">
          <i data-lucide="info" class="h-4 w-4"></i>
          <span>Solo puedes subir un maximo de {{ $bulkUploadLimit }} audios a la vez.</span>
        </div>
        @if ($bulkUploadLimit < 50)
        <div class="rounded-md border border-amber-200 bg-amber-100 px-3 py-2 text-xs text-amber-900 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-100">
          Este limite depende de la configuracion PHP (<code>max_file_uploads={{ ini_get('max_file_uploads') }}</code>). Aumentalo en tu <code>php.ini</code> si necesitas subir mas archivos simultaneamente.
        </div>
        @endif
      </div>
      <div id="bulk-upload-dropzone"
           class="flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-border/70 bg-background py-10 px-6 text-center cursor-pointer transition hover:border-blue-400 hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
           data-upload-url="{{ route('admin.audios.bulk-upload') }}"
           data-max-files="{{ $bulkUploadLimit }}">
        <input type="file" id="bulk-upload-input" class="hidden" multiple accept=".mp3,.wav,.aac" />
        <div class="flex flex-col items-center gap-2">
          <span class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/10 text-blue-500">
            <i data-lucide="files" class="h-6 w-6"></i>
          </span>
          <p class="text-base font-medium text-foreground">Arrastra tus audios aqui</p>
          <p class="text-xs text-muted-foreground">Formatos permitidos: MP3, WAV, AAC - Maximo 50 MB por archivo</p>
        </div>
        <button type="button" id="bulk-upload-browse" class="inline-flex items-center gap-2 rounded-lg border border-border bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-muted">
          <i data-lucide="folder-open" class="h-4 w-4"></i> Seleccionar desde el equipo
        </button>
      </div>
      <div id="bulk-upload-progress" class="hidden space-y-2">
        <div class="h-2 w-full rounded-full bg-muted overflow-hidden">
          <div id="bulk-upload-progress-bar" class="h-full w-0 bg-blue-500 transition-all duration-200"></div>
        </div>
        <p id="bulk-upload-progress-text" class="text-xs text-muted-foreground text-center"></p>
      </div>
      <div id="bulk-upload-feedback" class="hidden text-sm"></div>
      <ul id="bulk-upload-results" class="space-y-2 text-sm"></ul>
    </div>
  </div>
  @endif

  <div class="rounded-lg border bg-card text-card-foreground shadow-sm border-border/50">
    <div class="p-6 space-y-4">
      <form method="GET" action="{{ route(auth()->user()->role . '.audios.index') }}" class="flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por titulo, autor, serie o cita" class="w-72 max-w-full rounded-md border px-3 py-2 text-sm bg-background" />
        <select name="categoria_id" class="rounded-md border px-3 py-2 text-sm bg-background">
            <option value="">Todas las categorias</option>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>{{ $categoria->nombre }}</option>
            @endforeach
        </select>
        <select name="estado" class="rounded-md border px-3 py-2 text-sm bg-background">
          <option value="">Estado</option>
          <option value="Publicado" @selected(request('estado')==='Publicado')>Publicado</option>
          <option value="Pendiente" @selected(request('estado')==='Pendiente')>Pendiente</option>
        </select>
        <select name="year" class="rounded-md border px-3 py-2 text-sm bg-background">
            <option value="">Anio</option>
            @foreach($years as $year)
                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
            @endforeach
        </select>
        <select name="per_page" class="rounded-md border px-3 py-2 text-sm bg-background" onchange="this.form.submit()">
            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 por pagina</option>
            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 por pagina</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 por pagina</option>
            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 por pagina</option>
        </select>
        <button type="submit" class="inline-flex items-center gap-2 px-4 h-9 text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 dark:bg-blue-800 dark:hover:bg-blue-700">Filtrar</button>
        @if (request()->has('search') || request()->has('estado') || request()->has('categoria_id') || request()->has('year'))
          <a href="{{ route(auth()->user()->role . '.audios.index') }}" class="inline-flex items-center justify-center px-4 h-9 border border-border text-sm font-medium rounded-md shadow-sm text-muted-foreground bg-muted hover:bg-muted/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ring">Limpiar</a>
        @endif
      </form>
      <div class="flex flex-wrap items-center gap-3 justify-between text-sm" id="audio-results-bar" data-per-page="{{ $audios->perPage() }}">
        <span class="text-muted-foreground" id="audio-results-summary">
          Resultados {{ $audios->firstItem() ?? 0 }}-{{ $audios->lastItem() ?? 0 }} de {{ $audios->total() }} audios
        </span>
        @if ($isAdmin)
        <form id="audio-bulk-action-form" method="POST" action="{{ route('admin.audios.bulk-action') }}" class="flex items-center gap-2 text-sm" data-max-select="50">
          @csrf
          <label for="audio-bulk-action-select" class="sr-only">Acción masiva</label>
          <select id="audio-bulk-action-select" name="action" class="rounded-md border border-border bg-background px-3 py-2 text-sm" required>
            <option value="">Acción masiva</option>
            <option value="delete">Eliminar</option>
            <option value="pendiente">Pendiente</option>
            <option value="publicar">Publicar</option>
          </select>
          <button type="submit" class="inline-flex items-center gap-2 px-3 h-9 text-sm font-medium rounded-md bg-destructive text-destructive-foreground hover:bg-destructive/90" data-bulk-apply>
            Aplicar
          </button>
        </form>
        @endif
      </div>
      <div class="relative max-w-full min-w-0">
        <div id="audios-scroll-top"
             class="overflow-x-auto overflow-y-hidden custom-hscroll h-6 -mx-4 md:-mx-6"
             aria-hidden="true" style="display:none">
          <div id="audios-scroll-top-spacer" style="height:1px;width:0"></div>
        </div>

        <div class="-mx-4 md:-mx-6">
          <div id="audios-scroll-bottom" class="overflow-x-auto overflow-y-hidden custom-hscroll">
            <table id="audios-table" class="min-w-full w-full table-auto text-sm divide-y divide-border [&>tbody>tr>td]:px-2 md:[&>tbody>tr>td]:px-3">
              <thead class="bg-muted/50">
                <tr class="[&>th]:px-4 [&>th]:py-3 [&>th]:text-left [&>th]:whitespace-nowrap">
                  <th scope="col"></th> {{-- Play --}}
                  <th scope="col" class="w-[240px]">Nombre</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Categoria</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Serie</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Fecha</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Cita Biblica</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Turno</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Estado</th>
                  <th scope="col" class="px-4 py-3 text-left text-sm font-semibold text-muted-foreground uppercase tracking-wider">Duracion</th>
                  @if ($isAdmin)
                  <th scope="col" class="px-2 py-3 text-right">
                    <div class="flex items-center justify-end gap-1 text-xs font-semibold text-muted-foreground">
                      <input type="checkbox" id="audio-select-master" class="h-4 w-4 rounded border-border text-blue-600 focus:ring-blue-500" data-audio-select-master>
                      <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-border text-[10px] font-bold text-muted-foreground cursor-default" title="Puedes seleccionar hasta 50 audios para las acciones masivas (Eliminar, Pendiente o Publicar)." aria-label="Puedes seleccionar hasta 50 audios para las acciones masivas (Eliminar, Pendiente o Publicar).">i</span>
                    </div>
                  </th>
                  @endif
                  <th scope="col" class="relative px-4 py-3">
                    <span class="sr-only">Acciones</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-card divide-y divide-border">
              @foreach ($audios as $audio)
                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm">
                    <x-player.play-button
                      :src="route('public.audios.play', $audio)"
                      :title="$audio->titulo ?? 'Unknown Title'"
                      :author="$audio->autor?->nombre ?? 'Unknown Artist'"
                      :download="route('public.download_audio', $audio)"
                      :index="$loop->index"
                    />
                  </td>
                  <td class="px-2 md:px-3 py-3 text-sm font-medium w-[240px] leading-tight">
                    @php
                      $words = preg_split('/\s+/', trim($audio->titulo ?? ''));
                      $chunks = array_chunk($words, 3);
                    @endphp
                    @foreach($chunks as $i => $chunk)
                      <span class="block {{ $i>0 ? 'opacity-90' : '' }}">{{ implode(' ', $chunk) }}</span>
                    @endforeach
                    <div class="text-xs text-muted-foreground">{{ $audio->autor?->nombre ?? '' }}</div>
                  </td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm">
                    <x-ui.cat-badge :name="$audio->categoria?->nombre ?? ''" />
                  </td>
                  <td class="px-2 md:px-3 py-3 text-sm whitespace-normal break-words" style="max-width: 220px;">{{ $audio->serie?->nombre ?? '' }}</td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm">{{ $audio->fecha_publicacion ? \Carbon\Carbon::parse($audio->fecha_publicacion)->format('d/m/Y') : '' }}</td>
                  <td class="px-2 md:px-3 py-3 text-sm whitespace-normal break-words" style="max-width: 260px;">{{ trim(($audio->libro?->nombre ?? '') . ' ' . ($audio->cita_biblica ?? '')) }}</td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm">{{ $audio->turno?->nombre ?? '' }}</td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm">
                    @if(auth()->user()->role === 'admin')
                      <select
                        class="js-audio-status-select inline-flex appearance-none rounded-full border border-border px-3 py-1 text-xs font-semibold bg-background focus:outline-none focus:ring-2 focus:ring-blue-500"
                        data-update-url="{{ route('admin.audios.updateEstado', $audio) }}"
                        data-last-value="{{ $audio->estado }}">
                        <option value="Pendiente" @selected($audio->estado === 'Pendiente')>Pendiente</option>
                        <option value="Publicado" @selected($audio->estado === 'Publicado')>Publicado</option>
                      </select>
                    @else
                      @if ($audio->estado == 'Publicado')
                        <span class="px-2.5 py-0.5 rounded-full text-sm font-semibold bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-300">Publico</span>
                      @elseif ($audio->estado == 'Pendiente')
                        <span class="px-2.5 py-0.5 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">Pendiente</span>
                      @else
                        {{ $audio->estado }}
                      @endif
                    @endif
                  </td>
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-sm" style="width: 80px;">{{ $audio->duracion ?? '' }}</td>
                  @if ($isAdmin)
                  <td class="px-2 md:px-3 py-3 text-right">
                    <input
                      type="checkbox"
                      class="h-4 w-4 rounded border-border text-blue-600 focus:ring-blue-500"
                      value="{{ $audio->id }}"
                      name="audio_ids[]"
                      form="audio-bulk-action-form"
                      data-audio-select-item
                      aria-label="Seleccionar audio {{ $audio->titulo ?? ('#' . $audio->id) }}"
                    >
                  </td>
                  @endif
                  <td class="px-2 md:px-3 py-3 whitespace-nowrap text-left text-sm font-medium">
                    <a href="{{ route(auth()->user()->role . '.audios.edit', $audio) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-500 text-white hover:bg-blue-600 h-7 w-7" title="Editar">
                      <i data-lucide="pencil" class="h-4 w-4"></i>
                    </a>
                    <form action="{{ route(auth()->user()->role . '.audios.destroy', $audio) }}" method="POST" class="inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" onclick="return confirm('Estas seguro de que quieres eliminar este audio? Esta accion no se puede deshacer.');" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-7 w-7 ml-2">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    @if ($audios->hasPages())
        <div class="p-6 border-t border-border">
            {{ $audios->appends(request()->query())->links() }}
        </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
  window.audioPlaylist = [
    @foreach ($audios as $audio)
    {
      id: {{ $audio->id }},
      play_url: '{{ route('public.audios.play', $audio) }}',
      title: '{{ $audio->titulo ?? 'Unknown Title' }}',
      artist: '{{ $audio->autor?->nombre ?? 'Unknown Artist' }}',
      download_url: '{{ route('public.download_audio', $audio) }}'
    },
    @endforeach
  ];

  // Sincroniza barras superior e inferior y muestra la superior solo si hay overflow
  document.addEventListener('DOMContentLoaded', function () {
    const top    = document.getElementById('audios-scroll-top');
    const bottom = document.getElementById('audios-scroll-bottom');
    const table  = document.getElementById('audios-table');
    if (!top || !bottom || !table) return;

    const topFiller = top.querySelector('div');
    const sync = (from, to) => { to.scrollLeft = from.scrollLeft; };

    top.addEventListener('scroll', () => sync(top, bottom));
    bottom.addEventListener('scroll', () => sync(bottom, top));

    const update = () => {
      try {
        const w = table.scrollWidth || table.offsetWidth;
        if (topFiller) topFiller.style.width = w + 'px';
        const hasOverflow = w > bottom.clientWidth + 1;
        top.style.display = hasOverflow ? 'block' : 'none';
      } catch (e) {}
    };

    update();
    if (window.ResizeObserver) new ResizeObserver(update).observe(table);
    window.addEventListener('resize', update);
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.Player && typeof window.Player.bind === 'function') {
      window.Player.bind();
    }
  });
</script>
@if($isAdmin)
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('audio-bulk-action-form');
    if (!form) return;

    const items = Array.from(document.querySelectorAll('[data-audio-select-item]'));
    if (!items.length) return;

    const master = document.querySelector('[data-audio-select-master]');
    const actionSelect = document.getElementById('audio-bulk-action-select');
    const applyButton = form.querySelector('[data-bulk-apply]');
    const bar = document.getElementById('audio-results-bar');

    const maxSelect = parseInt(form.dataset.maxSelect || '50', 10) || 50;
    const perPage = parseInt(bar?.dataset.perPage || '25', 10) || 25;

    const getSelected = () => items.filter((checkbox) => checkbox.checked);
    const getLimit = () => Math.min(items.length, Math.min(perPage, maxSelect));

    const syncMaster = () => {
      if (!master) return;
      const count = getSelected().length;
      const limit = getLimit();
      if (count === 0) {
        master.checked = false;
        master.indeterminate = false;
        return;
      }
      if (count >= limit) {
        master.indeterminate = false;
        master.checked = true;
      } else {
        master.checked = false;
        master.indeterminate = true;
      }
    };

    const syncApplyButton = () => {
      if (!applyButton) return;
      const hasSelection = getSelected().length > 0;
      const hasAction = !!(actionSelect && actionSelect.value);
      applyButton.disabled = !(hasSelection && hasAction);
    };

    const updateUI = () => {
      syncMaster();
      syncApplyButton();
    };

    const limitMessage = `Solo puedes seleccionar hasta ${maxSelect} audios.`;

    master?.addEventListener('change', () => {
      if (!master.checked) {
        items.forEach((checkbox) => { checkbox.checked = false; });
        updateUI();
        return;
      }
      const limit = getLimit();
      let selected = 0;
      items.forEach((checkbox) => {
        const shouldCheck = selected < limit;
        checkbox.checked = shouldCheck;
        if (shouldCheck) {
          selected += 1;
        }
      });
      updateUI();
    });

    items.forEach((checkbox) => {
      checkbox.addEventListener('change', () => {
        const selectedCount = getSelected().length;
        if (selectedCount > maxSelect) {
          checkbox.checked = false;
          alert(limitMessage);
        }
        updateUI();
      });
    });

    actionSelect?.addEventListener('change', updateUI);

    form.addEventListener('submit', (event) => {
      const selectedCount = getSelected().length;
      if (selectedCount === 0) {
        event.preventDefault();
        alert('Selecciona al menos un audio.');
        return;
      }
      if (!actionSelect?.value) {
        event.preventDefault();
        alert('Selecciona una acción masiva.');
        return;
      }
      if (selectedCount > maxSelect) {
        event.preventDefault();
        alert(limitMessage);
      }
    });

    updateUI();
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const setFeedback = (feedbackEl, message, type = 'info') => {
      if (!feedbackEl) return;
      const classes = ['text-blue-600', 'text-green-600', 'text-red-600', 'text-muted-foreground', 'hidden'];
      feedbackEl.classList.remove(...classes);
      const map = { info: 'text-blue-600', success: 'text-green-600', error: 'text-red-600' };
      feedbackEl.classList.add(map[type] || 'text-blue-600');
      feedbackEl.textContent = message;
    };

    const toggleDropzoneHighlight = (zone, active) => {
      if (!zone) return;
      zone.classList.toggle('ring-2', !!active);
      zone.classList.toggle('ring-blue-400', !!active);
    };

    const progressWrap = document.getElementById('bulk-upload-progress');
    const progressBar = document.getElementById('bulk-upload-progress-bar');
    const progressLabel = document.getElementById('bulk-upload-progress-text');
    const showProgress = (percent, message) => {
      if (!progressWrap || !progressBar || !progressLabel) return;
      progressWrap.classList.remove('hidden');
      const safePercent = Math.min(100, Math.max(0, percent));
      progressBar.style.width = safePercent + '%';
      progressLabel.textContent = message || '';
    };
    const resetProgress = () => {
      if (!progressWrap || !progressBar || !progressLabel) return;
      progressBar.style.width = '0%';
      progressLabel.textContent = '';
      progressWrap.classList.add('hidden');
    };

    (function initBulkUpload() {
      const dropzone = document.getElementById('bulk-upload-dropzone');
      if (!dropzone) return;

      const input = document.getElementById('bulk-upload-input');
      const feedback = document.getElementById('bulk-upload-feedback');
      const results = document.getElementById('bulk-upload-results');
      const browseButton = document.getElementById('bulk-upload-browse');
      const uploadUrl = dropzone.dataset.uploadUrl;
      const maxFiles = parseInt(dropzone.dataset.maxFiles || '50', 10);

      const appendResult = (message, kind) => {
        if (!results) return;
        const li = document.createElement('li');
        li.textContent = message;
        li.className = kind === 'error' ? 'text-red-600' : 'text-green-600';
        results.prepend(li);
      };

      const enableDropzone = (enable) => {
        if (!dropzone) return;
        dropzone.classList.toggle('opacity-60', !enable);
        dropzone.classList.toggle('pointer-events-none', !enable);
      };

      const handleFiles = (fileList) => {
        const files = Array.from(fileList || []);
        if (!files.length || !uploadUrl) return;

        if (Number.isFinite(maxFiles) && maxFiles > 0 && files.length > maxFiles) {
          setFeedback(feedback, `Seleccionaste ${files.length} archivo(s) y el maximo permitido es ${maxFiles}.`, 'error');
          return;
        }

        const formData = new FormData();
        files.forEach((file) => formData.append('audios[]', file));

        setFeedback(feedback, `Subiendo ${files.length} archivo(s)...`, 'info');
        resetProgress();
        showProgress(0, 'Preparando subida...');
        enableDropzone(false);
        toggleDropzoneHighlight(dropzone, true);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl);
        xhr.responseType = 'json';
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.addEventListener('progress', (event) => {
          if (!event.lengthComputable) return;
          const percent = (event.loaded / event.total) * 100;
          showProgress(percent, `Subiendo... ${percent.toFixed(0)}%`);
        });

        xhr.addEventListener('load', () => {
          const payload = xhr.response ?? {};
          const created = Array.isArray(payload.created) ? payload.created : [];
          const errors = Array.isArray(payload.errors) ? payload.errors : [];

          created.forEach((item) => {
            appendResult(`+ ${item.titulo || 'Audio sin titulo'} (#${item.id})`, 'success');
          });
          errors.forEach((errorItem) => {
            const fileName = errorItem?.file || 'archivo';
            const message = errorItem?.message || 'Error inesperado.';
            appendResult(`- ${fileName}: ${message}`, 'error');
          });

          if (xhr.status < 200 || xhr.status >= 300) {
            const errorMessage = payload?.errors?.audios?.[0]
              || payload?.message
              || 'No se pudieron subir los audios.';
            setFeedback(feedback, errorMessage, 'error');
            resetProgress();
            enableDropzone(true);
            toggleDropzoneHighlight(dropzone, false);
            if (input) input.value = '';
            return;
          }

          if (created.length) {
            showProgress(100, 'Subida completada.');
            setFeedback(feedback, `Se cargaron ${created.length} audio(s). Actualizando tabla...`, 'success');
            setTimeout(() => window.location.reload(), 1000);
          } else if (errors.length) {
            setFeedback(feedback, 'No se pudo procesar uno o mas archivos.', 'error');
            resetProgress();
            enableDropzone(true);
            toggleDropzoneHighlight(dropzone, false);
          } else {
            setFeedback(feedback, 'No se recibio confirmacion del servidor.', 'error');
            resetProgress();
            enableDropzone(true);
            toggleDropzoneHighlight(dropzone, false);
          }
        });

        xhr.addEventListener('error', () => {
          setFeedback(feedback, 'No se pudieron subir los audios.', 'error');
          resetProgress();
          enableDropzone(true);
          toggleDropzoneHighlight(dropzone, false);
        });

        xhr.addEventListener('abort', () => {
          setFeedback(feedback, 'La subida fue cancelada.', 'error');
          resetProgress();
          enableDropzone(true);
          toggleDropzoneHighlight(dropzone, false);
        });

        xhr.send(formData);

        if (input) input.value = '';
      };

      ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          toggleDropzoneHighlight(dropzone, true);
        });
      });

      ['dragleave', 'dragend'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
          event.preventDefault();
          toggleDropzoneHighlight(dropzone, false);
        });
      });

      dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        toggleDropzoneHighlight(dropzone, false);
        if (event.dataTransfer?.files?.length) {
          handleFiles(event.dataTransfer.files);
        }
      });

      dropzone.addEventListener('click', () => {
        input?.click();
      });

      browseButton?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        input?.click();
      });

      input?.addEventListener('change', () => {
        if (input.files?.length) {
          handleFiles(input.files);
        }
        input.value = '';
      });
    })();

    (function initStatusSelects() {
      const selects = document.querySelectorAll('.js-audio-status-select');
      if (!selects.length) return;

      const applyStateStyles = (element, state) => {
        const isPublished = state === 'Publicado';
        element.classList.remove(
          'bg-green-200',
          'text-green-800',
          'dark:bg-green-900',
          'dark:text-green-300',
          'bg-yellow-100',
          'text-yellow-800',
          'dark:bg-yellow-900',
          'dark:text-yellow-300'
        );
        if (isPublished) {
          element.classList.add('bg-green-200', 'text-green-800', 'dark:bg-green-900', 'dark:text-green-300');
        } else {
          element.classList.add('bg-yellow-100', 'text-yellow-800', 'dark:bg-yellow-900', 'dark:text-yellow-300');
        }
      };

      selects.forEach((select) => {
        const current = select.value;
        select.dataset.lastValue = current;
        applyStateStyles(select, current);

        select.addEventListener('change', async () => {
          const desired = select.value;
          const previous = select.dataset.lastValue || current;

          if (!csrfToken) {
            alert('No se encontro token CSRF.');
            select.value = previous;
            return;
          }

          select.disabled = true;

          try {
            const response = await fetch(select.dataset.updateUrl, {
              method: 'PATCH',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
              },
              body: JSON.stringify({ estado: desired }),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
              const message = payload?.errors?.estado?.[0]
                || payload?.message
                || 'No se pudo actualizar el estado.';
              throw new Error(message);
            }

            select.dataset.lastValue = desired;
            applyStateStyles(select, desired);
          } catch (error) {
            alert(error?.message || 'No se pudo actualizar el estado.');
            select.value = previous;
            applyStateStyles(select, previous);
          } finally {
            select.disabled = false;
          }
        });
      });
    })();
  });
</script>
@endif
@endpush
