import { test } from 'node:test';
import assert from 'node:assert/strict';
import { AudioPlayback } from '../../resources/js/audio-playback.js';

class Media extends EventTarget {
  currentTime = 0;
  duration = 3600;
  readyState = 1;
  seeking = false;
  paused = true;
  error = null;
  loads = 0;
  plays = 0;
  play() { this.plays++; this.paused = false; return Promise.resolve(); }
  pause() { this.paused = true; }
  load() { this.loads++; this.currentTime = 0; this.readyState = 0; this.paused = true; }
  emit(name) { this.dispatchEvent(new Event(name)); }
}

function fixture() {
  const media = new Media();
  const jobs = new Map();
  const status = [];
  let next = 0;
  const playback = new AudioPlayback(media, {
    schedule: fn => { const id = ++next; jobs.set(id, fn); return id; },
    cancel: id => jobs.delete(id),
    onStatus: (message, busy) => status.push({ message, busy }),
  });
  const expire = () => { const pending = [...jobs.entries()]; jobs.clear(); pending.forEach(([, fn]) => fn()); };
  return { media, playback, status, jobs, expire };
}

test('preloads only the selected source and respects data saving', () => {
  const { media, playback } = fixture();
  playback.load('/play-audio/1');
  assert.equal(media.src, '/play-audio/1');
  assert.equal(media.preload, 'auto');
  playback.load('/play-audio/2', { saveData: true });
  assert.equal(media.src, '/play-audio/2');
  assert.equal(media.preload, 'metadata');
  playback.pause();
});

test('a paused seek preserves pause and does not start automatic reloads', () => {
  const { media, playback, expire, jobs } = fixture();
  playback.load('/play-audio/1');
  playback.pause();
  playback.seek(1800);
  expire();
  assert.equal(media.currentTime, 1800);
  assert.equal(media.paused, true);
  assert.equal(media.plays, 1);
  assert.equal(media.loads, 0);
  assert.equal(jobs.size, 0);
});

test('a stalled seek reloads once and restores the selected time after metadata', () => {
  const { media, playback, expire, jobs } = fixture();
  playback.load('/play-audio/1');
  playback.seek(2340);
  expire();
  assert.equal(media.loads, 1);
  assert.equal(media.currentTime, 0);
  media.readyState = 1;
  media.emit('loadedmetadata');
  assert.equal(media.currentTime, 2340);
  assert.equal(media.paused, false);
  media.readyState = 4;
  media.currentTime = 2340.5;
  media.emit('timeupdate');
  assert.equal(jobs.size, 0);
  expire();
  assert.equal(media.loads, 1);
  playback.pause();
});

test('exhausted recovery stops instead of creating a reload loop', () => {
  const { media, playback, expire, status, jobs } = fixture();
  playback.load('/play-audio/1');
  playback.seek(2340);
  expire();
  expire();
  expire();
  assert.equal(media.loads, 1);
  assert.equal(media.paused, true);
  assert.equal(playback.wantsToPlay, false);
  assert.equal(jobs.size, 0);
  assert.match(status.at(-1).message, /Reproducir/);
  assert.equal(status.at(-1).busy, false);
});

test('pausing during recovery prevents an unexpected resume after metadata', () => {
  const { media, playback, expire } = fixture();
  playback.load('/play-audio/1');
  playback.seek(900);
  expire();
  playback.pause();
  media.emit('loadedmetadata');
  expire();
  assert.equal(media.currentTime, 900);
  assert.equal(media.paused, true);
  assert.equal(media.plays, 1);
});

test('changing track discards the previous restore position and timeout', () => {
  const { media, playback, expire } = fixture();
  playback.load('/play-audio/1');
  playback.seek(900);
  expire();
  playback.load('/play-audio/2');
  media.emit('loadedmetadata');
  assert.equal(media.src, '/play-audio/2');
  assert.equal(media.currentTime, 0);
  assert.equal(playback.retries, 0);
  playback.pause();
  expire();
  assert.equal(media.loads, 1);
});

test('seeked alone does not claim playback progress and disable the watchdog', () => {
  const { media, playback, expire } = fixture();
  playback.load('/play-audio/1');
  playback.seek(1500);
  media.readyState = 4;
  media.emit('seeked');
  expire();
  assert.equal(media.loads, 1);
  playback.pause();
});

test('blocked playback offers a user action and does not keep trying', async () => {
  const { media, playback, status, expire } = fixture();
  media.play = () => Promise.reject(Object.assign(new Error('blocked'), { name: 'NotAllowedError' }));
  playback.load('/play-audio/1');
  await Promise.resolve();
  expire();
  assert.equal(playback.wantsToPlay, false);
  assert.equal(media.loads, 0);
  assert.match(status.at(-1).message, /Pulsa Reproducir/);
});

test('an obsolete play rejection cannot stop the next selected track', async () => {
  const { media, playback } = fixture();
  let rejectOld;
  media.play = () => new Promise((_, reject) => { rejectOld = reject; });
  playback.load('/play-audio/1');
  media.play = () => Promise.resolve();
  playback.load('/play-audio/2');
  rejectOld(new Error('old request failed'));
  await Promise.resolve();
  assert.equal(playback.wantsToPlay, true);
  assert.equal(playback.failed, false);
  playback.pause();
});

test('rejects invalid targets and clamps finite seek positions to the audio', () => {
  const { media, playback } = fixture();
  playback.seek(Number.NaN);
  assert.equal(media.currentTime, 0);
  playback.seek(-10);
  assert.equal(media.currentTime, 0);
  playback.seek(4000);
  assert.equal(media.currentTime, 3600);
});

test('an operating-system pause is respected after normal loading', () => {
  const { media, playback, expire } = fixture();
  playback.load('/play-audio/1');
  media.emit('loadedmetadata');
  media.pause();
  media.emit('pause');
  expire();
  assert.equal(playback.wantsToPlay, false);
  assert.equal(media.loads, 0);
});

test('the internal pause caused by a recovery load does not cancel the intended resume', () => {
  const { media, playback, expire } = fixture();
  playback.load('/play-audio/1');
  media.emit('loadedmetadata');
  playback.seek(1200);
  expire();
  media.emit('pause');
  assert.equal(playback.wantsToPlay, true);
  media.emit('loadedmetadata');
  assert.equal(media.currentTime, 1200);
  assert.equal(media.paused, false);
  playback.pause();
});
