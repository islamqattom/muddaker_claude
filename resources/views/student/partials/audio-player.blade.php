<div class="card" style="background: linear-gradient(135deg, #182B48, #2A4570); color: #fff;">
    <div class="card-body py-2">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <button class="btn btn-light btn-sm" id="playPauseBtn" onclick="togglePlay()">
                    <i class="fas fa-play" id="playIcon"></i>
                </button>
            </div>
            <div class="col">
                <div class="d-flex align-items-center">
                    <span id="currentTime" class="me-2" style="font-size: 12px;">0:00</span>
                    <div class="progress flex-grow-1" style="height: 6px; cursor: pointer;" onclick="seekAudio(event)">
                        <div class="progress-bar bg-light" id="audioProgress" style="width: 0%;"></div>
                    </div>
                    <span id="duration" class="ms-2" style="font-size: 12px;">0:00</span>
                </div>
            </div>
            <div class="col-auto">
                <select class="form-select form-select-sm bg-transparent text-white border-0" id="speedSelect" onchange="changeSpeed(this.value)" style="width: auto;">
                    <option value="0.5">0.5x</option>
                    <option value="0.75">0.75x</option>
                    <option value="1" selected>1x</option>
                    <option value="1.25">1.25x</option>
                    <option value="1.5">1.5x</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-light" onclick="restartPage()">
                    <i class="fas fa-redo"></i>
                </button>
            </div>
        </div>
        <div class="mt-2 text-center">
            <small class="opacity-75" id="nowPlaying">اختر أي آية للاستماع</small>
        </div>
    </div>
</div>

<audio id="quranAudio" style="display: none;"></audio>

<script>
(function() {
    let isPlaying = false;
    let autoScroll = true;
    let currentAudio = null;

    function initAudioPlayer() {
        const autoScrollToggle = document.getElementById('autoScrollToggle');
        if (autoScrollToggle) {
            autoScrollToggle.addEventListener('change', function() {
                autoScroll = this.checked;
            });
        }

        // Click on verses to play
        document.querySelectorAll('.verse-element').forEach(el => {
            el.style.cursor = 'pointer';
            el.addEventListener('click', function() {
                const surahNum = parseInt(this.dataset.surah);
                const verseNum = parseInt(this.dataset.verse);
                playVerse(surahNum, verseNum);
                document.querySelectorAll('.verse-element').forEach(e => e.classList.remove('playing'));
                this.classList.add('playing');
            });
        });
    }

    function setupAudioEvents(audio) {
        audio.addEventListener('loadedmetadata', function() {
            const durationEl = document.getElementById('duration');
            if (durationEl) durationEl.textContent = formatTime(audio.duration);
        });

        audio.addEventListener('timeupdate', function() {
            const currentTimeEl = document.getElementById('currentTime');
            const progressEl = document.getElementById('audioProgress');
            if (currentTimeEl) currentTimeEl.textContent = formatTime(audio.currentTime);
            if (progressEl && audio.duration) {
                progressEl.style.width = ((audio.currentTime / audio.duration) * 100) + '%';
            }
        });

        audio.addEventListener('ended', function() {
            playNextVerse();
        });
    }

    function togglePlay() {
        if (!currentAudio) return;
        if (isPlaying) {
            currentAudio.pause();
            const iconEl = document.getElementById('playIcon');
            if (iconEl) iconEl.className = 'fas fa-play';
        } else {
            currentAudio.play();
            const iconEl = document.getElementById('playIcon');
            if (iconEl) iconEl.className = 'fas fa-pause';
        }
        isPlaying = !isPlaying;
    }

    function playVerse(surahNumber, verseNumber) {
        const pageContainer = document.getElementById('pageContainer');
        const audioUrls = pageContainer ? JSON.parse(pageContainer.dataset.audioUrls || '{}') : {};
        const key = surahNumber + ':' + verseNumber;
        const audioUrl = audioUrls[key];
        if (!audioUrl) return;

        if (currentAudio) currentAudio.pause();
        currentAudio = new Audio(audioUrl);
        setupAudioEvents(currentAudio);
        currentAudio.play();
        isPlaying = true;

        const iconEl = document.getElementById('playIcon');
        const nowPlayingEl = document.getElementById('nowPlaying');
        if (iconEl) iconEl.className = 'fas fa-pause';
        if (nowPlayingEl) nowPlayingEl.textContent = 'الآية ' + verseNumber;

        const verseEl = document.querySelector('.verse-element[data-surah="' + surahNumber + '"][data-verse="' + verseNumber + '"]');
        if (verseEl && autoScroll) {
            verseEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function playNextVerse() {
        const currentEl = document.querySelector('.verse-element.playing');
        if (currentEl) {
            const nextEl = currentEl.nextElementSibling;
            if (nextEl && nextEl.classList.contains('verse-element')) {
                const surahNum = parseInt(nextEl.dataset.surah);
                const verseNum = parseInt(nextEl.dataset.verse);
                nextEl.classList.add('playing');
                currentEl.classList.remove('playing');
                playVerse(surahNum, verseNum);
            }
        }
    }

    function restartPage() {
        window.location.reload();
    }

    function changeSpeed(speed) {
        if (currentAudio) currentAudio.playbackRate = parseFloat(speed);
    }

    function formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    // Page navigation functions
    window.jumpToJuz = function(juz) {
        if (juz) {
            const partPages = {
                1: 1, 2: 22, 3: 42, 4: 63, 5: 83, 6: 104, 7: 125, 8: 145, 9: 165, 10: 186,
                11: 206, 12: 227, 13: 247, 14: 267, 15: 286, 16: 307, 17: 326, 18: 346, 19: 365, 20: 385,
                21: 405, 22: 425, 23: 445, 24: 463, 25: 482, 26: 502, 27: 521, 28: 540, 29: 558, 30: 578
            };
            window.location.href = '/student/quran/page/' + (partPages[juz] || 1);
        }
    };

    window.jumpToPage = function() {
        const page = document.getElementById('pageInput').value;
        if (page >= 1 && page <= 604) {
            window.location.href = '/student/quran/page/' + page;
        }
    };

    window.openBookmarkModal = function(pageNumber) {
        const note = prompt('أضف ملاحظة (اختياري):');
        if (note !== null) {
            fetch('/student/bookmarks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    page_number: pageNumber,
                    note: note || null
                })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    alert('تم إضافة العلامة بنجاح');
                }
            })
            .catch(() => {
                alert('حدث خطأ أثناء إضافة العلامة');
            });
        }
    };

    // Expose functions globally
    window.togglePlay = togglePlay;
    window.playVerse = playVerse;
    window.restartPage = restartPage;
    window.changeSpeed = changeSpeed;

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAudioPlayer);
    } else {
        initAudioPlayer();
    }
})();
</script>

<style>
.verse-element.playing {
    background-color: rgba(39, 174, 96, 0.2);
    border-radius: 4px;
}
.verse-element:hover {
    background-color: rgba(39, 174, 96, 0.1);
}
</style>
