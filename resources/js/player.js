document.addEventListener('DOMContentLoaded', function () {
    const playerManager = {
        audio: document.getElementById('pl-audio'),
        stickyPlayer: document.getElementById('sticky-player'),
        
        elements: {
            play: document.querySelectorAll('[id^="pl-play"]'),
            playIcon: document.querySelectorAll('[id^="pl-play-icon"]'),
            pauseIcon: document.querySelectorAll('[id^="pl-pause-icon"]'),
            prev: document.querySelectorAll('[id^="pl-prev"]'),
            next: document.querySelectorAll('[id^="pl-next"]'),
            back10: document.querySelectorAll('[id^="pl-back-10"]'),
            forward10: document.querySelectorAll('[id^="pl-forward-10"]'),
            title: document.querySelectorAll('[id^="pl-title"]'),
            author: document.querySelectorAll('[id^="pl-author"]'),
            current: document.querySelectorAll('[id^="pl-current"]'),
            duration: document.querySelectorAll('[id^="pl-duration"]'),
            seek: document.querySelectorAll('[id^="pl-seek"]'),
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
        },

        init() {
            if (!this.audio) {
                console.error('Audio element #pl-audio not found.');
                return;
            }
            this.collectPlaylist();
            this.bindEventListeners();
        },

        collectPlaylist() {
            const trackButtons = document.querySelectorAll('.btn-play[data-audio-src]');
            this.state.playlist = Array.from(trackButtons).map((button, index) => ({
                src: button.dataset.audioSrc,
                title: button.dataset.title || 'Pista sin título',
                author: button.dataset.author || 'Autor desconocido',
                download: button.dataset.download,
                button: button
            }));
        },

        bindEventListeners() {
            this.state.playlist.forEach((track, index) => {
                track.button.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (this.state.currentIndex === index) {
                        this.togglePlayPause();
                    } else {
                        this.playTrack(index);
                    }
                });
            });

            this.elements.play.forEach(btn => btn.addEventListener('click', () => this.togglePlayPause()));
            this.elements.next.forEach(btn => btn.addEventListener('click', () => this.playNext()));
            this.elements.prev.forEach(btn => btn.addEventListener('click', () => this.playPrev()));
            this.elements.back10.forEach(btn => btn.addEventListener('click', () => this.audio.currentTime -= 10));
            this.elements.forward10.forEach(btn => btn.addEventListener('click', () => this.audio.currentTime += 10));

            this.audio.addEventListener('timeupdate', () => this.updateProgress());
            this.audio.addEventListener('loadedmetadata', () => this.updateDuration());
            this.audio.addEventListener('ended', () => this.playNext());
            this.audio.addEventListener('play', () => this.updatePlayState(true));
            this.audio.addEventListener('pause', () => this.updatePlayState(false));

            this.elements.seek.forEach(bar => bar.addEventListener('input', (e) => this.seek(e.target.value)));
            this.elements.volume.addEventListener('input', (e) => this.setVolume(e.target.value));
            this.elements.volumeToggle.addEventListener('click', () => this.toggleMute());

            // Mobile expand/collapse
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

            this.audio.src = track.src;
            this.updateMetadata(track);
            
            this.audio.play().catch(e => console.error('Error playing audio:', e));

            if (this.stickyPlayer.classList.contains('hidden')) {
                this.stickyPlayer.classList.remove('hidden', 'opacity-0', 'translate-y-2');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        },
        
        updatePlayState(isPlaying) {
            this.state.isPlaying = isPlaying;
            this.updateNodeList(this.elements.playIcon, el => el.classList.toggle('hidden', isPlaying));
            this.updateNodeList(this.elements.pauseIcon, el => el.classList.toggle('hidden', !isPlaying));
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
            const playIcon = button.querySelector('.icon-play');
            const eqIcon = button.querySelector('.icon-eq');
            if (playIcon) playIcon.classList.toggle('hidden', isPlaying);
            if (eqIcon) eqIcon.classList.toggle('hidden', !isPlaying);
        },

        updateMetadata(track) {
            this.updateNodeList(this.elements.title, el => el.textContent = track.title);
            this.updateNodeList(this.elements.author, el => el.textContent = track.author);
            this.updateNodeList(this.elements.download, el => {
                if (track.download) {
                    el.href = track.download;
                    el.style.display = 'inline-flex';
                } else {
                    el.style.display = 'none';
                }
            });
        },

        togglePlayPause() {
            if (this.state.isPlaying) {
                this.audio.pause();
            } else {
                if (this.state.currentIndex === -1) {
                    this.playTrack(0);
                } else {
                    this.audio.play().catch(e => console.error('Error resuming audio:', e));
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
            if (isNaN(this.audio.duration)) return;
            const progress = (this.audio.currentTime / this.audio.duration) * 100;
            this.updateNodeList(this.elements.seek, el => el.value = progress);
            this.updateNodeList(this.elements.current, el => el.textContent = this.formatTime(this.audio.currentTime));
        },

        updateDuration() {
            this.updateNodeList(this.elements.duration, el => el.textContent = this.formatTime(this.audio.duration));
        },

        seek(value) {
            if (isNaN(this.audio.duration)) return;
            this.audio.currentTime = (this.audio.duration / 100) * value;
        },

        setVolume(value) {
            this.audio.volume = value;
            this.audio.muted = value == 0;
            this.state.isMuted = value == 0;
        },
        
        toggleMute() {
            this.audio.muted = !this.audio.muted;
            this.state.isMuted = this.audio.muted;
        },

        expandPlayer() {
            document.body.classList.add('player-expanded');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        collapsePlayer() {
            document.body.classList.remove('player-expanded');
        },

        formatTime(seconds) {
            const min = Math.floor(seconds / 60);
            const sec = Math.floor(seconds % 60);
            return `${min}:${sec < 10 ? '0' : ''}${sec}`;
        },

        updateNodeList(nodeList, callback) {
            nodeList.forEach(node => callback(node));
        }
    };

    playerManager.init();
});