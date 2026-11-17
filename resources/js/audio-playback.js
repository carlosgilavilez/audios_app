// Keep one media request active and recover once if playback makes no progress.
export class AudioPlayback {
  constructor(audio, { onStatus = () => {}, onIntent = () => {}, recoveryDelay = 12000,
    schedule = (fn, delay) => setTimeout(fn, delay), cancel = id => clearTimeout(id) } = {}) {
    this.audio = audio;
    this.onStatus = onStatus;
    this.onIntent = onIntent;
    this.recoveryDelay = recoveryDelay;
    this.schedule = schedule;
    this.cancel = cancel;
    this.wantsToPlay = false;
    this.generation = 0;
    this.retries = 0;
    this.timer = null;
    this.restoreTime = null;
    this.loadingSource = false;
    this.failed = false;
    this.lastTime = 0;

    audio.addEventListener('waiting', () => this.wait());
    audio.addEventListener('seeking', () => this.wait());
    audio.addEventListener('stalled', () => {
      if (audio.readyState < 3) this.wait();
    });
    audio.addEventListener('loadedmetadata', () => {
      this.loadingSource = false;
      if (this.restoreTime === null) return;
      const target = this.restoreTime;
      this.restoreTime = null;
      audio.currentTime = Math.max(0, Math.min(target, audio.duration || target));
      this.lastTime = audio.currentTime;
      if (this.wantsToPlay) this.requestPlay();
    });
    for (const event of ['canplay', 'playing', 'seeked']) {
      audio.addEventListener(event, () => {
        if (!audio.seeking && audio.readyState >= 3) this.onStatus('', false);
      });
    }
    audio.addEventListener('timeupdate', () => {
      if (!audio.seeking && audio.readyState >= 3 && audio.currentTime > this.lastTime + 0.05) {
        this.lastTime = audio.currentTime;
        this.clearTimer();
        this.onStatus('', false);
      }
    });
    audio.addEventListener('error', () => {
      if (this.wantsToPlay && audio.error?.code === 2 && this.retries === 0) this.recover();
      else this.fail('No se pudo cargar el audio. Pulsa Reproducir para reintentar.');
    });
    audio.addEventListener('ended', () => this.clearTimer());
    audio.addEventListener('pause', () => {
      if (this.loadingSource || audio.ended) return;
      this.setIntent(false);
      this.clearTimer();
    });
  }

  load(src, { saveData = false } = {}) {
    this.clearTimer();
    this.generation += 1;
    this.retries = 0;
    this.failed = false;
    this.restoreTime = null;
    this.lastTime = 0;
    this.loadingSource = true;
    this.audio.preload = saveData ? 'metadata' : 'auto';
    this.audio.src = src;
    this.setIntent(true);
    this.onStatus('Cargando audio…', true);
    this.requestPlay();
    this.arm();
  }

  play() {
    this.setIntent(true);
    if (this.failed || this.audio.error) {
      this.retries = 0;
      this.failed = false;
      this.recover();
    } else {
      this.lastTime = this.audio.currentTime;
      this.requestPlay();
      this.arm();
    }
  }

  pause() {
    this.setIntent(false);
    this.clearTimer();
    this.audio.pause();
  }

  seek(target) {
    if (!Number.isFinite(this.audio.duration) || this.audio.duration <= 0 || !Number.isFinite(target)) return;
    this.clearTimer();
    this.retries = 0;
    this.restoreTime = null;
    this.lastTime = Math.max(0, Math.min(target, this.audio.duration));
    this.audio.currentTime = this.lastTime;
    this.wait();
  }

  wait() {
    if (this.failed) return;
    this.onStatus(this.audio.seeking ? 'Cargando el punto elegido…' : 'Cargando audio…', true);
    this.arm();
  }

  arm() {
    if (!this.wantsToPlay || this.timer !== null) return;
    const generation = this.generation;
    this.timer = this.schedule(() => {
      this.timer = null;
      if (generation !== this.generation || !this.wantsToPlay) return;
      if (this.retries === 0) this.recover();
      else this.fail('La carga está tardando demasiado. Pulsa Reproducir para reintentar.');
    }, this.recoveryDelay);
  }

  recover() {
    this.clearTimer();
    this.retries += 1;
    this.generation += 1;
    this.restoreTime = this.audio.currentTime || this.lastTime;
    this.loadingSource = true;
    this.onStatus('Reintentando cargar el audio…', true);
    // Abort the obsolete range request; metadata restores this same position.
    this.audio.load();
    this.arm();
  }

  requestPlay() {
    const generation = this.generation;
    this.audio.play().catch((error) => {
      if (generation !== this.generation || error.name === 'AbortError' || !this.wantsToPlay) return;
      this.fail(error.name === 'NotAllowedError'
        ? 'Pulsa Reproducir para continuar.'
        : 'No se pudo iniciar el audio. Pulsa Reproducir para reintentar.');
    });
  }

  fail(message) {
    this.clearTimer();
    this.failed = true;
    this.setIntent(false);
    this.audio.pause();
    this.onStatus(message, false);
  }

  setIntent(value) {
    this.wantsToPlay = value;
    this.onIntent(value);
  }

  clearTimer() {
    if (this.timer !== null) this.cancel(this.timer);
    this.timer = null;
  }
}
