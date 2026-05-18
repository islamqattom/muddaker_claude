class LiveRecitationController {
    constructor() {
        this.ws = null;
        this.mediaRecorder = null;
        this.audioContext = null;
        this.sessionId = null;
        this.currentSession = null;
        this.verses = [];
        this.audioUrls = {};
        this.currentVerseIndex = 0;
        this.totalErrors = 0;
        this.isRecording = false;
        this.silenceTimeout = null;
        this.silenceThreshold = 1500;
        this.isEvaluating = false;
        this.transcriptText = '';
        this.audioChunks = [];
        this.similarityThreshold = 80;
        this.apiToken = '';
        this.faultAudio = new Audio('/sounds/fault.mp3');
        this.faultAudio.onerror = () => {
            console.warn('Fault sound file not found, continuing without audio feedback');
            this.faultAudio = null;
        };
        this.verseCardsCache = null;
        this.silenceRafId = null;
        this.isEnding = false;
        this.lastProcessedVerseIndex = -1;
        this.errorCountForCurrentVerse = 0;
        this.maxErrorsPerVerse = 3;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 3;
        this.reconnectDelay = 1000;
        this.lastErrorFeedbackTime = 0;
        this.errorFeedbackThrottle = 1500;
        this.isPreviewMode = false;
        this.viewMode = 'cards'; // 'cards', 'quran', or 'mushaf'
        this.mushafLayout = 'KFGQPC_V4'; // default mushaf layout

        // Web Speech API fallback
        this.speechRecognition = null;
        this.useWebSpeech = false;

        // Session timer
        this.sessionStartTime = null;
        this.sessionTimerInterval = null;
        this.sessionTimerEl = null;
        this.sessionTimerSectionEl = null;

        // Accuracy chart
        this.accuracyChart = null;
        this.accuracyData = [];
        this.verseAccuracyMap = [];

        // Word-level data from QUL
        this.wordSegments = {};
        this.verseWords = {};
        this.tajweedRules = {};
        this.concordance = {};
        this.mutashabihat = {};
        this.currentReciter = '';
        this.wordHighlightTimer = null;

        this.init();
    }

    init() {
        this.modeSelect = document.getElementById('modeSelect');
        this.startPageInput = document.getElementById('startPage');
        this.reciterSelect = document.getElementById('reciterSelect');

        // Check for Web Speech API support
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (SpeechRecognition) {
            this.speechRecognition = new SpeechRecognition();
            this.speechRecognition.continuous = true;
            this.speechRecognition.interimResults = true;
            this.speechRecognition.lang = 'ar-SA';

            this.speechRecognition.onresult = (event) => {
                let transcript = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    transcript += event.results[i][0].transcript;
                }
                if (transcript.trim().length > 0) {
                    this.transcriptText = transcript;
                    this.updateTranscript(transcript);

                    // Real-time error detection during speech
                    if (event.results[i].isFinal) {
                        this.checkForErrorsDuringRecitation(transcript);
                        this.processRecitation(transcript);
                    } else {
                        this.checkForErrorsDuringRecitation(transcript);
                    }
                }
            };

            this.speechRecognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                if (event.error === 'not-allowed') {
                    this.showError('لم يتم السماح بالوصول إلى الميكروفون');
                }
            };

            this.speechRecognition.onend = () => {
                if (this.isRecording && !this.isEnding) {
                    // Restart recognition if it stopped unexpectedly
                    try {
                        this.speechRecognition.start();
                    } catch (e) {
                        console.warn('Could not restart speech recognition');
                    }
                }
            };
        }

        this.modeSelect = document.getElementById('modeSelect');
        this.endPageInput = document.getElementById('endPage');
        this.endPageInputContainer = document.getElementById('endPageInput');
        this.similarityThresholdInput = document.getElementById('similarityThreshold');
        this.previewBtn = document.getElementById('previewVerses');
        this.startBtn = document.getElementById('startRecitation');
        this.stopBtn = document.getElementById('stopRecitation');
        this.nextVerseBtn = document.getElementById('nextVerse');
        this.statusEl = document.getElementById('recitationStatus');
        this.progressEl = document.getElementById('recitationProgress');
        this.versesCountEl = document.getElementById('versesCount');
        this.errorCountEl = document.getElementById('errorCount');
        this.currentVerseEl = document.getElementById('currentVerse');
        this.transcriptEl = document.getElementById('transcript');

        // Cache CSRF token once
        this.csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';
        // Read API token from meta tag if available
        this.apiToken = document.querySelector('meta[name=api-token]')?.content || '';
        // Read route URLs from meta tags
        this.tokenUrl = document.querySelector('meta[name=token-url]')?.content || '/student/live-recitation/token';
        this.previewUrl = document.querySelector('meta[name=preview-url]')?.content || '/api/student/live-recitation/preview';
        this.startUrl = document.querySelector('meta[name=start-url]')?.content || '/api/student/live-recitation/start';
        this.errorUrl = document.querySelector('meta[name=error-url]')?.content || '/api/student/live-recitation/error';
        this.progressUrl = document.querySelector('meta[name=progress-url]')?.content || '/api/student/live-recitation/progress';
        this.reportUrlTemplate = document.querySelector('meta[name=report-url-template]')?.content || '/api/student/live-recitation/session/__ID__/report';

        // Validate critical elements
        if (!this.startBtn || !this.modeSelect || !this.startPageInput) {
            console.error('Failed to initialize LiveRecitationController: missing critical DOM elements', {
                startBtn: this.startBtn,
                modeSelect: this.modeSelect,
                startPageInput: this.startPageInput
            });
            return;
        }

        this.modeSelect.addEventListener('change', () => this.togglePageInputs());
        this.previewBtn.addEventListener('click', () => this.loadVersesPreview());
        this.startBtn.addEventListener('click', () => this.startRecitation());
        this.stopBtn.addEventListener('click', () => this.stopRecitation());
        this.nextVerseBtn.addEventListener('click', () => this.nextVerse());

        // Load reciters
        this.loadReciters();

        // Cache frequently accessed DOM elements
        this.pageInputsContainer = document.getElementById('pageInputs');
        this.endPageInputContainer = document.getElementById('endPageInput');
        this.surahInputsContainer = document.getElementById('surahInputs');
        this.verseRangeInputsContainer = document.getElementById('verseRangeInputs');
        this.progressBarEl = document.getElementById('progressBar');
        this.toastContainerEl = document.getElementById('toastContainer');
        this.statusIndicatorEl = document.getElementById('statusIndicator');

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isRecording && !this.isEnding) {
                this.stopRecitation();
            }
            if ((e.key === ' ' || e.key === 'Enter') && !this.isRecording && document.activeElement === this.startBtn) {
                e.preventDefault();
                this.startRecitation();
            }
        });

        // Threshold slider display
        const thresholdInput = document.getElementById('similarityThreshold');
        const thresholdValue = document.getElementById('thresholdValue');
        if (thresholdInput && thresholdValue) {
            thresholdInput.addEventListener('input', () => {
                thresholdValue.textContent = thresholdInput.value + '%';
            });
        }

        // Clear transcript button
        const clearTranscriptBtn = document.getElementById('clearTranscript');
        if (clearTranscriptBtn) {
            clearTranscriptBtn.addEventListener('click', () => {
                this.transcriptText = '';
                if (this.transcriptEl) {
                    this.transcriptEl.innerHTML = '<span class="text-muted">جارٍ الاستماع...</span>';
                }
            });
        }

        // View mode toggle is handled via onclick in HTML
    }

    togglePageInputs() {
        const mode = this.modeSelect.value;

        if (mode === 'page') {
            this.pageInputsContainer.classList.remove('d-none');
            this.endPageInputContainer.classList.add('d-none');
            this.surahInputsContainer.classList.add('d-none');
            this.verseRangeInputsContainer.classList.add('d-none');
        } else if (mode === 'range') {
            this.pageInputsContainer.classList.remove('d-none');
            this.endPageInputContainer.classList.remove('d-none');
            this.surahInputsContainer.classList.add('d-none');
            this.verseRangeInputsContainer.classList.add('d-none');
        } else if (mode === 'surah') {
            this.pageInputsContainer.classList.add('d-none');
            this.endPageInputContainer.classList.add('d-none');
            this.surahInputsContainer.classList.remove('d-none');
            this.verseRangeInputsContainer.classList.remove('d-none');
            this.loadSurahList();
        }
    }

    loadSurahList() {
        const surahSelect = document.getElementById('surahSelect');
        if (surahSelect.options.length > 1) return; // Already loaded

        const surahs = [
            { number: 1, name: 'الفاتحة'}, { number: 2, name: 'البقرة'}, { number: 3, name: 'آل عمران'},
            { number: 4, name: 'النساء'}, { number: 5, name: 'المائدة'}, { number: 6, name: 'الأنعام'},
            { number: 7, name: 'الأعراف'}, { number: 8, name: 'الأنفال'}, { number: 9, name: 'التوبة'},
            { number: 10, name: 'يونس'}, { number: 11, name: 'هود'}, { number: 12, name: 'يوسف'},
            { number: 13, name: 'الرعد'}, { number: 14, name: 'إبراهيم'}, { number: 15, name: 'الحجر'},
            { number: 16, name: 'النحل'}, { number: 17, name: 'الإسراء'}, { number: 18, name: 'الكهف'},
            { number: 19, name: 'مريم'}, { number: 20, name: 'طه'}, { number: 21, name: 'الأنبياء'},
            { number: 22, name: 'الحج'}, { number: 23, name: 'المؤمنون'}, { number: 24, name: 'النور'},
            { number: 25, name: 'الفرقان'}, { number: 26, name: 'الشعراء'}, { number: 27, name: 'النمل'},
            { number: 28, name: 'القصص'}, { number: 29, name: 'العنكبوت'}, { number: 30, name: 'الروم'},
            { number: 31, name: 'لقمان'}, { number: 32, name: 'السجدة'}, { number: 33, name: 'الأحزاب'},
            { number: 34, name: 'سبأ'}, { number: 35, name: 'فاطر'}, { number: 36, name: 'يس'},
            { number: 37, name: 'الصافات'}, { number: 38, name: 'ص'}, { number: 39, name: 'الزمر'},
            { number: 40, name: 'غافر'}, { number: 41, name: 'فصلت'}, { number: 42, name: 'الشورى'},
            { number: 43, name: 'الزخرف'}, { number: 44, name: 'الدخان'}, { number: 45, name: 'الجاثية'},
            { number: 46, name: 'الأحقاف'}, { number: 47, name: 'محمد'}, { number: 48, name: 'الفتح'},
            { number: 49, name: 'الحجرات'}, { number: 50, name: 'ق'}, { number: 51, name: 'الذاريات'},
            { number: 52, name: 'الطور'}, { number: 53, name: 'النجم'}, { number: 54, name: 'القمر'},
            { number: 55, name: 'الرحمن'}, { number: 56, name: 'الواقعة'}, { number: 57, name: 'الحديد'},
            { number: 58, name: 'المجادلة'}, { number: 59, name: 'الحشر'}, { number: 60, name: 'الممتحنة'},
            { number: 61, name: 'الصف'}, { number: 62, name: 'الجمعة'}, { number: 63, name: 'المنافقون'},
            { number: 64, name: 'التغابن'}, { number: 65, name: 'الطلاق'}, { number: 66, name: 'التحريم'},
            { number: 67, name: 'الملك'}, { number: 68, name: 'القلم'}, { number: 69, name: 'الحاقة'},
            { number: 70, name: 'المعارج'}, { number: 71, name: 'نوح'}, { number: 72, name: 'الجن'},
            { number: 73, name: 'المزمل'}, { number: 74, name: 'المدثر'}, { number: 75, name: 'القيامة'},
            { number: 76, name: 'الإنسان'}, { number: 77, name: 'المرسلات'}, { number: 78, name: 'النبأ'},
            { number: 79, name: 'النازعات'}, { number: 80, name: 'عبس'}, { number: 81, name: 'التكوير'},
            { number: 82, name: 'الإنفطار'}, { number: 83, name: 'المطففين'}, { number: 84, name: 'الإنشقاق'},
            { number: 85, name: 'البروج'}, { number: 86, name: 'الطارق'}, { number: 87, name: 'الأعلى'},
            { number: 88, name: 'الغاشية'}, { number: 89, name: 'الفجر'}, { number: 90, name: 'البلد'},
            { number: 91, name: 'الشمس'}, { number: 92, name: 'الليل'}, { number: 93, name: 'الضحى'},
            { number: 94, name: 'الشرح'}, { number: 95, name: 'التين'}, { number: 96, name: 'العلق'},
            { number: 97, name: 'القدر'}, { number: 98, name: 'البينة'}, { number: 99, name: 'الزلزلة'},
            { number: 100, name: 'العاديات'}, { number: 101, name: 'القارعة'}, { number: 102, name: 'التكاثر'},
            { number: 103, name: 'العصر'}, { number: 104, name: 'الهمزة'}, { number: 105, name: 'الفيل'},
            { number: 106, name: 'قريش'}, { number: 107, name: 'الماعون'}, { number: 108, name: 'الكوثر'},
            { number: 109, name: 'الكافرون'}, { number: 110, name: 'النصر'}, { number: 111, name: 'المسد'},
            { number: 112, name: 'الإخلاص'}, { number: 113, name: 'الفلق'}, { number: 114, name: 'الناس'}
        ];

        surahs.forEach(surah => {
            const option = document.createElement('option');
            option.value = surah.number;
            option.textContent = surah.number + '. ' + surah.name;
            surahSelect.appendChild(option);
        });
    }

    async loadReciters() {
        if (!this.reciterSelect) return;
        try {
            const response = await fetch('/api/student/live-recitation/reciters', {
                headers: { 'Accept': 'application/json' },
                credentials: 'include'
            });
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.reciters) {
                    this.reciterSelect.innerHTML = '<option value="">اختر القارئ</option>';
                    Object.entries(data.reciters).forEach(([slug, name]) => {
                        const option = document.createElement('option');
                        option.value = slug;
                        option.textContent = name;
                        this.reciterSelect.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.warn('Failed to load reciters:', error);
        }
    }

    async loadVersesPreview() {
        const mode = this.modeSelect.value;
        const startPage = parseInt(this.startPageInput.value);
        const endPage = mode === 'range' ? parseInt(this.endPageInput.value) : startPage;

        // Validate inputs before sending
        if (mode === 'surah') {
            const surahSelect = document.getElementById('surahSelect');
            const verseStart = parseInt(document.getElementById('verseStart').value);
            const verseEnd = parseInt(document.getElementById('verseEnd').value);
            if (!surahSelect.value) {
                this.showError('يرجى اختيار السورة');
                return;
            }
            if (isNaN(verseStart) || verseStart < 1) {
                this.showError('يرجى إدخال رقم آية البداية بشكل صحيح');
                return;
            }
            if (isNaN(verseEnd) || verseEnd < verseStart) {
                this.showError('يرجى إدخال رقم آية النهاية بشكل صحيح (يجب أن يكون أكبر من أو يساوي آية البداية)');
                return;
            }
        }

        this.previewBtn.disabled = true;
        this.previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جارٍ التحميل...';
        this.updateStatus('جارٍ تحميل الآيات...');

        // Get API token if not available
        if (!this.apiToken) {
            try {
                const tokenResponse = await fetch(this.tokenUrl, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'include'
                });
                if (!tokenResponse.ok) throw new Error('Token request failed');
                const tokenData = await tokenResponse.json();
                if (tokenData.success && tokenData.token) {
                    this.apiToken = tokenData.token;
                }
            } catch (error) {
                console.error('Failed to get API token:', error);
            }
        }

        try {
            const requestBody = { mode: mode };
            if (this.reciterSelect && this.reciterSelect.value) {
                requestBody.reciter = this.reciterSelect.value;
            }

            if (mode === 'page') {
                requestBody.start_page = startPage;
                requestBody.end_page = startPage;
            } else if (mode === 'range') {
                requestBody.start_page = startPage;
                requestBody.end_page = endPage;
            } else if (mode === 'surah') {
                const surahSelect = document.getElementById('surahSelect');
                const verseStart = parseInt(document.getElementById('verseStart').value);
                const verseEnd = parseInt(document.getElementById('verseEnd').value);
                requestBody.surah = parseInt(surahSelect.value);
                requestBody.verse_start = verseStart;
                requestBody.verse_end = verseEnd;
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            };
            if (this.apiToken) {
                headers['Authorization'] = 'Bearer ' + this.apiToken;
            }

            const response = await fetch(this.previewUrl, {
                method: 'POST',
                headers: headers,
                credentials: 'include',
                body: JSON.stringify(requestBody)
            });

            const data = await response.json();

            if (!data.success) {
                let errorMsg = data.error || 'فشل في تحميل الآيات';
                if (response.status === 422 && data.errors) {
                    errorMsg = Object.values(data.errors).flat().join(' / ') || errorMsg;
                }
                this.showError(errorMsg);
                this.previewBtn.disabled = false;
                this.previewBtn.innerHTML = '<i class="fas fa-eye me-2"></i>عرض الآيات';
                return;
            }

            if (!data.data || !data.data.verses || data.data.verses.length === 0) {
                this.showError('لم يتم العثور على آيات للنطاق المحدد');
                this.previewBtn.disabled = false;
                this.previewBtn.innerHTML = '<i class="fas fa-eye me-2"></i>عرض الآيات';
                return;
            }

            this.verses = data.data.verses;
            this.audioUrls = data.data.audio_urls || {};
            this.wordSegments = data.data.word_segments || {};
            this.verseWords = data.data.verse_words || {};
            this.tajweedRules = data.data.tajweed_rules || {};
            this.concordance = data.data.concordance || {};
            this.mutashabihat = data.data.mutashabihat || {};
            this.currentVerseIndex = 0;
            this.totalErrors = 0;
            this.isPreviewMode = true;
            this.updateProgress();
            this.refreshVersesDisplay();
            this.highlightVerse(0);

            // Enable start button after successful preview
            this.startBtn.disabled = false;
            this.previewBtn.disabled = false;
            this.previewBtn.innerHTML = '<i class="fas fa-eye me-2"></i>عرض الآيات';
            this.updateStatus('تم تحميل الآيات - جاهز للتسميع');
        } catch (error) {
            console.error('Error loading verses preview:', error);
            this.showError('حدث خطأ أثناء تحميل الآيات');
            this.previewBtn.disabled = false;
            this.previewBtn.innerHTML = '<i class="fas fa-eye me-2"></i>عرض الآيات';
        }
    }

    async startRecitation() {
        const mode = this.modeSelect.value;
        const startPage = parseInt(this.startPageInput.value);
        const endPage = mode === 'range' ? parseInt(this.endPageInput.value) : startPage;
        this.similarityThreshold = parseInt(this.similarityThresholdInput.value);
        this.startBtn.disabled = true;

        // Update status indicator
        this.updateStatusIndicator('connecting');

        // Reset reconnect attempts
        this.reconnectAttempts = 0;
        this.updateStatus('جارٍ البدء...');

        // Validate surah mode inputs
        if (mode === 'surah') {
            const surahSelect = document.getElementById('surahSelect');
            const verseStart = parseInt(document.getElementById('verseStart').value);
            const verseEnd = parseInt(document.getElementById('verseEnd').value);
            if (!surahSelect.value) {
                this.showError('يرجى اختيار السورة');
                this.startBtn.disabled = false;
                return;
            }
            if (isNaN(verseStart) || verseStart < 1) {
                this.showError('يرجى إدخال رقم آية البداية بشكل صحيح');
                this.startBtn.disabled = false;
                return;
            }
            if (isNaN(verseEnd) || verseEnd < verseStart) {
                this.showError('يرجى إدخال رقم آية النهاية بشكل صحيح');
                this.startBtn.disabled = false;
                return;
            }
        }

        // Check if we have API token (from meta tag or previous fetch)
        if (!this.apiToken) {
            try {
                const tokenResponse = await fetch(this.tokenUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                    credentials: 'include'
                });
                if (!tokenResponse.ok) {
                    throw new Error('Token request failed: ' + tokenResponse.status);
                }
                const tokenData = await tokenResponse.json();
                if (!tokenData.success || !tokenData.token) {
                    this.showError('فشل في الحصول على رمز الدخول');
                    this.startBtn.disabled = false;
                    return;
                }
                this.apiToken = tokenData.token;
            } catch (error) {
                console.error('Failed to get API token:', error);
                this.showError('فشل في الاتصال بالخادم: ' + error.message);
                this.startBtn.disabled = false;
                return;
            }
        }

        try {
            const requestBody = { mode: mode };
            if (this.reciterSelect && this.reciterSelect.value) {
                requestBody.reciter = this.reciterSelect.value;
            }

            if (mode === 'page') {
                requestBody.start_page = startPage;
                requestBody.end_page = startPage;
            } else if (mode === 'range') {
                requestBody.start_page = startPage;
                requestBody.end_page = endPage;
            } else if (mode === 'surah') {
                const surahSelect = document.getElementById('surahSelect');
                const verseStart = parseInt(document.getElementById('verseStart').value);
                const verseEnd = parseInt(document.getElementById('verseEnd').value);
                requestBody.surah = parseInt(surahSelect.value);
                requestBody.verse_start = verseStart;
                requestBody.verse_end = verseEnd;
            }

            const response = await fetch(this.startUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.apiToken,
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(requestBody)
            });
            let data;
            const responseText = await response.text();
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse JSON. Response text:', responseText);
                throw new Error('Server returned non-JSON response: ' + response.status);
            }

            console.log('API Response:', response.status, data);

            if (!data.success) {
                let errorMsg = data.error || data.errors || 'فشل في بدء جلسة التسميع';
                if (errorMsg.includes('AssemblyAI') || errorMsg.includes('مفتاح')) {
                    errorMsg = 'يرجى التواصل مع المشرف لتفعيل خدمة التسميع المباشر';
                }
                if (response.status === 422 && data.errors) {
                    errorMsg = Object.values(data.errors).flat().join(' / ') || errorMsg;
                }
                console.error('API Error:', errorMsg);
                this.showError(errorMsg);
                this.startBtn.disabled = false;
                this.startBtn.classList.remove('recording');
                this.startBtn.innerHTML = '<i class="fas fa-microphone me-2"></i>ابدأ التسميع';
                return;
            }

            // Validate API response has required session data
            if (!data.data || !data.data.session_id) {
                console.error('Invalid API response - missing session:', data);
                this.showError('فشل في بدء جلسة التسميع');
                this.startBtn.disabled = false;
                return;
            }

            this.sessionId = data.data.session_id;
            this.currentSession = {
                id: data.data.session_id,
                audioUrls: data.data.audio_urls,
                websocketToken: data.data.websocket_token,
                websocketUrl: data.data.websocket_url
            };

            // If not in preview mode, load verses from response
            if (!this.isPreviewMode) {
                if (!data.data.verses || !Array.isArray(data.data.verses) || data.data.verses.length === 0) {
                    console.error('Invalid API response - missing verses:', data);
                    this.showError('لم يتم العثور على آيات للنطاق المحدد');
                    this.startBtn.disabled = false;
                    return;
                }
                this.verses = data.data.verses;
                this.audioUrls = data.data.audio_urls || {};
                this.wordSegments = data.data.word_segments || {};
                this.verseWords = data.data.verse_words || {};
                this.tajweedRules = data.data.tajweed_rules || {};
                this.concordance = data.data.concordance || {};
                this.mutashabihat = data.data.mutashabihat || {};
                this.currentVerseIndex = 0;
                this.totalErrors = 0;
                this.updateProgress();
                this.refreshVersesDisplay();
                this.highlightVerse(0);
            } else {
                // In preview mode: reset tracking state but keep verses display
                this.audioUrls = data.data.audio_urls || {};
                this.wordSegments = data.data.word_segments || {};
                this.verseWords = data.data.verse_words || {};
                this.tajweedRules = data.data.tajweed_rules || {};
                this.concordance = data.data.concordance || {};
                this.mutashabihat = data.data.mutashabihat || {};
                this.currentVerseIndex = 0;
                this.totalErrors = 0;
                this.errorCountEl.textContent = '0';
                this.transcriptText = '';
                this.lastProcessedVerseIndex = -1;
                this.errorCountForCurrentVerse = 0;
                this.updateProgress();
                this.highlightVerse(0);
            }

            this.isEnding = false;
            this.stopBtn.classList.remove('d-none');
            this.nextVerseBtn.classList.remove('d-none');

            // Start session timer and accuracy chart
            this.startSessionTimer();
            this.initAccuracyChart();
            this.showToast('بدأت جلسة التسميع - اقرأ بصوت واضح', 'success');

            // Use Web Speech API if no WebSocket URL (AssemblyAI not configured)
            if (data.data.websocket_url && data.data.websocket_token) {
                this.connectWebSocket();
            } else {
                this.startWebSpeechRecognition();
            }
        } catch (error) {
            console.error('Error starting recitation:', error);
            console.error('Response status:', response?.status);
            console.error('Response body:', response?.body);
            this.showError('حدث خطأ أثناء بدء التسميع');
            this.startBtn.disabled = false;
            this.startBtn.classList.remove('recording');
            this.startBtn.innerHTML = '<i class="fas fa-microphone me-2"></i>ابدأ التسميع';
        }
    }

    startWebSpeechRecognition() {
        if (!this.speechRecognition) {
            this.showError('المتصفح لا يدعم التعرف على الصوت العربي');
            this.resetUI();
            return;
        }

        this.updateStatusIndicator('active');
        this.updateStatus('متصل - جارٍ الاستماع...');
        this.isRecording = true;
        this.startBtn.classList.add('recording');
        this.startBtn.innerHTML = '<i class="fas fa-circle me-2"></i>جارٍ التسجيل...';

        try {
            this.speechRecognition.start();
        } catch (error) {
            console.error('Failed to start speech recognition:', error);
            this.showError('فشل في بدء التعرف على الصوت');
        }
    }

    getCsrfToken() {
        return this.csrfToken;
    }

    connectWebSocket() {
        // API returns websocket_url (snake_case), access it correctly
        const websocketUrl = this.currentSession.websocket_url || this.currentSession.websocketUrl;

        if (!websocketUrl) {
            console.error('WebSocket URL is missing from session data');
            this.showError('خطأ في الاتصال - عنوان الخادم غير موجود');
            return;
        }

        try {
            this.ws = new WebSocket(websocketUrl);
        } catch (error) {
            console.error('Failed to create WebSocket:', error);
            this.showError('فشل في إنشاء الاتصال');
            return;
        }

        this.ws.onopen = () => {
            this.updateStatusIndicator('active');
            this.updateStatus('متصل - جارٍ الاستماع...');
            this.isRecording = true;
            this.startBtn.classList.add('recording');
            this.startBtn.innerHTML = '<i class="fas fa-circle me-2"></i>جارٍ التسجيل...';
            this.startRecording();
        };
        this.ws.onmessage = (event) => {
            try {
                const message = JSON.parse(event.data);
                this.handleWebSocketMessage(message);
            } catch (error) {
                console.error('Failed to parse WebSocket message:', error);
            }
        };
        this.ws.onerror = (error) => {
            console.error('WebSocket error:', error);
            this.showError('خطأ في الاتصال');
        };
        this.ws.onclose = () => {
            this.isRecording = false;
            this.stopSilenceDetection();
            if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
                this.mediaRecorder.stop();
            }
            // Attempt reconnection if not ending and under max attempts
            if (!this.isEnding && this.reconnectAttempts < this.maxReconnectAttempts) {
                this.reconnectAttempts++;
                this.updateStatus('جارٍ إعادة الاتصال... (' + this.reconnectAttempts + '/' + this.maxReconnectAttempts + ')');
                setTimeout(() => this.connectWebSocket(), this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1));
            } else if (!this.isEnding) {
                // Max reconnection attempts reached, stop gracefully
                this.updateStatus('انقطع الاتصال - جارٍ إنهاء التسميع...');
                this.showError('انقطع الاتصال بالخادم. سيتم حفظ تقدمك.');
                setTimeout(() => this.stopRecitation(), 1500);
            }
        };
    }

    async startRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.mediaRecorder = new MediaRecorder(stream);
            this.audioChunks = [];

            // Set up analyser BEFORE starting recording
            this.analyser = this.audioContext.createAnalyser();
            this.analyser.fftSize = 256;
            const source = this.audioContext.createMediaStreamSource(stream);
            source.connect(this.analyser);

            this.mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    this.audioChunks.push(event.data);
                }
            };
            this.mediaRecorder.onstop = async () => {
                if (this.audioChunks.length > 0 && this.isRecording) {
                    const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                    await this.sendAudioChunk(audioBlob);
                }
            };
            this.mediaRecorder.start(1000);
            this.startSilenceDetection();
        } catch (error) {
            console.error('Error accessing microphone:', error);
            this.showError('لم يتم السماح بالوصول إلى الميكروفون');
        }
    }

    startSilenceDetection() {
        if (!this.analyser || !this.isRecording) return;
        const dataArray = new Uint8Array(this.analyser.frequencyBinCount);
        const detectSilence = () => {
            if (!this.isRecording) return;
            this.analyser.getByteFrequencyData(dataArray);
            const average = dataArray.reduce((a, b) => a + b, 0) / dataArray.length;
            if (average < 5) {
                if (!this.silenceTimeout) {
                    this.silenceTimeout = setTimeout(() => {
                        this.onSilenceDetected();
                    }, this.silenceThreshold);
                }
            } else {
                if (this.silenceTimeout) {
                    clearTimeout(this.silenceTimeout);
                    this.silenceTimeout = null;
                }
            }
            this.silenceRafId = requestAnimationFrame(detectSilence);
        };
        this.silenceRafId = requestAnimationFrame(detectSilence);
    }

    stopSilenceDetection() {
        if (this.silenceRafId) {
            cancelAnimationFrame(this.silenceRafId);
            this.silenceRafId = null;
        }
        if (this.silenceTimeout) {
            clearTimeout(this.silenceTimeout);
            this.silenceTimeout = null;
        }
    }

    onSilenceDetected() {
        if (this.isEvaluating || this.isEnding || this.currentVerseIndex >= this.verses.length) return;
        this.isEvaluating = true;
        // Don't stop recording, just evaluate current transcript and continue
        this.evaluateCurrentVerse();
    }

    async sendAudioChunk(blob) {
        if (!this.ws || this.ws.readyState !== WebSocket.OPEN) return;

        try {
            const reader = new FileReader();
            await new Promise((resolve, reject) => {
                reader.onloadend = () => {
                    try {
                        const base64 = reader.result.split(',')[1];
                        this.ws.send(JSON.stringify({ audio_data: base64 }));
                        resolve();
                    } catch (e) {
                        reject(e);
                    }
                };
                reader.onerror = () => reject(reader.error);
                reader.readAsDataURL(blob);
            });
        } catch (error) {
            console.error('Failed to send audio chunk:', error);
        }

        this.audioChunks = [];
        if (this.isRecording && this.mediaRecorder.state === 'inactive') {
            this.mediaRecorder.start(1000);
        }
    }

    handleWebSocketMessage(message) {
        if (message.message_type === 'PartialTranscript' || message.message_type === 'FinalTranscript') {
            const transcript = message.text;
            this.transcriptText = transcript;
            this.updateTranscript(transcript);

            // Real-time error detection - check during speech (PartialTranscript)
            if (message.message_type === 'PartialTranscript' && transcript.trim().length > 3) {
                this.checkForErrorsDuringRecitation(transcript);
            }

            // On final transcript, do verse matching and evaluation
            if (message.message_type === 'FinalTranscript' && transcript.trim().length > 0) {
                this.processRecitation(transcript);
            }
        }
    }

    checkForErrorsDuringRecitation(transcript) {
        const currentVerse = this.verses[this.currentVerseIndex];
        if (!currentVerse) return;

        const similarity = this.calculateSimilarity(transcript, currentVerse.text);

        // Throttle error feedback to avoid too many alerts
        const now = Date.now();
        if (now - this.lastErrorFeedbackTime < this.errorFeedbackThrottle) return;

        // If similarity drops below threshold during recitation, show real-time error feedback
        // Only trigger if similarity is reasonable (not too low, to avoid false positives on partial text)
        if (similarity < this.similarityThreshold && similarity > 30) {
            this.lastErrorFeedbackTime = now;
            // Show real-time error feedback - verse glows red immediately
            this.setVerseErrorState(this.currentVerseIndex, 1500);
            // Play error tone for immediate feedback
            this.playErrorTone();

            // Also update error count
            this.totalErrors++;
            if (this.errorCountEl) {
                this.errorCountEl.textContent = this.totalErrors;
            }
        }
    }

    playErrorTone() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            // Error tone: 300Hz + 400Hz for a dissonant "wrong" sound
            oscillator.frequency.value = 350;
            oscillator.type = 'sawtooth'; // Harsher sound for error
            gainNode.gain.setValueAtTime(0.2, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.25);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.25);

            oscillator.onended = () => audioContext.close();
        } catch (e) {
            console.warn('Error tone playback failed:', e);
        }
    }

    /**
     * Play the recitation audio for a verse.
     * Supports word-level highlighting if wordSegments are available.
     * @param {string} url
     * @param {HTMLButtonElement} btn
     * @param {string|null} verseKey - e.g. "1:1" for word-level highlighting
     */
    playVerseAudio(url, btn, verseKey = null) {
        // If clicking the same button that is currently playing, just stop it
        if (this.currentAudioBtn === btn && this.currentVerseAudio) {
            this.stopCurrentAudio();
            return;
        }

        this.stopCurrentAudio();

        const audio = new Audio(url);
        this.currentVerseAudio = audio;
        this.currentAudioBtn = btn;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>جارٍ التحميل...';
        btn.disabled = true;

        audio.addEventListener('canplaythrough', () => {
            btn.innerHTML = '<i class="fas fa-stop me-1"></i>إيقاف';
            btn.disabled = false;
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-success');
            audio.play();
            // Start word highlighting if segments available
            if (verseKey && this.wordSegments[verseKey]) {
                this.startWordHighlighting(verseKey, audio);
            }
        });

        audio.addEventListener('ended', () => {
            this.resetAudioButton(btn);
            this.stopWordHighlighting();
        });

        audio.addEventListener('error', () => {
            btn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>خطأ';
            btn.disabled = false;
            this.showToast('تعذر تحميل التسجيل الصوتي', 'error');
            this.stopCurrentAudio();
        });

        audio.load();
    }

    stopCurrentAudio() {
        if (this.currentVerseAudio) {
            this.currentVerseAudio.pause();
            this.currentVerseAudio.currentTime = 0;
            this.currentVerseAudio = null;
        }
        if (this.currentAudioBtn) {
            this.currentAudioBtn.innerHTML = '<i class="fas fa-volume-up me-1"></i>سماع التلاوة';
            this.currentAudioBtn.classList.remove('btn-success');
            this.currentAudioBtn.classList.add('btn-outline-success');
            this.currentAudioBtn = null;
        }
        this.stopWordHighlighting();
    }

    resetAudioButton(btn) {
        btn.innerHTML = '<i class="fas fa-volume-up me-1"></i>سماع التلاوة';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-success');
        this.currentVerseAudio = null;
        this.currentAudioBtn = null;
    }

    startWordHighlighting(verseKey, audio) {
        const segments = this.wordSegments[verseKey];
        if (!segments || segments.length === 0) return;

        const words = this.verseWords[verseKey];
        if (!words) return;

        // Find word elements for this verse
        const wordEls = this.getVerseWordElements(verseKey);
        if (!wordEls) return;

        this.stopWordHighlighting();

        const checkTime = () => {
            if (!this.currentVerseAudio || this.currentVerseAudio.paused) return;
            const currentTime = audio.currentTime;

            // Find current word based on time
            let activeIndex = -1;
            for (let i = 0; i < segments.length; i++) {
                const seg = segments[i];
                if (currentTime >= seg.start_time && currentTime <= seg.end_time) {
                    activeIndex = i;
                    break;
                }
                if (i === segments.length - 1 && currentTime > seg.end_time) {
                    activeIndex = i;
                }
            }

            // Highlight current word
            wordEls.forEach((el, i) => {
                el.classList.toggle('highlighted-word', i === activeIndex);
            });

            this.wordHighlightTimer = requestAnimationFrame(checkTime);
        };

        this.wordHighlightTimer = requestAnimationFrame(checkTime);
    }

    stopWordHighlighting() {
        if (this.wordHighlightTimer) {
            cancelAnimationFrame(this.wordHighlightTimer);
            this.wordHighlightTimer = null;
        }
        // Remove all word highlights
        document.querySelectorAll('.highlighted-word').forEach(el => {
            el.classList.remove('highlighted-word');
        });
    }

    getVerseWordElements(verseKey) {
        const card = document.querySelector(`.verse-card[data-key="${verseKey}"]`);
        if (!card) return null;
        return card.querySelectorAll('.verse-word');
    }

    /**
     * Apply Tajweed rules to a single word text using rule spans
     */
    applyTajweedToWord(wordText, rules) {
        if (!rules || rules.length === 0) return wordText;
        // Sort rules by start_char descending to build from end to start
        const sortedRules = [...rules].sort((a, b) => (b.start_char || 0) - (a.start_char || 0));
        let result = wordText;
        sortedRules.forEach(rule => {
            const start = rule.start_char || 0;
            const end = rule.end_char != null ? rule.end_char + 1 : start + 1;
            const color = rule.rule_color || this.getTajweedColor(rule.rule_type);
            const label = this.getTajweedLabel(rule.rule_type);
            const before = result.substring(0, start);
            const target = result.substring(start, end);
            const after = result.substring(end);
            result = before + `<span class="tajweed-rule tajweed-${rule.rule_type}" style="color:${color}" title="${label}">${target}</span>` + after;
        });
        return result;
    }

    /**
     * Apply Tajweed rules to full verse text (when no word-level data)
     */
    applyTajweedToText(text, rules) {
        if (!rules || rules.length === 0) return text;
        // For full-text mode, word_position is null; apply sequentially
        const sortedRules = [...rules].sort((a, b) => (b.start_char || 0) - (a.start_char || 0));
        let result = text;
        sortedRules.forEach(rule => {
            const start = rule.start_char || 0;
            const end = rule.end_char != null ? rule.end_char + 1 : start + 1;
            const color = rule.rule_color || this.getTajweedColor(rule.rule_type);
            const label = this.getTajweedLabel(rule.rule_type);
            const before = result.substring(0, start);
            const target = result.substring(start, end);
            const after = result.substring(end);
            result = before + `<span class="tajweed-rule tajweed-${rule.rule_type}" style="color:${color}" title="${label}">${target}</span>` + after;
        });
        return result;
    }

    getTajweedColor(ruleType) {
        const colors = {
            'ghunna': '#FF7F50',
            'ikhfa': '#8A2BE2',
            'idgham': '#4169E1',
            'idgham_without_ghunnah': '#6495ED',
            'iqlab': '#2E8B57',
            'qalqalah': '#008080',
            'madd_2': '#DC143C',
            'madd_246': '#B22222',
            'madd_6': '#8B0000',
            'madd_obligatory': '#FF1493',
            'slient': '#708090',
        };
        return colors[ruleType] || '#666666';
    }

    getTajweedLabel(ruleType) {
        const labels = {
            'ghunna': 'غنة',
            'ikhfa': 'إخفاء',
            'idgham': 'إدغام',
            'idgham_without_ghunnah': 'إدغام بلا غنة',
            'iqlab': 'إقلاب',
            'qalqalah': 'قلقلة',
            'madd_2': 'مد ٢',
            'madd_246': 'مد ٢/٤/٦',
            'madd_6': 'مد ٦',
            'madd_obligatory': 'مد لازم',
            'slient': 'سكت',
        };
        return labels[ruleType] || ruleType;
    }

    /**
     * Show word concordance modal (grammar/morphology analysis)
     */
    showWordConcordance(verseKey, wordPosition) {
        const concordanceData = this.concordance[verseKey];
        if (!concordanceData || !concordanceData[wordPosition]) {
            // Try from verse-level concordance array
            const verseConc = this.concordance[verseKey];
            const wordData = verseConc ? verseConc.find(w => w.word_position === wordPosition + 1) : null;
            if (!wordData) {
                this.showToast('لا توجد بيانات تحليلية لهذه الكلمة', 'info');
                return;
            }
            this.renderConcordanceModal(wordData, verseKey);
            return;
        }
        this.renderConcordanceModal(concordanceData[wordPosition], verseKey);
    }

    renderConcordanceModal(wordData, verseKey) {
        const parts = verseKey.split(':');
        const surah = parts[0];
        const ayah = parts[1];

        const posLabel = wordData.pos_label || wordData.part_of_speech || '';
        const root = wordData.root || '';
        const lemma = wordData.lemma || '';
        const gender = wordData.gender || '';
        const number = wordData.number || '';
        const person = wordData.person || '';
        const tense = wordData.tense || '';
        const voice = wordData.voice || '';
        const wordCase = wordData.case || wordData.word_case || '';
        const translation = wordData.translation || '';

        let detailsHtml = '';
        if (root) detailsHtml += `<tr><td class="fw-bold">الجذر</td><td>${root}</td></tr>`;
        if (lemma) detailsHtml += `<tr><td class="fw-bold">المصدر</td><td>${lemma}</td></tr>`;
        if (posLabel) detailsHtml += `<tr><td class="fw-bold">نوع الكلمة</td><td>${posLabel}</td></tr>`;
        if (gender) detailsHtml += `<tr><td class="fw-bold">الجنس</td><td>${gender === 'masculine' ? 'مذكر' : gender === 'feminine' ? 'مؤنث' : gender}</td></tr>`;
        if (number) detailsHtml += `<tr><td class="fw-bold">العدد</td><td>${number === 'singular' ? 'مفرد' : number === 'dual' ? 'مثنى' : number === 'plural' ? 'جمع' : number}</td></tr>`;
        if (person) detailsHtml += `<tr><td class="fw-bold">الشخص</td><td>${person === '1st' ? 'متكلم' : person === '2nd' ? 'مخاطب' : person === '3rd' ? 'غائب' : person}</td></tr>`;
        if (tense) detailsHtml += `<tr><td class="fw-bold">الزمن</td><td>${tense === 'past' ? 'ماضٍ' : tense === 'present' ? 'مضارع' : tense === 'imperative' ? 'أمر' : tense}</td></tr>`;
        if (voice) detailsHtml += `<tr><td class="fw-bold">المبني</td><td>${voice === 'active' ? 'معلوم' : voice === 'passive' ? 'مجهول' : voice}</td></tr>`;
        if (wordCase) detailsHtml += `<tr><td class="fw-bold">الإعراب</td><td>${wordCase === 'nominative' ? 'رفع' : wordCase === 'accusative' ? 'نصب' : wordCase === 'genitive' ? 'جر' : wordCase}</td></tr>`;
        if (translation) detailsHtml += `<tr><td class="fw-bold">الترجمة</td><td>${translation}</td></tr>`;

        const modalHtml = `
            <div class="modal fade" id="concordanceModal" tabindex="-1">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-microscope me-2"></i>تحليل الكلمة
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                <span class="fs-2 font-amiri">${wordData.word_text || wordData.text_uthmani || ''}</span>
                                <div class="text-muted small">سورة ${surah} - آية ${ayah}</div>
                            </div>
                            <table class="table table-sm table-striped">
                                <tbody>${detailsHtml}</tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        </div>
                    </div>
                </div>
            </div>`;

        const existing = document.getElementById('concordanceModal');
        if (existing) existing.remove();
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('concordanceModal'));
        modal.show();
    }

    /**
     * Show mutashabihat panel for a verse
     */
    showMutashabihat(verseKey) {
        const data = this.mutashabihat[verseKey];
        if (!data || data.length === 0) {
            this.showToast('لا توجد آيات متشابهة لهذه الآية', 'info');
            return;
        }

        const parts = verseKey.split(':');
        const surah = parts[0];
        const ayah = parts[1];

        let itemsHtml = '';
        data.forEach(item => {
            const simBadge = item.similarity_score >= 80 ? 'bg-danger' : item.similarity_score >= 60 ? 'bg-warning' : 'bg-info';
            itemsHtml += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong>${item.surah}:${item.ayah}</strong>
                        <span class="badge ${simBadge}">${item.similarity_score}% تشابه</span>
                    </div>
                    ${item.shared_phrase ? `<div class="small text-muted"><em>العبارة المشتركة: ${item.shared_phrase}</em></div>` : ''}
                </div>`;
        });

        const modalHtml = `
            <div class="modal fade" id="mutashabihatModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-clone me-2"></i>آيات متشابهة
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                <span class="text-muted">الآية: ${surah}:${ayah}</span>
                            </div>
                            <div class="list-group">${itemsHtml}</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        </div>
                    </div>
                </div>
            </div>`;

        const existing = document.getElementById('mutashabihatModal');
        if (existing) existing.remove();
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('mutashabihatModal'));
        modal.show();
    }

    processRecitation(transcript) {
        // Find the best matching verse for the transcription
        let bestMatchIndex = -1;
        let bestSimilarity = 0;

        for (let i = 0; i < this.verses.length; i++) {
            const similarity = this.calculateSimilarity(transcript, this.verses[i].text);
            if (similarity > bestSimilarity) {
                bestSimilarity = similarity;
                bestMatchIndex = i;
            }
        }

        // If we found a good match
        if (bestMatchIndex !== -1 && bestSimilarity >= this.similarityThreshold) {
            // Highlight the matching verse
            this.highlightVerse(bestMatchIndex);

            // If this is a new verse (different from current) and we've made progress
            // Only auto-advance by ONE verse at a time to ensure validation
            if (bestMatchIndex === this.currentVerseIndex + 1) {
                this.currentVerseIndex = bestMatchIndex;
                this.lastProcessedVerseIndex = bestMatchIndex;
                this.errorCountForCurrentVerse = 0;
                this.updateProgress();
                this.updateProgressOnServer(bestSimilarity);

                // Check if all verses completed
                if (this.currentVerseIndex >= this.verses.length - 1) {
                    this.finishRecitation();
                    return;
                }
            } else if (bestMatchIndex > this.currentVerseIndex + 1) {
                // Skipping verses is not allowed - just highlight but don't advance
                this.highlightVerse(this.currentVerseIndex);
            }
        }
        // Note: Error handling for current verse is done in evaluateCurrentVerse (on silence)
        // to avoid double fault sound and ensure proper validation
    }

    highlightVerse(index) {
        this.verseCardsCache.forEach((card, i) => {
            card.classList.toggle('active-verse', i === index);
        });
    }

    playFaultSound() {
        // Use the programmatic error tone since fault.mp3 may not exist
        this.playErrorTone();
        this.setVerseErrorState(this.currentVerseIndex, 2000);
    }

    setVerseErrorState(index, duration = 1500) {
        if (this.verseCardsCache && this.verseCardsCache[index]) {
            const card = this.verseCardsCache[index];

            // Remove any existing timeout to avoid conflicts
            if (card._errorTimeout) {
                clearTimeout(card._errorTimeout);
            }

            // Add error state
            card.classList.add('error-verse');

            // If there have been multiple errors on this verse, keep it red longer
            const actualDuration = this.errorCountForCurrentVerse > 2 ? duration * 2 : duration;

            card._errorTimeout = setTimeout(() => {
                card.classList.remove('error-verse');
                card._errorTimeout = null;
            }, actualDuration);
        }
    }

    initializeVersesDisplay() {
        if (!this.verses || this.verses.length === 0) return;
        if (!this.currentVerseEl) return;

        const container = document.createElement('div');
        container.className = 'verses-container';

        this.verses.forEach((verse, index) => {
            const audioKey = verse.surah + ':' + verse.verse_number;
            const card = document.createElement('div');
            card.className = 'verse-card' + (index === 0 ? ' active-verse' : '');
            card.dataset.index = index;
            card.dataset.key = audioKey;
            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'button');
            card.setAttribute('aria-label', 'آية ' + (index + 1) + ' من ' + audioKey);

            // Click handler for manual navigation
            card.addEventListener('click', () => {
                if (!this.isEvaluating && !this.isEnding) {
                    this.currentVerseIndex = index;
                    this.highlightVerse(index);
                    this.updateProgress();
                }
            });

            // Keyboard handler
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    card.click();
                }
            });

            const header = document.createElement('div');
            header.className = 'd-flex justify-content-between align-items-center mb-2';

            const badge = document.createElement('span');
            badge.className = 'badge verse-badge bg-secondary';
            badge.textContent = audioKey;

            const counter = document.createElement('span');
            counter.className = 'verse-counter';
            counter.textContent = (index + 1) + ' / ' + this.verses.length;

            header.appendChild(badge);
            header.appendChild(counter);

            card.appendChild(header);

            // Word-level display if available
            const words = this.verseWords[audioKey];
            const tajweed = this.tajweedRules[audioKey];
            if (words && words.length > 0) {
                const wordsContainer = document.createElement('div');
                wordsContainer.className = 'verse-words-container';
                wordsContainer.dir = 'rtl';
                words.forEach((word, wi) => {
                    const wordSpan = document.createElement('span');
                    wordSpan.className = 'verse-word';
                    wordSpan.dataset.position = wi;
                    wordSpan.dataset.key = audioKey;
                    let wordText = word.text_uthmani || word.text_imlaei || '';
                    // Apply tajweed highlighting to this word if rules exist
                    if (tajweed && tajweed.length > 0) {
                        const wordRules = tajweed.filter(r => r.word_position === (wi + 1));
                        if (wordRules.length > 0) {
                            wordSpan.innerHTML = this.applyTajweedToWord(wordText, wordRules);
                        } else {
                            wordSpan.textContent = wordText;
                        }
                    } else {
                        wordSpan.textContent = wordText;
                    }
                    // Click handler for word concordance
                    wordSpan.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.showWordConcordance(audioKey, wi);
                    });
                    wordsContainer.appendChild(wordSpan);
                });
                card.appendChild(wordsContainer);
            } else {
                const text = document.createElement('p');
                text.className = 'verse-text mb-0';
                text.dir = 'rtl';
                // Apply tajweed to full text if no word-level data
                if (tajweed && tajweed.length > 0) {
                    text.innerHTML = this.applyTajweedToText(verse.text, tajweed);
                } else {
                    text.textContent = verse.text;
                }
                card.appendChild(text);
            }

            // Action buttons container
            const btnContainer = document.createElement('div');
            btnContainer.className = 'd-flex gap-2 mt-2 flex-wrap';

            // Audio player button
            const audioUrl = this.audioUrls[audioKey];
            if (audioUrl) {
                const audioBtn = document.createElement('button');
                audioBtn.className = 'btn btn-sm btn-outline-success verse-audio-btn';
                audioBtn.innerHTML = '<i class="fas fa-volume-up me-1"></i>سماع التلاوة';
                audioBtn.setAttribute('aria-label', 'تشغيل تلاوة آية ' + audioKey);
                audioBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.playVerseAudio(audioUrl, audioBtn, audioKey);
                });
                btnContainer.appendChild(audioBtn);
            }

            // Mutashabihat button
            if (this.mutashabihat[audioKey] && this.mutashabihat[audioKey].length > 0) {
                const mutaBtn = document.createElement('button');
                mutaBtn.className = 'btn btn-sm mutashabihat-btn';
                mutaBtn.innerHTML = '<i class="fas fa-clone me-1"></i>متشابهات (' + this.mutashabihat[audioKey].length + ')';
                mutaBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.showMutashabihat(audioKey);
                });
                btnContainer.appendChild(mutaBtn);
            }

            if (btnContainer.children.length > 0) {
                card.appendChild(btnContainer);
            }

            container.appendChild(card);
        });

        this.currentVerseEl.innerHTML = '';
        this.currentVerseEl.appendChild(container);
        // Cache the verse card elements for reuse
        this.verseCardsCache = container.querySelectorAll('.verse-card');
    }

    refreshVersesDisplay() {
        if (!this.verses || this.verses.length === 0) return;
        if (this.viewMode === 'quran') {
            this.initializeQuranPageView();
        } else if (this.viewMode === 'mushaf') {
            this.initializeMushafView();
        } else {
            this.initializeVersesDisplay();
        }
        this.highlightVerse(this.currentVerseIndex);
    }

    setViewMode(mode) {
        this.viewMode = mode;
        const viewModeCards = document.getElementById('viewModeCards');
        const viewModeQuran = document.getElementById('viewModeQuran');
        const viewModeMushaf = document.getElementById('viewModeMushaf');
        if (viewModeCards) viewModeCards.classList.remove('active');
        if (viewModeQuran) viewModeQuran.classList.remove('active');
        if (viewModeMushaf) viewModeMushaf.classList.remove('active');
        const activeBtn = mode === 'cards' ? viewModeCards : mode === 'quran' ? viewModeQuran : viewModeMushaf;
        if (activeBtn) activeBtn.classList.add('active');
        this.refreshVersesDisplay();
    }

    initializeQuranPageView() {
        if (!this.verses || this.verses.length === 0) return;
        if (!this.currentVerseEl) return;

        const container = document.createElement('div');
        container.className = 'verses-container quran-page-view';

        const surahNum = this.verses[0]?.surah || 1;
        const surahName = this.getSurahName(surahNum);

        const headerDiv = document.createElement('div');
        headerDiv.className = 'surah-header';

        const title = document.createElement('h3');
        title.className = 'surah-title';
        title.textContent = 'سورة ' + surahName;
        headerDiv.appendChild(title);

        if (surahNum !== 9) {
            const bismillah = document.createElement('p');
            bismillah.className = 'bismillah';
            bismillah.textContent = 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ';
            headerDiv.appendChild(bismillah);
        }

        container.appendChild(headerDiv);

        this.verses.forEach((verse, index) => {
            const line = document.createElement('div');
            line.className = 'quran-verse-line' + (index === 0 ? ' active-verse' : '');
            line.dataset.index = index;
            line.setAttribute('tabindex', '0');
            line.setAttribute('role', 'button');
            line.setAttribute('aria-label', 'آية ' + (index + 1));

            line.addEventListener('click', () => {
                if (!this.isEvaluating && !this.isEnding) {
                    this.currentVerseIndex = index;
                    this.highlightVerse(index);
                    this.updateProgress();
                }
            });

            line.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    line.click();
                }
            });

            const verseText = document.createElement('span');
            verseText.className = 'quran-verse-text';
            verseText.dir = 'rtl';
            const audioKey = verse.surah + ':' + verse.verse_number;
            const tajweed = this.tajweedRules[audioKey];
            if (tajweed && tajweed.length > 0) {
                verseText.innerHTML = this.applyTajweedToText(verse.text, tajweed);
            } else {
                verseText.textContent = verse.text;
            }

            const verseNum = document.createElement('span');
            verseNum.className = 'quran-verse-number';
            verseNum.textContent = this.convertToArabicNumerals(verse.verse_number);

            line.appendChild(verseText);
            line.appendChild(verseNum);

            // Audio player button (inline, small)
            const audioUrl = this.audioUrls[audioKey];
            if (audioUrl) {
                const audioBtn = document.createElement('button');
                audioBtn.className = 'btn btn-sm btn-outline-success verse-audio-btn-inline';
                audioBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
                audioBtn.setAttribute('aria-label', 'تشغيل تلاوة آية ' + audioKey);
                audioBtn.title = 'سماع التلاوة';
                audioBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.playVerseAudio(audioUrl, audioBtn, audioKey);
                });
                line.appendChild(audioBtn);
            }

            container.appendChild(line);
        });

        const navDiv = document.createElement('div');
        navDiv.className = 'quran-page-nav';

        const prevBtn = document.createElement('button');
        prevBtn.className = 'verse-nav-btn';
        prevBtn.textContent = 'السابق';
        prevBtn.disabled = this.currentVerseIndex === 0;
        prevBtn.addEventListener('click', () => {
            if (this.currentVerseIndex > 0) {
                this.currentVerseIndex--;
                this.highlightVerse(this.currentVerseIndex);
                this.updateProgress();
            }
        });

        const nextBtn = document.createElement('button');
        nextBtn.className = 'verse-nav-btn';
        nextBtn.textContent = 'التالي';
        nextBtn.disabled = this.currentVerseIndex >= this.verses.length - 1;
        nextBtn.addEventListener('click', () => {
            if (this.currentVerseIndex < this.verses.length - 1) {
                this.currentVerseIndex++;
                this.highlightVerse(this.currentVerseIndex);
                this.updateProgress();
            }
        });

        navDiv.appendChild(prevBtn);
        navDiv.appendChild(nextBtn);
        container.appendChild(navDiv);

        this.currentVerseEl.innerHTML = '';
        this.currentVerseEl.appendChild(container);
        this.verseCardsCache = container.querySelectorAll('.quran-verse-line');
    }

    initializeMushafView() {
        if (!this.verses || this.verses.length === 0) return;
        if (!this.currentVerseEl) return;

        const container = document.createElement('div');
        container.className = 'verses-container mushaf-page-view';

        const surahNum = this.verses[0]?.surah || 1;
        const surahName = this.getSurahName(surahNum);

        // Page header with surah name and layout badge
        const headerDiv = document.createElement('div');
        headerDiv.className = 'mushaf-header';

        const layoutBadge = document.createElement('span');
        layoutBadge.className = 'mushaf-layout-badge';
        layoutBadge.textContent = this.mushafLayout === 'KFGQPC_V4' ? 'مصحف المدينة' : 'هندوباك';

        const title = document.createElement('h3');
        title.className = 'mushaf-title';
        title.textContent = 'سورة ' + surahName;

        headerDiv.appendChild(layoutBadge);
        headerDiv.appendChild(title);

        if (surahNum !== 9) {
            const bismillah = document.createElement('p');
            bismillah.className = 'mushaf-bismillah';
            bismillah.textContent = 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ';
            headerDiv.appendChild(bismillah);
        }

        container.appendChild(headerDiv);

        // Render verses in mushaf style - 15 lines per page simulation
        const linesContainer = document.createElement('div');
        linesContainer.className = 'mushaf-lines';

        let currentLineNum = 1;
        let currentLine = document.createElement('div');
        currentLine.className = 'mushaf-line';
        currentLine.dataset.line = currentLineNum;

        this.verses.forEach((verse, index) => {
            const audioKey = verse.surah + ':' + verse.verse_number;

            // Wrap each verse in a mushaf line
            const verseWrapper = document.createElement('span');
            verseWrapper.className = 'mushaf-verse-wrapper' + (index === 0 ? ' active-verse' : '');
            verseWrapper.dataset.index = index;
            verseWrapper.dataset.key = audioKey;

            // Tajweed coloring
            const tajweed = this.tajweedRules[audioKey];
            let verseHtml = '';
            if (this.verseWords[audioKey]) {
                // Word-by-word mushaf rendering with click handler
                this.verseWords[audioKey].forEach((word, wi) => {
                    const wordRules = tajweed ? tajweed.filter(r => r.word_position === (wi + 1)) : [];
                    let wordText = word.text_uthmani || word.text_imlaei || '';
                    let wordHtml = wordText;
                    if (wordRules.length > 0) {
                        wordHtml = this.applyTajweedToWord(wordText, wordRules);
                    }
                    verseHtml += `<span class="mushaf-word" data-position="${wi}" data-key="${audioKey}">${wordHtml}</span> `;
                });
            } else {
                if (tajweed && tajweed.length > 0) {
                    verseHtml = this.applyTajweedToText(verse.text, tajweed);
                } else {
                    verseHtml = verse.text;
                }
            }

            verseWrapper.innerHTML = verseHtml;

            // Verse number in ornate circle
            const verseNumEl = document.createElement('span');
            verseNumEl.className = 'mushaf-verse-number';
            verseNumEl.textContent = this.convertToArabicNumerals(verse.verse_number);
            verseWrapper.appendChild(verseNumEl);

            // Click to navigate
            verseWrapper.addEventListener('click', () => {
                if (!this.isEvaluating && !this.isEnding) {
                    this.currentVerseIndex = index;
                    this.highlightVerse(index);
                    this.updateProgress();
                }
            });

            currentLine.appendChild(verseWrapper);

            // Simulate 15 lines per page
            if (currentLine.children.length >= 2 || currentLineNum >= 15) {
                linesContainer.appendChild(currentLine);
                currentLineNum++;
                currentLine = document.createElement('div');
                currentLine.className = 'mushaf-line';
                currentLine.dataset.line = currentLineNum;
            }
        });

        if (currentLine.children.length > 0) {
            linesContainer.appendChild(currentLine);
        }

        container.appendChild(linesContainer);

        // Page footer with page info
        const footerDiv = document.createElement('div');
        footerDiv.className = 'mushaf-footer';
        const pageInfo = document.createElement('span');
        pageInfo.className = 'mushaf-page-info';
        pageInfo.textContent = 'جزء ' + this.getJuzLabel(surahNum);
        footerDiv.appendChild(pageInfo);
        container.appendChild(footerDiv);

        this.currentVerseEl.innerHTML = '';
        this.currentVerseEl.appendChild(container);
        this.verseCardsCache = container.querySelectorAll('.mushaf-verse-wrapper');

        // Add click handlers to words for concordance
        container.querySelectorAll('.mushaf-word').forEach(wordEl => {
            wordEl.addEventListener('click', (e) => {
                e.stopPropagation();
                const key = wordEl.dataset.key;
                const pos = parseInt(wordEl.dataset.position);
                if (key && !isNaN(pos)) {
                    this.showWordConcordance(key, pos);
                }
            });
        });
    }

    getJuzLabel(surahNum) {
        // Approximate juz mapping for display
        const juzMap = {1:1,2:2,3:3,4:4,5:5,6:6,7:7,8:8,9:9,10:10,11:11,12:12,18:15,19:16,23:18,26:19,28:20,33:22,36:23,40:24,46:26,51:27,58:28,67:29,78:30};
        return juzMap[surahNum] || Math.min(30, Math.ceil(surahNum / 3.8));
    }

    getSurahName(num) {
        const surahs = ['', 'الفاتحة', 'البقرة', 'آل عمران', 'النساء', 'المائدة', 'الأنعام', 'الأعراف', 'الأنفال', 'التوبة', 'يونس', 'هود', 'يوسف', 'الرعد', 'إبراهيم', 'الحجر', 'النحل', 'الإسراء', 'الكهف', 'مريم', 'طه', 'الأنبياء', 'الحج', 'المؤمنون', 'النور', 'الفرقان', 'الشعراء', 'النمل', 'القصص', 'العنكبوت', 'الروم', 'لقمان', 'السجدة', 'الأحزاب', 'سبأ', 'فاطر', 'يس', 'الصافات', 'ص', 'الزمر', 'غافر', 'فصلت', 'الشورى', 'الزخرف', 'الدخان', 'الجاثية', 'الأحقاف', 'محمد', 'الفتح', 'الحجرات', 'ق', 'الذاريات', 'الطور', 'النجم', 'القمر', 'الرحمن', 'الواقعة', 'الحديد', 'المجادلة', 'الحشر', 'الممتحنة', 'الصف', 'الجمعة', 'المنافقون', 'التغابن', 'الطلاق', 'التحريم', 'الملك', 'القلم', 'الحاقة', 'المعارج', 'نوح', 'الجن', 'المزمل', 'المدثر', 'القيامة', 'الإنسان', 'المرسلات', 'النبأ', 'النازعات', 'عبس', 'التكوير', 'الإنفطار', 'المطففين', 'الإنشقاق', 'البروج', 'الطارق', 'الأعلى', 'الغاشية', 'الفجر', 'البلد', 'الشمس', 'الليل', 'الضحى', 'الشرح', 'التين', 'العلق', 'القدر', 'البينة', 'الزلزلة', 'العاديات', 'القارعة', 'التكاثر', 'العصر', 'الهمزة', 'الفيل', 'قريش', 'الماعون', 'الكوثر', 'الكافرون', 'النصر', 'المسد', 'الإخلاص', 'الفلق', 'الناس'];
        return surahs[num] || num;
    }

    convertToArabicNumerals(num) {
        const arabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        return String(num).split('').map(d => arabicNumerals[parseInt(d)] || d).join('');
    }

    // ==========================================================
    // SESSION TIMER
    // ==========================================================
    startSessionTimer() {
        this.sessionStartTime = Date.now();
        this.sessionTimerEl = document.getElementById('sessionTimer');
        this.sessionTimerSectionEl = document.getElementById('sessionTimerSection');
        if (this.sessionTimerSectionEl) {
            this.sessionTimerSectionEl.style.display = 'block';
        }
        this.sessionTimerInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - this.sessionStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
            const seconds = (elapsed % 60).toString().padStart(2, '0');
            if (this.sessionTimerEl) {
                this.sessionTimerEl.textContent = minutes + ':' + seconds;
            }
        }, 1000);
    }

    stopSessionTimer() {
        if (this.sessionTimerInterval) {
            clearInterval(this.sessionTimerInterval);
            this.sessionTimerInterval = null;
        }
    }

    // ==========================================================
    // ACCURACY CHART (Chart.js)
    // ==========================================================
    initAccuracyChart() {
        const wrapper = document.getElementById('accuracyChartWrapper');
        if (wrapper) wrapper.style.display = 'block';

        const ctx = document.getElementById('accuracyChart')?.getContext('2d');
        if (!ctx) return;

        this.accuracyData = [];
        this.verseAccuracyMap = [];

        if (this.accuracyChart) {
            this.accuracyChart.destroy();
        }

        this.accuracyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'دقة التلاوة (%)',
                    data: [],
                    borderColor: '#1a7a4a',
                    backgroundColor: 'rgba(26, 122, 74, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#1a7a4a',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'الدقة: ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        ticks: { stepSize: 20 }
                    },
                    x: {
                        display: false
                    }
                }
            }
        });
    }

    updateAccuracyChart(verseIndex, similarity) {
        if (!this.accuracyChart) return;

        this.verseAccuracyMap[verseIndex] = similarity;

        // Rebuild chart data from verseAccuracyMap to keep order
        const labels = [];
        const data = [];
        for (let i = 0; i < this.verses.length; i++) {
            if (this.verseAccuracyMap[i] !== undefined) {
                labels.push('آية ' + (i + 1));
                data.push(this.verseAccuracyMap[i]);
            }
        }

        this.accuracyChart.data.labels = labels;
        this.accuracyChart.data.datasets[0].data = data;
        this.accuracyChart.update();
    }

    async evaluateCurrentVerse() {
        // Skip if ending or if we've already processed this verse
        if (this.isEnding || this.currentVerseIndex <= this.lastProcessedVerseIndex) {
            this.isEvaluating = false;
            return;
        }

        // Only evaluate if we have a transcript
        if (!this.transcriptText || this.transcriptText.trim().length === 0) {
            this.isEvaluating = false;
            return;
        }

        const verse = this.verses[this.currentVerseIndex];
        const expectedText = verse.text;
        const similarity = this.calculateSimilarity(this.transcriptText, expectedText);

        // Update accuracy chart
        this.updateAccuracyChart(this.currentVerseIndex, similarity);

        if (similarity < this.similarityThreshold) {
            this.errorCountForCurrentVerse++;
            await this.recordError(verse, similarity);
            this.showErrorState(verse);
            this.playFaultSound();

            // Auto-advance after max errors on same verse
            if (this.errorCountForCurrentVerse >= this.maxErrorsPerVerse) {
                this.lastProcessedVerseIndex = this.currentVerseIndex;
                if (this.currentVerseIndex < this.verses.length - 1) {
                    this.currentVerseIndex++;
                    this.errorCountForCurrentVerse = 0;
                    this.highlightVerse(this.currentVerseIndex);
                    this.updateProgress();
                } else {
                    this.finishRecitation();
                    this.isEvaluating = false;
                    return;
                }
            }
        } else {
            // Successfully recited - auto advance to next verse
            this.lastProcessedVerseIndex = this.currentVerseIndex;
            this.errorCountForCurrentVerse = 0;
            if (this.currentVerseIndex < this.verses.length - 1) {
                this.currentVerseIndex++;
                this.highlightVerse(this.currentVerseIndex);
                this.updateProgress();
            } else {
                // Last verse completed
                this.finishRecitation();
                this.isEvaluating = false;
                return;
            }
        }

        await this.updateProgressOnServer(similarity);
        this.isEvaluating = false;
        this.transcriptText = '';

        setTimeout(() => {
            if (this.isRecording && !this.isEnding && this.currentVerseIndex < this.verses.length) {
                if (this.mediaRecorder.state === 'inactive') {
                    this.mediaRecorder.start(1000);
                }
                this.startSilenceDetection();
            }
        }, 500);
    }

    // Character-based Levenshtein for proper UTF-8/Arabic support
    mbLevenshtein(str1, str2) {
        const chars1 = Array.from(str1);
        const chars2 = Array.from(str2);
        const len1 = chars1.length;
        const len2 = chars2.length;

        if (len1 === 0) return len2;
        if (len2 === 0) return len1;

        const matrix = [];
        for (let i = 0; i <= len1; i++) {
            matrix[i] = [i];
        }
        for (let j = 0; j <= len2; j++) {
            matrix[0][j] = j;
        }

        for (let i = 1; i <= len1; i++) {
            for (let j = 1; j <= len2; j++) {
                const cost = (chars1[i - 1] === chars2[j - 1]) ? 0 : 1;
                matrix[i][j] = Math.min(
                    matrix[i - 1][j] + 1,
                    matrix[i][j - 1] + 1,
                    matrix[i - 1][j - 1] + cost
                );
            }
        }
        return matrix[len1][len2];
    }

    calculateSimilarity(text1, text2) {
        const normalized1 = this.normalizeArabic(text1);
        const normalized2 = this.normalizeArabic(text2);
        if (normalized1 === normalized2) return 100;
        if (normalized1.length === 0 || normalized2.length === 0) return 0;
        const distance = this.mbLevenshtein(normalized1, normalized2);
        const maxLength = Math.max(normalized1.length, normalized2.length);
        return Math.round((1 - distance / maxLength) * 100);
    }

    normalizeArabic(text) {
        if (!text) return '';
        // Remove Arabic diacritics (tashkeel): \x{064B}-\x{065F} and \x{0670}
        text = text.replace(/[\u064B-\u065F\u0670]/g, '');
        // Normalize alef variations: أ إ آ ا -> ا
        text = text.replace(/[آأإا]/g, 'ا');
        // Normalize teh marbuta:ة -> ت
        text = text.replace(/ة/g, 'ت');
        // Normalize ya-al-maqsura: ى -> ي
        text = text.replace(/ى/g, 'ي');
        // Remove non-Arabic characters but keep Arabic letters, numbers, spaces
        text = text.replace(/[^\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF\p{N}\s]/gu, '');
        // Normalize whitespace
        text = text.replace(/\s+/g, ' ');
        return text.trim();
    }

    async recordError(verse, similarity) {
        // Always update local state first
        this.totalErrors++;
        this.errorCountEl.textContent = this.totalErrors;

        try {
            const response = await fetch(this.errorUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.apiToken,
                },
                credentials: 'include',
                body: JSON.stringify({
                    session_id: this.sessionId,
                    surah: verse.surah,
                    verse_number: verse.verse_number,
                    verse_index: this.currentVerseIndex,
                    expected_text: verse.text,
                    detected_text: this.transcriptText,
                    similarity: similarity,
                    error_type: similarity < 50 ? 'unclear' : 'mismatch'
                })
            });

            if (!response.ok) {
                throw new Error('Server responded with ' + response.status);
            }
        } catch (error) {
            console.error('Failed to record error to server:', error);
        }
    }

    async updateProgressOnServer(similarity) {
        try {
            await fetch(this.progressUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.apiToken,
                },
                credentials: 'include',
                body: JSON.stringify({
                    session_id: this.sessionId,
                    verse_index: this.currentVerseIndex,
                    similarity: similarity
                })
            });
        } catch (error) {
            console.error('Error updating progress:', error);
        }
    }

    showVerse(index) {
        if (index >= this.verses.length) {
            this.finishRecitation();
            return;
        }
        // Only initialize once, then just highlight
        if (!this.verseCardsCache || this.verseCardsCache.length === 0) {
            this.initializeVersesDisplay();
        }
        this.highlightVerse(index);
    }

    showErrorState(verse) {
        this.setVerseErrorState(this.currentVerseIndex, 2000);
    }

    updateTranscript(text) {
        if (!this.transcriptEl) return;
        if (text && text.trim().length > 0) {
            const span = document.createElement('span');
            span.className = 'text-success';
            span.textContent = text;
            this.transcriptEl.innerHTML = '';
            this.transcriptEl.appendChild(span);
        } else {
            this.transcriptEl.innerHTML = '<span class="text-muted">جارٍ الاستماع...</span>';
        }
    }

    nextVerse() {
        if (this.isEvaluating || this.isEnding) return;
        if (this.currentVerseIndex < this.verses.length - 1) {
            this.currentVerseIndex++;
            this.lastProcessedVerseIndex = this.currentVerseIndex;
            this.errorCountForCurrentVerse = 0;
            this.updateProgress();
            this.highlightVerse(this.currentVerseIndex);
            this.transcriptText = '';
        } else {
            this.finishRecitation();
        }
    }

    updateProgress() {
        this.progressEl.textContent = this.currentVerseIndex + ' / ' + this.verses.length;
        this.versesCountEl.textContent = this.verses.length;

        if (this.progressBarEl && this.verses.length > 0) {
            const percentage = (this.currentVerseIndex / this.verses.length) * 100;
            this.progressBarEl.style.width = percentage + '%';
            this.progressBarEl.setAttribute('aria-valuenow', Math.round(percentage));
        }
    }

    updateStatus(text) {
        this.statusEl.textContent = text;
    }

    updateStatusIndicator(state) {
        const indicator = document.getElementById('statusIndicator');
        if (!indicator) return;
        indicator.className = 'status-indicator ' + state;
    }

    showToast(message, type = 'info') {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            this._fallbackToast(message, type);
        }
    }

    showError(message) {
        this.showToast(message, 'error');
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    _fallbackToast(message, type) {
        if (!this.toastContainerEl) return;
        const toast = document.createElement('div');
        toast.className = 'toast-' + type;
        const icon = type === 'error' ? 'fa-exclamation-circle' : type === 'success' ? 'fa-check-circle' : 'fa-info-circle';
        toast.innerHTML = '<i class="fas ' + icon + '"></i><span>' + this.escapeHtml(message) + '</span>';
        this.toastContainerEl.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }


    cleanupRecording() {
        this.stopSilenceDetection();

        if (this.mediaRecorder) {
            if (this.mediaRecorder.state === 'recording' || this.mediaRecorder.state === 'paused') {
                this.mediaRecorder.stop();
            }
            // Stop all stream tracks to release microphone
            if (this.mediaRecorder.stream) {
                this.mediaRecorder.stream.getTracks().forEach(track => track.stop());
            }
            this.mediaRecorder = null;
        }

        if (this.audioContext) {
            this.audioContext.close();
            this.audioContext = null;
        }
        this.analyser = null;

        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.close();
        }

        // Stop speech recognition if active
        if (this.speechRecognition) {
            try {
                this.speechRecognition.stop();
            } catch (e) {
                console.warn('Speech recognition stop error:', e);
            }
        }
    }

    async stopRecitation() {
        if (!this.sessionId) return;

        this.isEnding = true;
        this.isRecording = false;
        this.cleanupRecording();

        try {
            const endUrl = this.reportUrlTemplate.replace('__ID__', this.sessionId).replace('/report', '/end');
            const response = await fetch(endUrl, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + this.apiToken,
                },
                credentials: 'include',
            });

            if (!response.ok) {
                throw new Error('Server responded with ' + response.status);
            }

            const data = await response.json();
            if (data.success) {
                this.showReport(data.data);
            } else {
                throw new Error(data.error || 'Unknown error');
            }
        } catch (error) {
            console.error('Error ending session:', error);
        }
        this.resetUI();
    }

    finishRecitation() {
        this.isEnding = true;
        this.isRecording = false;
        this.cleanupRecording();
        this.showSuccess('أحسنت! أكملت التسميع بنجاح');
        this.resetUI();
    }

    resetUI() {
        this.stopSessionTimer();
        const timerSection = document.getElementById('sessionTimerSection');
        if (timerSection) timerSection.style.display = 'none';
        const chartWrapper = document.getElementById('accuracyChartWrapper');
        if (chartWrapper) chartWrapper.style.display = 'none';

        this.startBtn.disabled = true;
        this.startBtn.classList.remove('recording');
        this.startBtn.innerHTML = '<i class="fas fa-microphone me-2"></i>ابدأ التسميع';
        this.stopBtn.classList.add('d-none');
        this.nextVerseBtn.classList.add('d-none');
        this.updateStatusIndicator('');
        this.updateStatus('في انتظار البدء');
        if (this.currentVerseEl) {
            this.currentVerseEl.innerHTML = '<div class="idle-state"><i class="fas fa-book-quran fa-4x mb-3 opacity-25"></i><h5>اختر الإعدادات ثم اضغط "عرض الآيات"</h5><p class="text-muted">ستظهر الآيات هنا للمعاينة قبل بدء التسميع</p></div>';
        }
        if (this.transcriptEl) {
            this.transcriptEl.innerHTML = '<span class="text-muted">جارٍ الاستماع...</span>';
        }
        this.sessionId = null;
        this.verses = [];
        this.currentVerseIndex = 0;
        this.totalErrors = 0;
        this.verseCardsCache = null;
        this.audioChunks = [];
        this.isEvaluating = false;
        this.isEnding = false;
        this.isPreviewMode = false;
        this.lastProcessedVerseIndex = -1;
        this.errorCountForCurrentVerse = 0;
        this.wordSegments = {};
        this.verseWords = {};
        this.tajweedRules = {};
        this.concordance = {};
        this.mutashabihat = {};
        this.previewBtn.disabled = false;
        this.previewBtn.innerHTML = '<i class="fas fa-eye me-2"></i>عرض الآيات';
    }

    showReport(report) {
        const session = report.session || {};
        const accuracy = session.average_similarity || 0;
        const totalVerses = session.total_verses || 0;
        const errorCount = report.errors ? report.errors.length : 0;
        let errorsHtml = '';
        if (report.errors && report.errors.length > 0) {
            errorsHtml = report.errors.map(err => '<li class="list-group-item"><strong>' + err.surah + ':' + err.verse_number + '</strong> - التشابه: ' + err.similarity.toFixed(1) + '%<br><small class="text-muted">المتوقع: ' + err.expected_text + '</small><br><small class="text-danger">ما تم نطقه: ' + err.detected_text + '</small></li>').join('');
        } else {
            errorsHtml = '<li class="list-group-item text-success">لا توجد أخطاء!</li>';
        }
        const reportHtml = '<div class="modal fade" id="reportModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>تقرير التسميع</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row text-center mb-4"><div class="col-md-4"><h3 class="text-primary">' + accuracy.toFixed(1) + '%</h3><p>الدقة</p></div><div class="col-md-4"><h3 class="text-success">' + totalVerses + '</h3><p>عدد الآيات</p></div><div class="col-md-4"><h3 class="text-danger">' + errorCount + '</h3><p>عدد الأخطاء</p></div></div><h6>تفاصيل الأخطاء:</h6><ul class="list-group">' + errorsHtml + '</ul></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button></div></div></div></div>';
        const existingModal = document.getElementById('reportModal');
        if (existingModal) { existingModal.remove(); }
        document.body.insertAdjacentHTML('beforeend', reportHtml);
        const modal = new bootstrap.Modal(document.getElementById('reportModal'));
        modal.show();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.liveRecitation = new LiveRecitationController();
    window.toggleViewMode = (mode) => {
        if (window.liveRecitation) {
            window.liveRecitation.setViewMode(mode);
        }
    };
});
