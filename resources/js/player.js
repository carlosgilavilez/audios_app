document.addEventListener('DOMContentLoaded', () => {
  const playerManager = {
    audio: document.getElementById('pl-audio'),
    stickyPlayer: document.getElementById('sticky-player'),

    elements: {
      play: document.querySelectorAll('button[id^="pl-play"]'),
      playIcon: document.querySelectorAll('[id^="pl-play-icon"]'),
      pauseIcon: document.querySelectorAll('[id^="pl-pause-icon"]'),
      prev: document.querySelectorAll('[id^="pl-prev"]'),
      next: document.querySelectorAll('[id^="pl-next"]'),
      back10: document.querySelectorAll('[id^="pl-back-10"]'),
      forward10: document.querySelectorAll('[id^="pl-forward-10"]'),
      title: document.querySelectorAll('[id^="pl-title"]'),
      author: document.querySelectorAll('[id^="pl-author"]'),
      category: document.querySelectorAll('[id^="pl-category"]'),
      series: document.querySelectorAll('[id^="pl-series"]'),
      dateMeta: document.querySelectorAll('[id^="pl-date"]'),
      citation: document.querySelectorAll('[id^="pl-citation"]'),
      current: document.querySelectorAll('[id^="pl-current"]'),
      duration: document.querySelectorAll('[id^="pl-duration"]'),
      seek: document.querySelectorAll('[id^="pl-seek"]'),
      miniSeek: document.getElementById('pl-seek-minimized'),
      progressLine: document.querySelectorAll('[id^="pl-progress-line"]'),
      volume: document.getElementById('pl-volume'),
      volumeToggle: document.getElementById('pl-volume-toggle'),
      download: document.querySelectorAll('[id^="pl-download"]'),
      expand: document.getElementById('pl-expand'),
      collapse: document.getElementById('pl-collapse'),
      minimizedPlayer: document.getElementById('minimized-player'),
      expandedPlayer: document.getElementById('expanded-player'),
    },

    state: {
      playlist: [],
      currentIndex: -1,
      isPlaying: false,
      isMuted: false,
      activeButton: null,
      scrubbing: null,
    },

    init() {
      if (!this.audio) {
        console.error('Audio element #pl-audio not found.');
        return;
      }
      this.collectPlaylist();
      this.bindEventListeners();
      window.addEventListener('resize', () => this.adjustBodyOffset());
    },

    collectPlaylist() {
      const trackButtons = document.querySelectorAll('.btn-play[data-audio-src]');
      this.state.playlist = Array.from(trackButtons).map((button, index) => ({
        src: button.dataset.audioSrc,
        title: button.dataset.title || 'Pista sin título',
        author: button.dataset.author || 'Autor desconocido',
        category: button.dataset.category || '',
        series: button.dataset.series || '',
        date: button.dataset.date || '',
        citation: button.dataset.citation || '',
        download: button.dataset.download,
        button,
      }));
    },

    bindEventListeners() {
      this.state.playlist.forEach((track, index) => {
        track.button.addEventListener('click', (event) => {
          event.preventDefault();
          if (this.state.currentIndex === index) {
            this.togglePlayPause();
          } else {
            this.playTrack(index);
          }
        });
      });

      this.elements.play.forEach((button) => button.addEventListener('click', () => this.togglePlayPause()));
      this.elements.next.forEach((button) => button.addEventListener('click', () => this.playNext()));
      this.elements.prev.forEach((button) => button.addEventListener('click', () => this.playPrev()));
      this.elements.back10.forEach((button) => button.addEventListener('click', () => { this.audio.currentTime -= 10; }));
      this.elements.forward10.forEach((button) => button.addEventListener('click', () => { this.audio.currentTime += 10; }));

      this.audio.addEventListener('timeupdate', () => this.updateProgress());
      this.audio.addEventListener('loadedmetadata', () => this.updateDuration());
      this.audio.addEventListener('ended', () => this.playNext());
      this.audio.addEventListener('play', () => this.updatePlayState(true));
      this.audio.addEventListener('pause', () => this.updatePlayState(false));
      this.audio.addEventListener('volumechange', () => this.updateVolumeState());

      this.elements.seek.forEach((bar) => {
        if (bar === this.elements.miniSeek) {
          bar.addEventListener('input', () => this.previewMiniSeek(bar));
          bar.addEventListener('change', () => this.commitMiniSeek(bar));
          bar.addEventListener('blur', () => this.commitMiniSeek(bar));
          bar.addEventListener('pointercancel', () => {
            if (this.state.scrubbing === bar) {
              this.state.scrubbing = null;
              this.updateProgress();
            }
          });
        } else {
          bar.addEventListener('input', (event) => this.seek(event.target.value));
        }
      });
      if (this.elements.volume) {
        this.elements.volume.addEventListener('input', (event) => this.setVolume(event.target.value));
      }
      if (this.elements.volumeToggle) {
        this.elements.volumeToggle.addEventListener('click', () => this.toggleMute());
      }

      if (this.elements.expand) {
        this.elements.expand.addEventListener('click', () => this.expandPlayer());
      }
      if (this.elements.collapse) {
        this.elements.collapse.addEventListener('click', () => this.collapsePlayer());
      }
    },

    playTrack(index) {
      if (index < 0 || index >= this.state.playlist.length) {
        console.warn(`Invalid track index: ${index}`);
        return;
      }

      this.state.currentIndex = index;
      const track = this.state.playlist[index];

      this.state.scrubbing = null;
      if (this.elements.miniSeek) {
        this.elements.miniSeek.disabled = true;
        this.elements.miniSeek.value = 0;
        this.elements.miniSeek.setAttribute('aria-valuetext', 'Cargando audio');
      }
      this.audio.src = track.src;
      this.updateMetadata(track);

      this.audio.play().catch((error) => console.error('Error playing audio:', error));

      if (this.stickyPlayer.classList.contains('hidden')) {
        this.stickyPlayer.classList.remove('hidden', 'opacity-0', 'translate-y-2');
        this.stickyPlayer.style.transform = 'translateY(0)';
        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }
      }

      this.adjustBodyOffset();
    },

    updatePlayState(isPlaying) {
      this.state.isPlaying = isPlaying;
      this.updateNodeList(this.elements.play, (button) => {
        button.setAttribute('aria-label', isPlaying ? 'Pausar' : 'Reproducir');
        button.setAttribute('aria-pressed', isPlaying ? 'true' : 'false');
      });
      this.updateNodeList(this.elements.playIcon, (element) => element.classList.toggle('hidden', isPlaying));
      this.updateNodeList(this.elements.pauseIcon, (element) => element.classList.toggle('hidden', !isPlaying));
      this.updateActiveButton(isPlaying);
    },

    updateActiveButton(isPlaying) {
      if (this.state.activeButton) {
        this.setButtonState(this.state.activeButton, false);
      }

      const currentTrack = this.state.playlist[this.state.currentIndex];
      if (currentTrack) {
        this.state.activeButton = currentTrack.button;
        this.setButtonState(this.state.activeButton, isPlaying);
      }
    },

    setButtonState(button, isPlaying) {
      button.setAttribute('aria-label', `${isPlaying ? 'Pausar' : 'Reproducir'} ${button.dataset.title || 'audio'}`);
      button.setAttribute('aria-pressed', isPlaying ? 'true' : 'false');
      const playIcon = button.querySelector('.icon-play');
      const eqIcon = button.querySelector('.icon-eq');
      if (playIcon) playIcon.classList.toggle('hidden', isPlaying);
      if (eqIcon) eqIcon.classList.toggle('hidden', !isPlaying);
    },

    updateMetadata(track) {
      this.updateNodeList(this.elements.title, (element) => { element.textContent = track.title; });
      this.updateNodeList(this.elements.author, (element) => { element.textContent = track.author; });
      this.updateNodeList(this.elements.download, (element) => {
        if (track.download) {
          element.href = track.download;
          element.style.display = 'inline-flex';
        } else {
          element.style.display = 'none';
        }
      });
      this.updateMetaField(this.elements.category, track.category);
      this.updateMetaField(this.elements.series, track.series);
      this.updateMetaField(this.elements.dateMeta, track.date);
      this.updateMetaField(this.elements.citation, track.citation);
    },

    togglePlayPause() {
      if (this.state.isPlaying) {
        this.audio.pause();
      } else {
        if (this.state.currentIndex === -1) {
          this.playTrack(0);
        } else {
          this.audio.play().catch((error) => console.error('Error resuming audio:', error));
        }
      }
    },

    playNext() {
      let nextIndex = this.state.currentIndex + 1;
      if (nextIndex >= this.state.playlist.length) {
        nextIndex = 0;
      }
      this.playTrack(nextIndex);
    },

    playPrev() {
      let prevIndex = this.state.currentIndex - 1;
      if (prevIndex < 0) {
        prevIndex = this.state.playlist.length - 1;
      }
      this.playTrack(prevIndex);
    },

    updateProgress() {
      if (this.state.scrubbing) return;
      this.renderProgress(this.audio.currentTime);
    },

    renderProgress(time) {
      const duration = this.audio.duration;
      if (!Number.isFinite(duration) || duration <= 0 || !Number.isFinite(time)) return;
      const position = Math.max(0, Math.min(duration, time));
      const progress = (position / duration) * 100;
      this.updateNodeList(this.elements.seek, (element) => {
        element.value = progress;
        element.style.setProperty('--range-progress', `${progress}%`);
        element.setAttribute('aria-valuetext', `${this.formatTime(position)} de ${this.formatTime(duration)}`);
      });
      this.updateNodeList(this.elements.progressLine, (element) => { element.style.width = `${progress}%`; });
      this.updateNodeList(this.elements.current, (element) => { element.textContent = this.formatTime(position); });
    },

    updateDuration() {
      if (this.elements.miniSeek) {
        this.elements.miniSeek.disabled = !Number.isFinite(this.audio.duration) || this.audio.duration <= 0;
      }
      this.updateNodeList(this.elements.duration, (element) => { element.textContent = this.formatTime(this.audio.duration); });
      this.updateProgress();
    },

    previewMiniSeek(bar) {
      if (!Number.isFinite(this.audio.duration) || this.audio.duration <= 0) return;
      this.state.scrubbing = bar;
      this.renderProgress((this.audio.duration / 100) * Number(bar.value));
    },

    commitMiniSeek(bar) {
      if (this.state.scrubbing !== bar) return;
      const value = bar.value;
      this.state.scrubbing = null;
      this.seek(value);
    },

    seek(value) {
      const percent = Number(value);
      if (!Number.isFinite(this.audio.duration) || this.audio.duration <= 0 || !Number.isFinite(percent)) return;
      this.audio.currentTime = (this.audio.duration / 100) * Math.max(0, Math.min(100, percent));
      this.updateProgress();
    },

    setVolume(value) {
      if (!this.audio) return;
      this.audio.volume = value;
      this.audio.muted = value == 0;
      this.state.isMuted = value == 0;
    },

    toggleMute() {
      this.audio.muted = !this.audio.muted;
      this.state.isMuted = this.audio.muted;
    },

    updateVolumeState() {
      const muted = this.audio.muted || this.audio.volume === 0;
      this.state.isMuted = muted;
      if (this.elements.volume) {
        this.elements.volume.style.setProperty('--range-progress', `${muted ? 0 : this.audio.volume * 100}%`);
      }
      if (this.elements.volumeToggle) {
        this.elements.volumeToggle.setAttribute('aria-label', muted ? 'Activar sonido' : 'Silenciar');
        this.elements.volumeToggle.setAttribute('aria-pressed', muted ? 'true' : 'false');
      }
    },

    expandPlayer() {
      document.body.classList.add('player-expanded');
      this.stickyPlayer.dataset.playerExpanded = 'true';
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
      this.adjustBodyOffset();
    },

    collapsePlayer() {
      document.body.classList.remove('player-expanded');
      this.stickyPlayer.dataset.playerExpanded = 'false';
      this.adjustBodyOffset();
    },

    formatTime(seconds) {
      if (!Number.isFinite(seconds) || seconds < 0) return '0:00';
      const minutes = Math.floor(seconds / 60);
      const secs = Math.floor(seconds % 60);
      return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
    },

    updateMetaField(nodes, value) {
      this.updateNodeList(nodes, (element) => {
        if (!element) return;
        if (value) {
          element.textContent = value;
          element.classList.remove('hidden');
        } else {
          element.textContent = '';
          element.classList.add('hidden');
        }
      });
    },

    adjustBodyOffset() {
      if (!this.stickyPlayer) return;
      const body = document.body;
      const height = this.stickyPlayer.offsetHeight || 0;

      if (height === 0 || this.stickyPlayer.classList.contains('hidden')) {
        body.classList.remove('player-visible-bottom', 'player-visible-top');
        body.style.removeProperty('--player-bottom-offset');
        body.style.removeProperty('--player-top-offset');
        return;
      }

      body.classList.add('player-visible-bottom');
      body.classList.remove('player-visible-top');
      body.style.setProperty('--player-bottom-offset', `${height}px`);
      body.style.removeProperty('--player-top-offset');
    },

    updateNodeList(nodeList, callback) {
      if (!nodeList) return;
      nodeList.forEach((node) => callback(node));
    },
  };

  playerManager.init();
});






