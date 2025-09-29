document.addEventListener('DOMContentLoaded', function () {
    const audio = document.getElementById('pl-audio');
    if (!audio) {
        console.error('Audio element with id "pl-audio" not found.');
        return;
    }

    const buttons = document.querySelectorAll('.btn-play[data-audio-src]');
    buttons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const src = button.dataset.audioSrc;
            if (src) {
                console.log('Playing audio from: ' + src);
                audio.src = src;
                audio.play().catch(e => console.error('Error playing audio:', e));
            } else {
                console.error('No audio source found for this button.');
            }
        });
    });
});
