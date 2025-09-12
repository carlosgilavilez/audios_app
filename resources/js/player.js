const Player = (() => {
  const $ = id => document.getElementById(id);
  const audio = $('pl-audio');
  const sticky = $('sticky-player');
  const btnPlay = $('pl-play'), iconPlay = $('pl-play-icon'), iconPause = $('pl-pause-icon');
  const btnPrev = $('pl-prev'), btnNext = $('pl-next');
  const lblTitle = $('pl-title'), lblAuthor = $('pl-author');
  const seek = $('pl-seek'), cur = $('pl-current'), dur = $('pl-duration');
  const vol = $('pl-volume'), rate = $('pl-rate'), aDownload = $('pl-download');

  let items = [];          // NodeList de botones de la tabla
  let index = -1;          // índice actual
  let dragging = false;

  // Helpers
  const fmt = s => (Number.isFinite(s) ? `${Math.floor(s/60)}:${String(Math.floor(s%60)).padStart(2,'0')}` : '0:00');
  const showSticky = () => { sticky.classList.remove('hidden'); requestAnimationFrame(() => sticky.classList.remove('opacity-0','translate-y-2')); };
  const setPlayUI = p => { iconPlay.classList.toggle('hidden', p); iconPause.classList.toggle('hidden', !p); btnPlay.setAttribute('aria-label', p ? 'Pausar' : 'Reproducir'); };
  const clearRowStates = () => document.querySelectorAll('.btn-play').forEach(b=>{
    b.classList.remove('bg-success','text-success-foreground'); // volver a modo neutro
    b.classList.add('bg-card','text-success');
    b.querySelector('.icon-play')?.classList.remove('hidden');
    b.querySelector('.icon-eq')?.classList.add('hidden');
  });
  const activateRow = (btn) => {
    btn.classList.remove('bg-card','text-success');
    btn.classList.add('bg-success','text-success-foreground'); // activo
    btn.querySelector('.icon-play')?.classList.add('hidden');
    btn.querySelector('.icon-eq')?.classList.remove('hidden');
    btn.focus({preventScroll:true});
  };

  // Cargar y reproducir índice
  const load = async (i) => {
    if (i < 0 || i >= items.length) return;
    index = i;
    const btn = items[index]; // This is always a DOM element

    let src, title, author, download;

    if (window.audioPlaylist && window.audioPlaylist[index]) {
        const audioData = window.audioPlaylist[index];
        src = audioData.play_url;
        title = audioData.title;
        author = audioData.artist;
        download = audioData.download_url;
    } else { // Fallback to dataset from the DOM element
        src = btn.dataset.audioSrc;
        title = btn.dataset.title;
        author = btn.dataset.author;
        download = btn.dataset.download;
    }

    lblTitle.textContent = title || '—';
    lblAuthor.textContent = author || '—';
    aDownload.href = download || src;

    audio.pause();
    audio.src = src;
    audio.load();

    clearRowStates();
    activateRow(btn); // Always activate the clicked DOM element

    showSticky();
    try { await audio.play(); setPlayUI(true); } catch(e){ console.error('play()', e); setPlayUI(false); }
  };

  // Bind filas
  const bind = () => {
    items = Array.from(document.querySelectorAll('.btn-play[data-audio-src]'));
    items.forEach((btn, i) => {
      btn.addEventListener('click', (ev) => {
        ev.preventDefault();
        const btnAudioSrc = btn.dataset.audioSrc;
        if (audio.src === btnAudioSrc && !audio.paused) {
          audio.pause();
        } else {
          load(i);
        }
      });
    });
  };

  // Controles del sticky
  btnPlay.addEventListener('click', async () => {
    if (audio.paused) { try { await audio.play(); setPlayUI(true); } catch(e){} }
    else { audio.pause(); setPlayUI(false); }
  });
  btnPrev.addEventListener('click',  () => { if (index > 0) load(index-1); });
  btnNext.addEventListener('click',  () => { if (index < items.length-1) load(index+1); });

  // Seek
  audio.addEventListener('loadedmetadata', () => { seek.max = Math.floor(audio.duration||0); dur.textContent = fmt(audio.duration||0); });
  audio.addEventListener('timeupdate', () => { if (!dragging) seek.value = Math.floor(audio.currentTime||0); cur.textContent = fmt(audio.currentTime||0); });
  const commitSeek = () => { const t = Number(seek.value||0); if (Number.isFinite(t)) audio.currentTime = t; dragging = false; };
  seek.addEventListener('input', () => { dragging = true; cur.textContent = fmt(Number(seek.value)); });
  seek.addEventListener('change', commitSeek);
  seek.addEventListener('mouseup', commitSeek); seek.addEventListener('touchend', commitSeek, {passive:true});

  // Volumen / velocidad
  vol.addEventListener('input',  () => audio.volume = Number(vol.value));
  rate.addEventListener('change', () => audio.playbackRate = Number(rate.value.replace('x','')));

  // Fin de pista
  audio.addEventListener('ended', () => { setPlayUI(false); clearRowStates(); });

  // Sincronizar barras EQ con play/pause desde el sticky
  const toggleEqForCurrent = (show) => {
    if (index < 0 || index >= items.length) return;
    const btn = items[index];
    btn.querySelector('.icon-eq')?.classList.toggle('hidden', !show);
    btn.querySelector('.icon-play')?.classList.toggle('hidden', show);
  };

  audio.addEventListener('play',  () => { setPlayUI(true);  toggleEqForCurrent(true); });
  audio.addEventListener('pause', () => { setPlayUI(false); toggleEqForCurrent(false); });

  // Persistencia básica de estado
  const persistKey = 'pl-state';
  let lastSaveAt = 0;
  const saveState = () => {
    try {
      const state = {
        src: audio.currentSrc || audio.src || '',
        title: lblTitle.textContent || '',
        author: lblAuthor.textContent || '',
        download: aDownload.getAttribute('href') || '',
        index,
        currentTime: Number(audio.currentTime || 0),
        paused: audio.paused,
      };
      localStorage.setItem(persistKey, JSON.stringify(state));
    } catch {}
  };

  audio.addEventListener('timeupdate', () => {
    const now = Date.now();
    if (now - lastSaveAt > 1000) { lastSaveAt = now; saveState(); }
  });
  audio.addEventListener('play',  () => { saveState(); });
  audio.addEventListener('pause', () => { saveState(); });

  const restore = async () => {
    try {
      const raw = localStorage.getItem(persistKey);
      if (!raw) return;
      const st = JSON.parse(raw);
      if (!st || !st.src) return;

      // Si hay un botón en la tabla que coincide, úsalo
      const match = Array.from(document.querySelectorAll('.btn-play[data-audio-src]'))
        .find(b => b.dataset.audioSrc === st.src);
      if (match) {
        items = Array.from(document.querySelectorAll('.btn-play[data-audio-src]'));
        index = Number(match.dataset.index ?? -1);
        clearRowStates();
        activateRow(match);
      } else {
        index = -1;
      }

      lblTitle.textContent = st.title || '';
      lblAuthor.textContent = st.author || '';
      aDownload.href = st.download || st.src;

      audio.pause();
      audio.src = st.src;
      audio.load();
      showSticky();
      const seekTo = Number(st.currentTime || 0);
      if (Number.isFinite(seekTo) && seekTo > 0) {
        audio.addEventListener('loadedmetadata', function onlm(){
          audio.removeEventListener('loadedmetadata', onlm);
          try { audio.currentTime = seekTo; } catch {}
        });
      }
      if (st.paused === false) {
        try { await audio.play(); setPlayUI(true); } catch {}
      } else {
        setPlayUI(false);
      }
    } catch {}
  };

  // API pública
  return { bind, restore };
})();

document.addEventListener('DOMContentLoaded', () => {
  Player.bind();
  const path = location.pathname;
  const skipRestore = /^(?:\/admin|\/editor)\b/.test(path);
  if (!skipRestore && typeof Player.restore === 'function') Player.restore();
});
