const Player = (function () {
  const byId = function (id) { return document.getElementById(id); };
  const audio = byId('pl-audio');
  if (!audio) {
    return { bind: function () {}, restore: function () {} };
  }

  const sticky = byId('sticky-player');
  const btnPlay = byId('pl-play');
  const iconPlay = byId('pl-play-icon');
  const iconPause = byId('pl-pause-icon');
  const btnPrev = byId('pl-prev');
  const btnNext = byId('pl-next');
  const btnBack10 = byId('pl-back-10');
  const btnForward10 = byId('pl-forward-10');
  const lblTitle = byId('pl-title');
  const lblAuthor = byId('pl-author');
  const seek = byId('pl-seek');
  const cur = byId('pl-current');
  const dur = byId('pl-duration');
  const vol = byId('pl-volume');
  const rate = byId('pl-rate');
  const aDownload = byId('pl-download');
  const expandToggle = document.getElementById('pl-expand');
  const toggleLabel = expandToggle ? expandToggle.querySelector('[data-player-toggle-label]') : null;
  const playerMediaQuery = window.matchMedia ? window.matchMedia('(max-width: 767px)') : null;

  let isExpanded = true;

  let items = [];
  let index = -1;
  let dragging = false;
  const mediaMatchesMobile = () => (playerMediaQuery ? playerMediaQuery.matches : (window.innerWidth || 0) <= 767);

  const setExpanded = (value) => {
    if (!sticky) return;
    const expanded = !!value;
    isExpanded = expanded;
    sticky.setAttribute('data-player-expanded', expanded ? 'true' : 'false');
    if (expandToggle) {
      expandToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      if (toggleLabel) {
        toggleLabel.textContent = expanded ? 'Ocultar controles' : 'Mostrar controles';
      }
    }
  };

  const ensureExpandedForInteraction = () => {
    if (mediaMatchesMobile() && !isExpanded) {
      setExpanded(true);
    }
  };

  const applyInitialExpansion = () => {
    if (mediaMatchesMobile()) {
      setExpanded(false);
    } else {
      setExpanded(true);
    }
  };

  if (playerMediaQuery) {
    const mqListener = (event) => {
      if (event.matches) {
        setExpanded(false);
      } else {
        setExpanded(true);
      }
    };
    if (typeof playerMediaQuery.addEventListener === 'function') {
      playerMediaQuery.addEventListener('change', mqListener);
    } else if (typeof playerMediaQuery.addListener === 'function') {
      playerMediaQuery.addListener(mqListener);
    }
  }
  applyInitialExpansion();

  function fmt(value) {
    if (!isFinite(value) || value < 0) return '0:00';
    const minutes = Math.floor(value / 60);
    const seconds = Math.floor(value % 60);
    return minutes + ':' + (seconds < 10 ? '0' + seconds : String(seconds));
  }

  function sanitizeName(name) {
    const base = (name || '').toString().trim() || 'audio';
    const cleaned = base.replace(/[\\/:*?"<>|]+/g, '');
    return cleaned.slice(0, 60) || 'audio';
  }

  function updateDownload(title, fallback) {
    if (!aDownload) return;
    const href = aDownload.getAttribute('href') || fallback || '';
    if (href) {
      aDownload.setAttribute('href', href);
    }
    const extCandidate = href.split('?')[0].split('.').pop() || 'mp3';
    const ext = extCandidate.length <= 4 ? extCandidate : 'mp3';
    aDownload.setAttribute('download', sanitizeName(title) + '.' + ext);
  }

  function showSticky() {
    if (!sticky) return;
    sticky.classList.remove('hidden');
    requestAnimationFrame(function () {
      sticky.classList.remove('opacity-0');
      sticky.classList.remove('translate-y-2');
    });
  }

  function setPlayUI(playing) {
    if (iconPlay) iconPlay.classList.toggle('hidden', playing);
    if (iconPause) iconPause.classList.toggle('hidden', !playing);
    if (btnPlay) {
      btnPlay.setAttribute('aria-label', playing ? 'Pausar' : 'Reproducir');
      btnPlay.setAttribute('aria-pressed', playing ? 'true' : 'false');
    }
  }

  function clearRowStates() {
    const activeScope = document.querySelector('[data-wp-section]:not([hidden]) [data-view-active="true"]') || document.querySelector('[data-view-active="true"]');
    if (!activeScope) return;
    const buttons = activeScope.querySelectorAll('.btn-play');
    buttons.forEach(function (button) {
      button.classList.remove('bg-success', 'text-success-foreground');
      button.classList.add('bg-card', 'text-success');
      const playIcon = button.querySelector('.icon-play');
      if (playIcon) playIcon.classList.remove('hidden');
      const eqIcon = button.querySelector('.icon-eq');
      if (eqIcon) eqIcon.classList.add('hidden');
    });
  }

  function activateRow(button) {
    if (!button) return;
    button.classList.remove('bg-card', 'text-success');
    button.classList.add('bg-success', 'text-success-foreground');
    const playIcon = button.querySelector('.icon-play');
    if (playIcon) playIcon.classList.add('hidden');
    const eqIcon = button.querySelector('.icon-eq');
    if (eqIcon) eqIcon.classList.remove('hidden');
    if (typeof button.focus === 'function') {
      button.focus({ preventScroll: true });
    }
  }

  async function load(i) {
    if (i < 0 || i >= items.length) return;
    index = i;
    const btn = items[index];

    let src = '';
    let title = '';
    let author = '';
    let download = '';

    if (window.audioPlaylist && window.audioPlaylist[index]) {
      const audioData = window.audioPlaylist[index];
      src = audioData.play_url || '';
      title = audioData.title || '';
      author = audioData.artist || '';
      download = audioData.download_url || '';
    } else if (btn) {
      src = btn.dataset.audioSrc || '';
      title = btn.dataset.title || '';
      author = btn.dataset.author || '';
      download = btn.dataset.download || '';
    }

    if (lblTitle) lblTitle.textContent = title || '-';
    if (lblAuthor) lblAuthor.textContent = author || '-';
    updateDownload(title, download || src);

    audio.pause();
    if (src) {
      audio.src = src;
      audio.load();
    }

    clearRowStates();
    activateRow(btn);
    showSticky();
    ensureExpandedForInteraction();

    try {
      await audio.play();
      setPlayUI(true);
    } catch (error) {
      console.error('play()', error);
      setPlayUI(false);
    }
  }

  function bind() {
    const activeScope = document.querySelector('[data-wp-section]:not([hidden]) [data-view-active="true"]') || document.querySelector('[data-view-active="true"]') || document;
    items = Array.from(activeScope.querySelectorAll('.btn-play[data-audio-src]'));
    items.forEach(function (button, i) {
      button.dataset.index = i;
      if (button.dataset.playerBound === '1') return;
      button.dataset.playerBound = '1';
      button.addEventListener('click', function (event) {
        event.preventDefault();
        const targetSrc = button.dataset.audioSrc;
        if (!targetSrc) return;

        if (audio.src === targetSrc) {
          if (audio.paused) {
            index = i;
            clearRowStates();
            activateRow(button);
            showSticky();
            ensureExpandedForInteraction();
        ensureExpandedForInteraction();
            audio.play().then(function () {
              setPlayUI(true);
            }).catch(function () {});
          } else {
            audio.pause();
          }
          return;
        }

        load(i);
      });
    });
  }

  function skipBy(delta) {
    if (!isFinite(delta)) return;
    const total = isFinite(audio.duration) ? audio.duration : 0;
    const current = isFinite(audio.currentTime) ? audio.currentTime : 0;
    const next = Math.max(0, Math.min(total, current + delta));
    audio.currentTime = next;
    if (!dragging && seek) seek.value = String(Math.floor(next));
    if (cur) cur.textContent = fmt(next);
  }

  if (btnPlay) {
    btnPlay.addEventListener('click', function () {
      if (audio.paused) {
        audio.play().then(function () {
          setPlayUI(true);
        }).catch(function () {});
      } else {
        audio.pause();
        setPlayUI(false);
      }
    });
  }

  if (btnPrev) {
    btnPrev.addEventListener('click', function () {
      if (index > 0) {
        bind();
        load(index - 1);
      }
    });
  }

  if (btnNext) {
    btnNext.addEventListener('click', function () {
      if (index < items.length - 1) {
        bind();
        load(index + 1);
      }
    });
  }

  if (expandToggle) {
    expandToggle.addEventListener("click", function (event) {
      event.preventDefault();
      setExpanded(!isExpanded);
    });
  }

  if (btnBack10) {
    btnBack10.addEventListener('click', function () {
      skipBy(-10);
    });
  }

  if (btnForward10) {
    btnForward10.addEventListener('click', function () {
      skipBy(10);
    });
  }

  audio.addEventListener('loadedmetadata', function () {
    const duration = Math.floor(audio.duration || 0);
    if (seek) seek.max = String(duration);
    if (dur) dur.textContent = fmt(duration);
  });

  audio.addEventListener('timeupdate', function () {
    const current = Math.floor(audio.currentTime || 0);
    if (!dragging && seek) seek.value = String(current);
    if (cur) cur.textContent = fmt(current);
  });

  function commitSeek() {
    if (!seek) return;
    const value = Number(seek.value || 0);
    if (isFinite(value)) audio.currentTime = value;
    dragging = false;
  }

  if (seek) {
    seek.addEventListener('input', function () {
      dragging = true;
      if (cur) cur.textContent = fmt(Number(seek.value));
    });
    seek.addEventListener('change', commitSeek);
    seek.addEventListener('mouseup', commitSeek);
    seek.addEventListener('touchend', commitSeek, { passive: true });
  }

  if (vol) {
    vol.addEventListener('input', function () {
      const value = Number(vol.value);
      if (isFinite(value)) {
        audio.volume = Math.max(0, Math.min(1, value));
      }
    });
  }

  if (rate) {
    rate.addEventListener('change', function () {
      const value = Number((rate.value || '').replace('x', ''));
      if (isFinite(value) && value > 0) {
        audio.playbackRate = value;
      }
    });
  }

  audio.addEventListener('ended', function () {
    setPlayUI(false);
    clearRowStates();
  });

  function toggleEqForCurrent(show) {
    if (index < 0 || index >= items.length) return;
    const button = items[index];
    const eqIcon = button.querySelector('.icon-eq');
    if (eqIcon) eqIcon.classList.toggle('hidden', !show);
    const playIcon = button.querySelector('.icon-play');
    if (playIcon) playIcon.classList.toggle('hidden', show);
  }

  audio.addEventListener('play', function () {
    setPlayUI(true);
    toggleEqForCurrent(true);
  });

  audio.addEventListener('pause', function () {
    setPlayUI(false);
    toggleEqForCurrent(false);
  });

  const persistKey = 'pl-state';
  let lastSaveAt = 0;

  function saveState() {
    try {
      const state = {
        src: audio.currentSrc || audio.src || '',
        title: lblTitle ? lblTitle.textContent || '' : '',
        author: lblAuthor ? lblAuthor.textContent || '' : '',
        download: aDownload ? aDownload.getAttribute('href') || '' : '',
        index: index,
        currentTime: Number(audio.currentTime || 0),
        paused: audio.paused,
        volume: Number(audio.volume || 1),
        rate: Number(audio.playbackRate || 1)
      };
      localStorage.setItem(persistKey, JSON.stringify(state));
    } catch (error) {}
  }

  audio.addEventListener('timeupdate', function () {
    const now = Date.now();
    if (now - lastSaveAt > 1000) {
      lastSaveAt = now;
      saveState();
    }
  });
  audio.addEventListener('play', saveState);
  audio.addEventListener('pause', saveState);
  window.addEventListener('beforeunload', saveState);
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') saveState();
  });

  async function restore() {
    try {
      const raw = localStorage.getItem(persistKey);
      if (!raw) return;
      const st = JSON.parse(raw);
      if (!st || !st.src) return;

      const match = Array.from(document.querySelectorAll('.btn-play[data-audio-src]'))
        .find(function (button) { return button.dataset.audioSrc === st.src; });

      if (match) {
        items = Array.from(document.querySelectorAll('.btn-play[data-audio-src]'));
        index = Number(match.dataset.index || items.indexOf(match));
        clearRowStates();
        activateRow(match);
      } else {
        index = -1;
      }

      if (lblTitle) lblTitle.textContent = st.title || '-';
      if (lblAuthor) lblAuthor.textContent = st.author || '-';
      if (aDownload) {
        aDownload.href = st.download || st.src;
        updateDownload(st.title, st.download || st.src);
      }

      audio.pause();
      audio.src = st.src;
      audio.load();
      showSticky();
      ensureExpandedForInteraction();

      if (isFinite(st.volume)) {
        try { audio.volume = Math.max(0, Math.min(1, Number(st.volume))); } catch (error) {}
        if (vol) vol.value = String(audio.volume);
      }

      if (isFinite(st.rate) && st.rate > 0) {
        try { audio.playbackRate = Number(st.rate); } catch (error) {}
        if (rate) {
          const val = audio.playbackRate + 'x';
          const options = Array.from(rate.options || []);
          if (options.some(function (opt) { return opt.value === val; })) {
            rate.value = val;
          }
        }
      }

      const seekTo = Number(st.currentTime || 0);
      if (isFinite(seekTo) && seekTo > 0) {
        audio.addEventListener('loadedmetadata', function handleLoaded() {
          audio.removeEventListener('loadedmetadata', handleLoaded);
          try { audio.currentTime = seekTo; } catch (error) {}
        });
      }

      if (st.paused === false) {
        try {
          await audio.play();
          setPlayUI(true);
        } catch (error) {}
      } else {
        setPlayUI(false);
      }
    } catch (error) {}
  }

  return { bind: bind, restore: restore };
})();

document.addEventListener('DOMContentLoaded', function () {
  if (!Player || typeof Player.bind !== 'function') return;
  Player.bind();
  const skipRestore = /^(?:\/admin|\/editor)\b/.test(location.pathname);
  if (!skipRestore && typeof Player.restore === 'function') {
    Player.restore();
  }
});




