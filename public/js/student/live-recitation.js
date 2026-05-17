class LiveRecitationController {
    constructor() {
        this.ws = null;
        this.mediaRecorder = null;
        this.audioContext = null;
        this.sessionId = null;
        this.currentSession = null;
        this.verses = [];
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

        // Web Speech API fallback
        this.speechRecognition = null;
        this.useWebSpeech = false;

        this.init();
    }

    init() {
        this.modeSelect = document.getElementById('modeSelect');
        this.startPageInput = document.getElementById('startPage');

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
        this.startBtn.addEventListener('click', () => this.startRecitation());
        this.stopBtn.addEventListener('click', () => this.stopRecitation());
        this.nextVerseBtn.addEventListener('click', () => this.nextVerse());

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

        // Check if we have API token (from meta tag or previous fetch)
        if (!this.apiToken) {
            try {
                const tokenResponse = await fetch('/student/live-recitation/token', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
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

            const response = await fetch('/api/student/live-recitation/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.apiToken,
                    'X-XSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(requestBody)
            });
            const data = await response.json();

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

            // Validate API response has required data
            if (!data.data || !data.data.verses || !Array.isArray(data.data.verses) || data.data.verses.length === 0) {
                console.error('Invalid API response - missing verses:', data);
                this.showError('لم يتم العثور على آيات للنطاق المحدد');
                this.startBtn.disabled = false;
                return;
            }

            this.sessionId = data.data.session_id;
            this.verses = data.data.verses;
            this.currentSession = {
                id: data.data.session_id,
                audioUrls: data.data.audio_urls,
                websocketToken: data.data.websocket_token,
                websocketUrl: data.data.websocket_url
            };
            this.currentVerseIndex = 0;
            this.totalErrors = 0;
            this.transcriptText = '';
            this.isEnding = false;
            this.lastProcessedVerseIndex = -1;
            this.errorCountForCurrentVerse = 0;
            this.updateProgress();
            this.initializeVersesDisplay();
            this.highlightVerse(0);
            this.stopBtn.classList.remove('d-none');
            this.nextVerseBtn.classList.remove('d-none');

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

        // If similarity drops below threshold during recitation, show warning
        if (similarity < this.similarityThreshold && similarity > 20) {
            this.lastErrorFeedbackTime = now;
            // Show real-time error feedback - verse glows red to indicate mistake
            this.setVerseErrorState(this.currentVerseIndex, 1000);
            // Play short error tone for immediate feedback
            this.playErrorTone();
        }
    }

    playErrorTone() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 400;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.15, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.15);

            oscillator.onended = () => audioContext.close();
        } catch (e) {
            console.warn('Error tone playback failed:', e);
        }
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
        this.faultAudio.play().catch((error) => {
            console.warn('Fault audio playback failed:', error.message);
        });
        this.setVerseErrorState(this.currentVerseIndex, 1500);
    }

    setVerseErrorState(index, duration = 1500) {
        if (this.verseCardsCache[index]) {
            this.verseCardsCache[index].classList.add('error-verse');
            setTimeout(() => {
                this.verseCardsCache[index].classList.remove('error-verse');
            }, duration);
        }
    }

    initializeVersesDisplay() {
        if (!this.verses || this.verses.length === 0) return;
        if (!this.currentVerseEl) return;

        const container = document.createElement('div');
        container.className = 'verses-container';

        this.verses.forEach((verse, index) => {
            const card = document.createElement('div');
            card.className = 'verse-card' + (index === 0 ? ' active-verse' : '');
            card.dataset.index = index;
            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'button');
            card.setAttribute('aria-label', 'آية ' + (index + 1) + ' من ' + verse.surah + ':' + verse.verse_number);

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
            badge.textContent = verse.surah + ':' + verse.verse_number;

            const counter = document.createElement('span');
            counter.className = 'verse-counter';
            counter.textContent = (index + 1) + ' / ' + this.verses.length;

            header.appendChild(badge);
            header.appendChild(counter);

            const text = document.createElement('p');
            text.className = 'verse-text mb-0';
            text.dir = 'rtl';
            text.textContent = verse.text;

            card.appendChild(header);
            card.appendChild(text);
            container.appendChild(card);
        });

        this.currentVerseEl.innerHTML = '';
        this.currentVerseEl.appendChild(container);
        // Cache the verse card elements for reuse
        this.verseCardsCache = container.querySelectorAll('.verse-card');
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
            const response = await fetch('/api/student/live-recitation/error', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.apiToken,
                    'X-XSRF-TOKEN': this.getCsrfToken()
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
            await fetch('/api/student/live-recitation/progress', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.apiToken,
                    'X-XSRF-TOKEN': this.getCsrfToken()
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

    showError(message) {
        if (!this.toastContainerEl) return;

        const toast = document.createElement('div');
        toast.className = 'toast-error';
        toast.innerHTML = '<i class="fas fa-exclamation-circle"></i><span>' + this.escapeHtml(message) + '</span>';
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
            const response = await fetch(`/api/student/live-recitation/session/${this.sessionId}/end`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + this.apiToken,
                    'X-XSRF-TOKEN': this.getCsrfToken()
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
        this.resetUI();
    }

    resetUI() {
        this.startBtn.disabled = false;
        this.startBtn.classList.remove('recording');
        this.startBtn.innerHTML = '<i class="fas fa-microphone me-2"></i>ابدأ التسميع';
        this.stopBtn.classList.add('d-none');
        this.nextVerseBtn.classList.add('d-none');
        this.updateStatusIndicator('');
        this.updateStatus('في انتظار البدء');
        if (this.currentVerseEl) {
            this.currentVerseEl.innerHTML = '<div class="idle-state"><i class="fas fa-microphone-alt fa-4x mb-3 opacity-25"></i><h5>اضغط "ابدأ التسميع" للبدء</h5><p class="text-muted">سيتم الاستماع إليك ومقارنة نطقك بالنص القرآني</p></div>';
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
        this.lastProcessedVerseIndex = -1;
        this.errorCountForCurrentVerse = 0;
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
});
