/**
 * Quran Reader JavaScript Module
 * Handles audio playback, auto-scroll, and verse interactions
 */

class QuranReader {
    constructor() {
        this.audio = document.getElementById('quranAudio');
        this.autoScroll = true;
        this.currentVerse = 0;
        this.verseElements = document.querySelectorAll('.verse-element');
        this.init();
    }

    init() {
        if (!this.audio) return;

        const autoScrollToggle = document.getElementById('autoScrollToggle');
        if (autoScrollToggle) {
            autoScrollToggle.addEventListener('change', (e) => {
                this.autoScroll = e.target.checked;
            });
        }

        this.audio.addEventListener('timeupdate', () => this.onTimeUpdate());
        this.audio.addEventListener('ended', () => this.onAudioEnded());

        this.verseElements.forEach(el => {
            el.style.cursor = 'pointer';
            el.addEventListener('click', () => {
                const verseNum = el.dataset.verse;
                if (verseNum) {
                    this.playVerse(verseNum);
                    el.classList.add('playing');
                }
            });
        });
    }

    onTimeUpdate() {
        if (!this.autoScroll || !this.audio) return;

        const container = document.getElementById('pageContainer');
        if (!container) return;

        const verseEls = document.querySelectorAll('.verse-element');
        const currentTime = this.audio.currentTime;

        for (const verse of verseEls) {
            const verseNum = parseInt(verse.dataset.verse);
            const startTime = this.getVerseStartTime(verseNum);
            const endTime = this.getVerseEndTime(verseNum);

            if (currentTime >= startTime && currentTime < endTime) {
                this.scrollToVerse(verse);
                this.highlightVerse(verse);
                break;
            }
        }
    }

    onAudioEnded() {
        this.playNextVerse();
    }

    scrollToVerse(verseElement) {
        const container = document.getElementById('pageContainer');
        if (!container || !verseElement) return;

        const verseTop = verseElement.offsetTop;
        const containerHeight = container.clientHeight;
        const scrollTarget = verseTop - (containerHeight / 3);

        container.scrollTo({
            top: scrollTarget,
            behavior: 'smooth'
        });
    }

    highlightVerse(verseElement) {
        document.querySelectorAll('.verse-element').forEach(el => {
            el.classList.remove('playing');
        });
        if (verseElement) {
            verseElement.classList.add('playing');
        }
    }

    playVerse(verseNumber) {
        const audioUrls = window.audioUrls || {};
        const key = Object.keys(audioUrls).find(k => k.endsWith(':' + verseNumber));

        if (!key || !audioUrls[key]) return;

        if (this.audio) {
            this.audio.pause();
            this.audio.src = audioUrls[key];
            this.audio.playbackRate = this.getPlaybackSpeed();
            this.audio.play();
            this.updateNowPlaying(verseNumber);
            this.highlightVerse(document.getElementById('verse-' + verseNumber));
        }
    }

    playNextVerse() {
        const currentEl = document.querySelector('.verse-element.playing');
        if (currentEl) {
            const nextEl = currentEl.nextElementSibling;
            if (nextEl && nextEl.classList.contains('verse-element')) {
                const verseNum = nextEl.dataset.verse;
                this.playVerse(verseNum);
            }
        }
    }

    getPlaybackSpeed() {
        const speedSelect = document.getElementById('speedSelect');
        return speedSelect ? parseFloat(speedSelect.value) : 1.0;
    }

    updateNowPlaying(verseNumber) {
        const nowPlaying = document.getElementById('nowPlaying');
        if (nowPlaying) {
            nowPlaying.textContent = 'الآية ' + verseNumber;
        }
    }

    getVerseStartTime(verseNumber) {
        // Approximate timing based on typical recitation pace
        // In a real implementation, this would come from metadata
        return (verseNumber - 1) * 15;
    }

    getVerseEndTime(verseNumber) {
        return verseNumber * 15;
    }

    togglePlay() {
        if (!this.audio) return;

        if (this.audio.paused) {
            this.audio.play();
        } else {
            this.audio.pause();
        }
    }

    setSpeed(speed) {
        if (this.audio) {
            this.audio.playbackRate = parseFloat(speed);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.quranReader = new QuranReader();
});
