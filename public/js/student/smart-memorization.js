/**
 * ============================================================
 * Smart Memorization Controller (التسميع الذكي والمتابعة الصوتية)
 * ============================================================
 * A word-by-word Quran memorization assistant inspired by Tarteel app.
 * Features:
 *  - Hide & Reveal: Words are hidden until correctly pronounced
 *  - Web Speech API integration for Arabic speech recognition
 *  - Sequential word matching with smart normalization
 *  - Error detection with visual feedback (red flash)
 *  - Silence detection (4s) with hint button for next word
 *  - Responsive design for mobile and desktop
 * ============================================================
 */

(function() {
    'use strict';

    /**
     * SmartMemorizationController
     * Main class that manages the smart memorization session.
     */
    class SmartMemorizationController {
        constructor() {
            // ========== Session State ==========
            this.isSessionActive = false;
            this.isRecording = false;
            this.words = [];              // Array of word objects
            this.currentWordIndex = 0;    // Index of next expected word
            this.verses = [];             // Raw verses from API
            this.speechRecognition = null;
            this.silenceTimer = null;     // Timer for detecting pause
            this.silenceDelay = 4000;     // 4 seconds before hint appears
            this.lastSpeechTimestamp = 0;
            this.apiToken = '';
            this.csrfToken = '';
            this.previewUrl = '';
            this.runningTranscript = '';  // Accumulated final speech results
            this.lastProcessedSpokenCount = 0; // How many spoken words have been processed so far
            this.errorAudio = this.buildErrorAudio();

            // ========== DOM Element References ==========
            this.initDOMReferences();

            // ========== Initialization ==========
            this.readConfigFromMetaTags();
            this.initSpeechRecognition();
            this.bindEventListeners();
            this.populateSurahSelect();
        }

        // ==========================================================
        // SECTION 1: DOM & Configuration
        // ==========================================================

        /**
         * Cache all DOM element references for performance.
         */
        initDOMReferences() {
            this.cardEl = document.getElementById('smartMemorizationCard');
            this.statusBadgeEl = document.getElementById('smartMemoStatus');
            this.surahSelectEl = document.getElementById('smartMemoSurah');
            this.fromInputEl = document.getElementById('smartMemoFrom');
            this.toInputEl = document.getElementById('smartMemoTo');
            this.loadBtnEl = document.getElementById('smartMemoLoadBtn');
            this.startBtnEl = document.getElementById('smartMemoStartBtn');
            this.stopBtnEl = document.getElementById('smartMemoStopBtn');
            this.hintBtnEl = document.getElementById('smartMemoHintBtn');
            this.textAreaEl = document.getElementById('smartMemoTextArea');
            this.listeningIndicatorEl = document.getElementById('smartMemoListeningIndicator');
            this.transcriptEl = document.getElementById('smartMemoTranscript');
            this.progressBarEl = document.getElementById('smartMemoProgressBar');
            this.progressTextEl = document.getElementById('smartMemoProgressText');
            this.toastContainerEl = document.getElementById('toastContainer');
        }

        /**
         * Read API config from meta tags (same pattern as LiveRecitationController).
         */
        readConfigFromMetaTags() {
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            this.apiToken = document.querySelector('meta[name="api-token"]')?.content || '';
            this.previewUrl = document.querySelector('meta[name="preview-url"]')?.content || '/api/student/live-recitation/preview';
        }

        // ==========================================================
        // SECTION 2: Speech Recognition Setup
        // ==========================================================

        /**
         * Initialize the Web Speech API (SpeechRecognition).
         * Sets up Arabic language, continuous mode, and result handlers.
         * Falls back gracefully if browser does not support it.
         */
        initSpeechRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

            if (!SpeechRecognition) {
                console.warn('[SmartMemorization] Web Speech API not supported in this browser.');
                return;
            }

            this.speechRecognition = new SpeechRecognition();
            this.speechRecognition.continuous = true;
            this.speechRecognition.interimResults = true;
            this.speechRecognition.lang = 'ar-SA';

            // On result: process transcript immediately
            this.speechRecognition.onresult = (event) => {
                this.handleSpeechResult(event);
            };

            // On error: log and show user-friendly message
            this.speechRecognition.onerror = (event) => {
                console.error('[SmartMemorization] Speech recognition error:', event.error);
                if (event.error === 'not-allowed') {
                    this.showToast('لم يتم السماح بالوصول إلى الميكروفون', 'error');
                    this.stopSession();
                } else if (event.error === 'no-speech') {
                    // no-speech is expected during pauses; restart automatically
                    this.attemptRestartRecognition();
                }
            };

            // On end: auto-restart if session is still active
            this.speechRecognition.onend = () => {
                if (this.isSessionActive && this.isRecording) {
                    this.attemptRestartRecognition();
                }
            };
        }

        /**
         * Attempt to restart speech recognition after unexpected stop.
         */
        attemptRestartRecognition() {
            if (!this.speechRecognition || !this.isSessionActive) return;
            try {
                this.speechRecognition.start();
            } catch (e) {
                // Already started or other minor error; ignore
            }
        }

        /**
         * Build a short error beep using Web Audio API.
         * No external files needed.
         */
        buildErrorAudio() {
            try {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            } catch (e) {
                return () => {}; // Silent fallback
            }
            return () => this._playErrorToneInternal();
        }

        /**
         * Internal: play the error tone through the cached AudioContext.
         */
        _playErrorToneInternal() {
            if (!this.audioCtx) return;
            // Browsers suspend AudioContext until first user gesture;
            // resume it on demand.
            if (this.audioCtx.state === 'suspended') {
                this.audioCtx.resume().catch(() => {});
            }
            const osc = this.audioCtx.createOscillator();
            const gain = this.audioCtx.createGain();
            osc.connect(gain);
            gain.connect(this.audioCtx.destination);
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(350, this.audioCtx.currentTime);
            gain.gain.setValueAtTime(0.25, this.audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.25);
            osc.start(this.audioCtx.currentTime);
            osc.stop(this.audioCtx.currentTime + 0.25);
        }

        /**
         * Play the error tone.
         */
        playErrorTone() {
            if (this.errorAudio) this.errorAudio();
        }

        // ==========================================================
        // SECTION 3: Event Listeners
        // ==========================================================

        /**
         * Bind all UI button click events.
         */
        bindEventListeners() {
            if (this.loadBtnEl) {
                this.loadBtnEl.addEventListener('click', () => this.loadVerses());
            }
            if (this.startBtnEl) {
                this.startBtnEl.addEventListener('click', () => this.startSession());
            }
            if (this.stopBtnEl) {
                this.stopBtnEl.addEventListener('click', () => this.stopSession());
            }
            if (this.hintBtnEl) {
                this.hintBtnEl.addEventListener('click', () => this.revealNextWordAsHint());
            }

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (!this.isSessionActive) return;
                if (e.key === 'Escape') {
                    this.stopSession();
                } else if (e.key === 'h' || e.key === 'H' || e.key === 'أ') {
                    this.revealNextWordAsHint();
                }
            });
        }

        // ==========================================================
        // SECTION 4: Data Loading (Verses)
        // ==========================================================

        /**
         * Populate the Surah dropdown with all 114 surahs.
         */
        populateSurahSelect() {
            if (!this.surahSelectEl) return;
            const surahs = [
                {n:1, name:'الفاتحة'},{n:2, name:'البقرة'},{n:3, name:'آل عمران'},{n:4, name:'النساء'},
                {n:5, name:'المائدة'},{n:6, name:'الأنعام'},{n:7, name:'الأعراف'},{n:8, name:'الأنفال'},
                {n:9, name:'التوبة'},{n:10, name:'يونس'},{n:11, name:'هود'},{n:12, name:'يوسف'},
                {n:13, name:'الرعد'},{n:14, name:'إبراهيم'},{n:15, name:'الحجر'},{n:16, name:'النحل'},
                {n:17, name:'الإسراء'},{n:18, name:'الكهف'},{n:19, name:'مريم'},{n:20, name:'طه'},
                {n:21, name:'الأنبياء'},{n:22, name:'الحج'},{n:23, name:'المؤمنون'},{n:24, name:'النور'},
                {n:25, name:'الفرقان'},{n:26, name:'الشعراء'},{n:27, name:'النمل'},{n:28, name:'القصص'},
                {n:29, name:'العنكبوت'},{n:30, name:'الروم'},{n:31, name:'لقمان'},{n:32, name:'السجدة'},
                {n:33, name:'الأحزاب'},{n:34, name:'سبأ'},{n:35, name:'فاطر'},{n:36, name:'يس'},
                {n:37, name:'الصافات'},{n:38, name:'ص'},{n:39, name:'الزمر'},{n:40, name:'غافر'},
                {n:41, name:'فصلت'},{n:42, name:'الشورى'},{n:43, name:'الزخرف'},{n:44, name:'الدخان'},
                {n:45, name:'الجاثية'},{n:46, name:'الأحقاف'},{n:47, name:'محمد'},{n:48, name:'الفتح'},
                {n:49, name:'الحجرات'},{n:50, name:'ق'},{n:51, name:'الذاريات'},{n:52, name:'الطور'},
                {n:53, name:'النجم'},{n:54, name:'القمر'},{n:55, name:'الرحمن'},{n:56, name:'الواقعة'},
                {n:57, name:'الحديد'},{n:58, name:'المجادلة'},{n:59, name:'الحشر'},{n:60, name:'الممتحنة'},
                {n:61, name:'الصف'},{n:62, name:'الجمعة'},{n:63, name:'المنافقون'},{n:64, name:'التغابن'},
                {n:65, name:'الطلاق'},{n:66, name:'التحريم'},{n:67, name:'الملك'},{n:68, name:'القلم'},
                {n:69, name:'الحاقة'},{n:70, name:'المعارج'},{n:71, name:'نوح'},{n:72, name:'الجن'},
                {n:73, name:'المزمل'},{n:74, name:'المدثر'},{n:75, name:'القيامة'},{n:76, name:'الإنسان'},
                {n:77, name:'المرسلات'},{n:78, name:'النبأ'},{n:79, name:'النازعات'},{n:80, name:'عبس'},
                {n:81, name:'التكوير'},{n:82, name:'الإنفطار'},{n:83, name:'المطففين'},{n:84, name:'الإنشقاق'},
                {n:85, name:'البروج'},{n:86, name:'الطارق'},{n:87, name:'الأعلى'},{n:88, name:'الغاشية'},
                {n:89, name:'الفجر'},{n:90, name:'البلد'},{n:91, name:'الشمس'},{n:92, name:'الليل'},
                {n:93, name:'الضحى'},{n:94, name:'الشرح'},{n:95, name:'التين'},{n:96, name:'العلق'},
                {n:97, name:'القدر'},{n:98, name:'البينة'},{n:99, name:'الزلزلة'},{n:100, name:'العاديات'},
                {n:101, name:'القارعة'},{n:102, name:'التكاثر'},{n:103, name:'العصر'},{n:104, name:'الهمزة'},
                {n:105, name:'الفيل'},{n:106, name:'قريش'},{n:107, name:'الماعون'},{n:108, name:'الكوثر'},
                {n:109, name:'الكافرون'},{n:110, name:'النصر'},{n:111, name:'المسد'},{n:112, name:'الإخلاص'},
                {n:113, name:'الفلق'},{n:114, name:'الناس'}
            ];

            surahs.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.n;
                opt.textContent = s.n + '. ' + s.name;
                this.surahSelectEl.appendChild(opt);
            });
        }

        /**
         * Fetch verses from the server using the existing preview API endpoint.
         * Splits verses into words and renders the hidden text.
         */
        async loadVerses() {
            const surah = parseInt(this.surahSelectEl.value);
            const verseStart = parseInt(this.fromInputEl.value);
            const verseEnd = parseInt(this.toInputEl.value);

            if (!surah || isNaN(verseStart) || verseStart < 1) {
                this.showToast('يرجى اختيار السورة وآية البداية', 'error');
                return;
            }
            if (isNaN(verseEnd) || verseEnd < verseStart) {
                this.showToast('رقم آية النهاية يجب أن يكون أكبر من أو يساوي آية البداية', 'error');
                return;
            }

            // Limit range to prevent overload
            if (verseEnd - verseStart > 50) {
                this.showToast('يمكن اختيار 50 آية كحد أقصى في الوضع الذكي', 'error');
                return;
            }

            this.setLoadingState(true);

            // Ensure we have an API token
            if (!this.apiToken) {
                await this.fetchApiToken();
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            };
            if (this.apiToken) {
                headers['Authorization'] = 'Bearer ' + this.apiToken;
            }

            try {
                const response = await fetch(this.previewUrl, {
                    method: 'POST',
                    headers: headers,
                    credentials: 'include',
                    body: JSON.stringify({
                        mode: 'surah',
                        surah: surah,
                        verse_start: verseStart,
                        verse_end: verseEnd
                    })
                });

                const data = await response.json();

                if (!data.success || !data.data || !data.data.verses || data.data.verses.length === 0) {
                    this.showToast(data.error || 'لم يتم العثور على آيات', 'error');
                    this.setLoadingState(false);
                    return;
                }

                this.verses = data.data.verses;
                this.buildWordsArray();
                // Show all words in preview mode (visible) before starting memorization
                this.words.forEach(w => w.revealed = true);
                this.renderWords();
                this.updateProgressUI();
                this.startBtnEl.disabled = false;
                this.showToast('تم تحميل الآيات - اضغط "بدء التسميع الذكي" لإخفاء النص', 'success');
            } catch (err) {
                console.error('[SmartMemorization] Load verses error:', err);
                this.showToast('حدث خطأ أثناء تحميل الآيات', 'error');
            } finally {
                this.setLoadingState(false);
            }
        }

        /**
         * Fallback token fetch if meta tag token is missing.
         */
        async fetchApiToken() {
            const tokenUrl = document.querySelector('meta[name="token-url"]')?.content;
            if (!tokenUrl) return;
            try {
                const res = await fetch(tokenUrl, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'include'
                });
                const data = await res.json();
                if (data.success && data.token) {
                    this.apiToken = data.token;
                }
            } catch (e) {
                console.warn('[SmartMemorization] Token fetch failed:', e);
            }
        }

        /**
         * Split loaded verses into individual word objects.
         * Each word keeps its original text, normalized form for matching,
         * and UI state (revealed, error).
         */
        buildWordsArray() {
            this.words = [];
            let globalIndex = 0;

            this.verses.forEach((verse, vIndex) => {
                if (!verse.text) return;
                // Split by whitespace and filter empty strings
                const verseWords = verse.text.trim().split(/\s+/);
                verseWords.forEach((w) => {
                    this.words.push({
                        id: globalIndex,
                        text: w,
                        normalized: this.normalizeArabic(w),
                        revealed: false,
                        error: false,
                        verseIndex: vIndex
                    });
                    globalIndex++;
                });
            });

            this.currentWordIndex = 0;
        }

        // ==========================================================
        // SECTION 5: Session Control (Start / Stop)
        // ==========================================================

        /**
         * Start the smart memorization session.
         * Activates recording, hides all unrevealed words, and starts silence detection.
         */
        startSession() {
            if (this.words.length === 0) {
                this.showToast('يرجى تحميل الآيات أولاً', 'error');
                return;
            }
            if (!this.speechRecognition) {
                this.showToast('المتصفح لا يدعم التعرف على الصوت. جرّب Chrome أو Edge.', 'error');
                return;
            }

            this.isSessionActive = true;
            this.isRecording = true;
            this.currentWordIndex = 0;
            this.runningTranscript = '';
            this.lastProcessedSpokenCount = 0;

            // Reset all words to hidden
            this.words.forEach(w => { w.revealed = false; w.error = false; });
            this.renderWords();
            this.updateProgressUI();

            // UI updates
            this.startBtnEl.classList.add('d-none');
            this.stopBtnEl.classList.remove('d-none');
            this.hintBtnEl.classList.add('d-none');
            this.statusBadgeEl.textContent = 'نشط';
            this.statusBadgeEl.className = 'badge bg-success';
            this.listeningIndicatorEl.classList.remove('d-none');
            this.textAreaEl.classList.add('smart-memo-active');

            // Start listening
            try {
                this.speechRecognition.start();
            } catch (e) {
                console.warn('[SmartMemorization] Recognition already started or error:', e);
            }

            this.resetSilenceTimer();
            this.showToast('بدأ التسميع الذكي - اقرأ بصوت واضح', 'success');
        }

        /**
         * Stop the session and release microphone resources.
         */
        stopSession() {
            this.isSessionActive = false;
            this.isRecording = false;
            this.clearSilenceTimer();

            if (this.speechRecognition) {
                try {
                    this.speechRecognition.stop();
                } catch (e) { /* ignore */ }
            }

            // UI updates
            this.startBtnEl.classList.remove('d-none');
            this.stopBtnEl.classList.add('d-none');
            this.hintBtnEl.classList.add('d-none');
            this.listeningIndicatorEl.classList.add('d-none');
            this.statusBadgeEl.textContent = 'غير نشط';
            this.statusBadgeEl.className = 'badge bg-secondary';
            this.textAreaEl.classList.remove('smart-memo-active');
            this.transcriptEl.textContent = '';

            // Reveal all words so user can review
            this.words.forEach(w => w.revealed = true);
            this.renderWords();
        }

        // ==========================================================
        // SECTION 6: Speech Result Handling
        // ==========================================================

        /**
         * Process incoming speech results from Web Speech API.
         * Processes ONLY the NEW final words incrementally (one-by-one),
         * never re-processing old speech. This prevents bulk-matching
         * that causes skipped words to be auto-revealed.
         *
         * @param {SpeechRecognitionEvent} event
         */
        handleSpeechResult(event) {
            this.resetSilenceTimer();

            let finalTranscript = '';
            let interimTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript;
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }

            // Show user what the browser thinks they said (live preview)
            this.updateTranscript(this.runningTranscript + interimTranscript);

            // Only process when we have final results
            if (finalTranscript.trim().length > 0) {
                this.runningTranscript += ' ' + finalTranscript;
                this.runningTranscript = this.runningTranscript.trim();
                this.processNewSpokenWordsIncremental();
            }
        }

        /**
         * Process only the NEW spoken words that arrived since last time.
         *
         * STRICT RULE:
         * - Only the CURRENT expected word can be revealed.
         * - If user says a different word (skipped ahead or wrong word):
         *   → play error sound + flash current expected word RED
         *   → do NOT reveal the spoken word
         *   → do NOT advance
         *   → user MUST say the current word correctly to proceed.
         */
        processNewSpokenWordsIncremental() {
            const allSpokenWords = this.runningTranscript
                .trim()
                .split(/\s+/)
                .map(w => this.normalizeArabic(w))
                .filter(w => w.length > 0);

            if (allSpokenWords.length === 0) return;
            if (allSpokenWords.length <= this.lastProcessedSpokenCount) return;

            // Process ONLY the newly arrived words
            for (let s = this.lastProcessedSpokenCount; s < allSpokenWords.length; s++) {
                const spoken = allSpokenWords[s];
                const match = this.findStrictWordMatch(spoken);

                if (match.correct) {
                    // ✅ Current word pronounced correctly
                    this.words[this.currentWordIndex].revealed = true;
                    this.words[this.currentWordIndex].error = false;
                    this.currentWordIndex++;
                    this.renderWords();
                } else {
                    // ❌ Wrong word or skipped ahead
                    // Play error sound
                    this.playErrorTone();
                    // Flash current expected word in red
                    this.flashErrorWord(this.currentWordIndex);
                }

                this.updateProgressUI();

                if (this.currentWordIndex >= this.words.length) {
                    this.handleCompletion();
                    return;
                }
            }

            // Remember how far we've processed
            this.lastProcessedSpokenCount = allSpokenWords.length;
        }

        /**
         * STRICT matching: only the current expected word is acceptable.
         *
         * @param {string} spoken — normalized spoken word
         * @returns {{correct: boolean}}
         */
        findStrictWordMatch(spoken) {
            if (this.currentWordIndex >= this.words.length) {
                return { correct: false };
            }

            const sim = this.calculateSimilarity(
                spoken,
                this.words[this.currentWordIndex].normalized
            );

            return { correct: sim >= 70 };
        }

        /**
         * Flash a single word in red (wrong pronunciation).
         *
         * @param {number} index
         */
        flashErrorWord(index) {
            if (index >= this.words.length) return;
            if (this.words[index].revealed) return; // Already revealed, skip

            this.words[index].error = true;
            this.renderWords();

            setTimeout(() => {
                if (this.words[index]) {
                    this.words[index].error = false;
                    this.renderWords();
                }
            }, 1400);
        }

        // ==========================================================
        // SECTION 7: Arabic Text Utilities
        // ==========================================================

        /**
         * Normalize Arabic text for robust matching.
         * Removes tashkeel (diacritics), normalizes alef variants, etc.
         * NOTE: Web Speech API returns unvocalized Arabic, so tashkeel
         * must be stripped from both spoken and expected text for matching.
         * @param {string} text
         * @returns {string}
         */
        normalizeArabic(text) {
            if (!text) return '';
            // Remove tashkeel (diacritics) — required because browser speech
            // recognition does NOT return vocalized Arabic.
            text = text.replace(/[\u064B-\u065F\u0670\u0640]/g, '');
            // Normalize alef variants -> ا
            text = text.replace(/[آأإا]/g, 'ا');
            // Normalize ta marbuta -> ت
            text = text.replace(/ة/g, 'ت');
            // Normalize alif maqsura -> ي
            text = text.replace(/ى/g, 'ي');
            // Remove Quranic stop marks and decorative marks
            text = text.replace(/[\u06D6-\u06ED\u06DF-\u06E8]/g, '');
            // Keep only Arabic letters, numbers, spaces
            text = text.replace(/[^\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF\d\s]/gu, '');
            // Collapse whitespace
            text = text.replace(/\s+/g, ' ');
            return text.trim();
        }

        /**
         * Calculate similarity between two strings using Levenshtein distance.
         * @param {string} a
         * @param {string} b
         * @returns {number} Similarity percentage (0-100)
         */
        calculateSimilarity(a, b) {
            if (!a || !b) return 0;
            if (a === b) return 100;
            const maxLen = Math.max(a.length, b.length);
            if (maxLen === 0) return 100;
            const dist = this.levenshteinDistance(a, b);
            return Math.round((1 - dist / maxLen) * 100);
        }

        /**
         * Levenshtein distance for UTF-8 / Arabic characters.
         * @param {string} str1
         * @param {string} str2
         * @returns {number}
         */
        levenshteinDistance(str1, str2) {
            const c1 = Array.from(str1);
            const c2 = Array.from(str2);
            const m = c1.length;
            const n = c2.length;

            if (m === 0) return n;
            if (n === 0) return m;

            const matrix = [];
            for (let i = 0; i <= m; i++) matrix[i] = [i];
            for (let j = 0; j <= n; j++) matrix[0][j] = j;

            for (let i = 1; i <= m; i++) {
                for (let j = 1; j <= n; j++) {
                    const cost = (c1[i - 1] === c2[j - 1]) ? 0 : 1;
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j] + 1,
                        matrix[i][j - 1] + 1,
                        matrix[i - 1][j - 1] + cost
                    );
                }
            }
            return matrix[m][n];
        }

        // ==========================================================
        // SECTION 8: Silence Detection & Hint
        // ==========================================================

        /**
         * Reset the silence timer. Called on every speech event.
         */
        resetSilenceTimer() {
            this.clearSilenceTimer();
            this.lastSpeechTimestamp = Date.now();
            this.silenceTimer = setTimeout(() => {
                this.onSilenceDetected();
            }, this.silenceDelay);
        }

        /**
         * Clear the silence timer.
         */
        clearSilenceTimer() {
            if (this.silenceTimer) {
                clearTimeout(this.silenceTimer);
                this.silenceTimer = null;
            }
        }

        /**
         * Called when user has been silent for the configured duration.
         * Shows the hint button so user can reveal the next word.
         */
        onSilenceDetected() {
            if (!this.isSessionActive || this.currentWordIndex >= this.words.length) return;

            // Show hint button with gentle pulse animation
            this.hintBtnEl.classList.remove('d-none');
            this.hintBtnEl.classList.add('hint-pulse');
            this.updateTranscript('توقفت عن القراءة... اضغط زر المساعدة إذا احتجت تلميحاً');
        }

        /**
         * Reveal the next expected word as a hint.
         * Called when user clicks the hint button or presses 'H'.
         */
        revealNextWordAsHint() {
            if (!this.isSessionActive || this.currentWordIndex >= this.words.length) return;

            const wordObj = this.words[this.currentWordIndex];
            wordObj.revealed = true;
            wordObj.error = false;

            // Apply hint-specific styling (different from normal reveal)
            this.renderWords();
            this.highlightHintWord(this.currentWordIndex);

            this.currentWordIndex++;
            this.updateProgressUI();
            this.hintBtnEl.classList.add('d-none');
            this.hintBtnEl.classList.remove('hint-pulse');
            this.resetSilenceTimer();
            this.updateTranscript('');

            if (this.currentWordIndex >= this.words.length) {
                this.handleCompletion();
            }
        }

        /**
         * Temporarily highlight a hint-revealed word with a distinct color.
         * @param {number} index
         */
        highlightHintWord(index) {
            const wordEl = document.getElementById('smart-word-' + index);
            if (wordEl) {
                wordEl.classList.add('hint-revealed');
                setTimeout(() => {
                    wordEl.classList.remove('hint-revealed');
                    wordEl.classList.add('revealed-word');
                }, 1500);
            }
        }

        // ==========================================================
        // SECTION 9: UI Rendering
        // ==========================================================

        /**
         * Render the words into the text area.
         * Each word gets a span with state classes:
         *   - hidden-word: not yet revealed
         *   - revealed-word: correctly spoken
         *   - error-word: temporary error flash
         *   - hint-revealed: revealed via hint button
         */
        renderWords() {
            if (!this.textAreaEl) return;

            if (this.words.length === 0) {
                this.textAreaEl.innerHTML = `
                    <div class="text-muted">
                        <i class="fas fa-book-quran fa-3x mb-3 opacity-25"></i>
                        <p>اختر السورة والآيات ثم اضغط "عرض" لبدء التسميع الذكي</p>
                    </div>`;
                return;
            }

            const container = document.createElement('div');
            container.className = 'smart-memo-words-container';
            container.dir = 'rtl';

            let currentVerseIdx = -1;

            this.words.forEach((word, idx) => {
                // Insert verse separator if entering a new verse
                if (word.verseIndex !== currentVerseIdx) {
                    currentVerseIdx = word.verseIndex;
                    const verseNum = this.verses[currentVerseIdx]?.verse_number || (currentVerseIdx + 1);
                    const sep = document.createElement('span');
                    sep.className = 'verse-separator';
                    sep.innerHTML = ` <span class="verse-number-badge">${verseNum}</span> `;
                    container.appendChild(sep);
                }

                const span = document.createElement('span');
                span.id = 'smart-word-' + idx;
                span.className = 'smart-memo-word';
                span.textContent = word.text;
                span.setAttribute('data-index', idx);

                if (word.error) {
                    // Error flash takes precedence (even if hidden)
                    span.classList.add('error-word');
                } else if (word.revealed) {
                    span.classList.add('revealed-word');
                } else {
                    span.classList.add('hidden-word');
                }

                container.appendChild(span);
                container.appendChild(document.createTextNode(' '));
            });

            this.textAreaEl.innerHTML = '';
            this.textAreaEl.appendChild(container);

            // Auto-scroll to current word (mobile-friendly)
            this.scrollToCurrentWord();
        }

        /**
         * Scroll the text area so the current expected word is visible.
         */
        scrollToCurrentWord() {
            if (this.currentWordIndex >= this.words.length) return;
            const el = document.getElementById('smart-word-' + this.currentWordIndex);
            if (el && this.textAreaEl) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
            }
        }

        /**
         * Update progress bar and text.
         */
        updateProgressUI() {
            const total = this.words.length;
            const current = Math.min(this.currentWordIndex, total);
            const percentage = total > 0 ? Math.round((current / total) * 100) : 0;

            if (this.progressBarEl) {
                this.progressBarEl.style.width = percentage + '%';
                this.progressBarEl.setAttribute('aria-valuenow', percentage);
            }
            if (this.progressTextEl) {
                this.progressTextEl.textContent = `${current} / ${total} كلمة (${percentage}%)`;
            }
        }

        /**
         * Update the live transcript display below the text area.
         * @param {string} text
         */
        updateTranscript(text) {
            if (this.transcriptEl) {
                this.transcriptEl.textContent = text;
            }
        }

        /**
         * Toggle loading state on the load button.
         * @param {boolean} loading
         */
        setLoadingState(loading) {
            if (this.loadBtnEl) {
                this.loadBtnEl.disabled = loading;
                this.loadBtnEl.innerHTML = loading
                    ? '<i class="fas fa-spinner fa-spin me-1"></i>جارٍ التحميل...'
                    : '<i class="fas fa-eye me-1"></i>عرض';
            }
        }

        // ==========================================================
        // SECTION 10: Completion & Feedback
        // ==========================================================

        /**
         * Handle session completion (all words revealed).
         */
        handleCompletion() {
            this.isSessionActive = false;
            this.isRecording = false;
            this.clearSilenceTimer();

            if (this.speechRecognition) {
                try { this.speechRecognition.stop(); } catch (e) { /* ignore */ }
            }

            this.startBtnEl.classList.remove('d-none');
            this.stopBtnEl.classList.add('d-none');
            this.hintBtnEl.classList.add('d-none');
            this.listeningIndicatorEl.classList.add('d-none');
            this.statusBadgeEl.textContent = 'مكتمل';
            this.statusBadgeEl.className = 'badge bg-primary';
            this.textAreaEl.classList.remove('smart-memo-active');

            // Celebration animation on text area
            this.textAreaEl.classList.add('smart-memo-complete');
            setTimeout(() => {
                this.textAreaEl.classList.remove('smart-memo-complete');
            }, 3000);

            this.showToast('أحسنت! أكملت التسميع بنجاح', 'success');
            this.updateTranscript('');
        }

        /**
         * Show a toast notification (reuses existing toast container if available).
         * @param {string} message
         * @param {'success'|'error'} type
         */
        showToast(message, type) {
            let container = this.toastContainerEl;
            if (!container) {
                container = document.createElement('div');
                container.id = 'smartMemoToastContainer';
                container.className = 'toast-container position-fixed bottom-0 start-50 translate-middle-x mb-4 z-index-1050';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            const isSuccess = type === 'success';
            toast.className = isSuccess ? 'toast-success' : 'toast-error';
            toast.innerHTML = `<i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i><span>${this.escapeHtml(message)}</span>`;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        /**
         * Escape HTML to prevent XSS in toast messages.
         * @param {string} text
         * @returns {string}
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // ==============================================================
    // Initialize controller when DOM is ready
    // ==============================================================
    document.addEventListener('DOMContentLoaded', () => {
        const panel = document.getElementById('panel-memorization');
        if (panel) {
            window.smartMemorization = new SmartMemorizationController();
        }
    });
})();
