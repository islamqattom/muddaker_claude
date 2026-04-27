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
        this.verseCardsCache = null;
        this.silenceRafId = null;
        this.isEnding = false;
        this.lastProcessedVerseIndex = -1;
        this.errorCountForCurrentVerse = 0;
        this.maxErrorsPerVerse = 3;
        this.init();
    }

    init() {
        this.modeSelect = document.getElementById('modeSelect');
        this.startPageInput = document.getElementById('startPage');
        this.endPageInput = document.getElementById('endPage');
        this.endPageInputContainer = document.getElementById('endPageInput');
        this.similarityThresholdInput = document.getElementById('similarityThreshold');
        this.startBtn = document.getElementById('startRecitation');
        this.stopBtn = document.getElementById('stopRecitation');
        this.nextVerseBtn = document.getElementById('nextVerse');
        this.statusEl = document.getElementById('recitationStatus');
        this.progressEl = document.getElementById('recitationProgress');
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
    }

    togglePageInputs() {
        if (this.modeSelect.value === 'range') {
            this.endPageInputContainer.classList.remove('d-none');
        } else {
            this.endPageInputContainer.classList.add('d-none');
        }
    }

    async startRecitation() {
        const mode = this.modeSelect.value;
        const startPage = parseInt(this.startPageInput.value);
        const endPage = mode === 'range' ? parseInt(this.endPageInput.value) : startPage;
        this.similarityThreshold = parseInt(this.similarityThresholdInput.value);
        this.startBtn.disabled = true;
        this.updateStatus('جارى البدء...');

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
            const response = await fetch('/api/student/live-recitation/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.apiToken,
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify({ mode: mode, start_page: startPage, end_page: endPage })
            });
            const data = await response.json();

            if (!data.success) {
                this.showError(data.error || data.errors || 'فشل في بدء جلسة التسميع');
                this.startBtn.disabled = false;
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
                websocketToken: data.data.websocket_token
            };
                id: data.data.session_id,
                audioUrls: data.data.audio_urls,
                websocketToken: data.data.websocket_token
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
            this.connectWebSocket();
        } catch (error) {
            console.error('Error starting recitation:', error);
            this.showError('حدث خطأ أثناء بدء التسميع: ' + error.message);
            this.startBtn.disabled = false;
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
            this.updateStatus('متصل - جارى الاستماع...');
            this.isRecording = true;
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
        };
    }

    async startRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.mediaRecorder = new MediaRecorder(stream);
            this.audioChunks = [];
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
        if (!this.audioContext || !this.isRecording) return;
        // Reuse existing analyser if already created
        if (!this.analyser) {
            this.analyser = this.audioContext.createAnalyser();
            const source = this.audioContext.createMediaStreamSource(this.mediaRecorder.stream);
            source.connect(this.analyser);
            this.analyser.fftSize = 256;
        }
        const dataArray = new Uint8Array(this.analyser.frequencyBinCount);
        const detectSilence = () => {
            if (!this.isRecording) return;
            this.analyser.getByteFrequencyData(dataArray);
            const average = dataArray.reduce((a, b) => a + b) / dataArray.length;
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

            // Real-time verse detection - match transcription to verses immediately
            if (message.message_type === 'FinalTranscript' && transcript.trim().length > 0) {
                this.processRecitation(transcript);
            }
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

            const header = document.createElement('div');
            header.className = 'd-flex justify-content-between align-items-center mb-2';

            const badge = document.createElement('span');
            badge.className = 'badge bg-secondary';
            badge.textContent = verse.surah + ':' + verse.verse_number;

            const counter = document.createElement('span');
            counter.className = 'text-muted';
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
        // Remove Arabic diacritics (tashkeel) - Unicode range ً-ٟ and ٰ
        text = text.replace(/[ً-ٰٟ]/g, '');
        // Normalize alef variations: أ إ آ ا -> ا
        text = text.replace(/[آأإا]/g, 'ا');
        // Normalize ya-al-maqsura: ى -> ي
        text = text.replace(/ى/g, 'ي');
        // Remove zero-width non-joiner (U+200C) and zero-width joiner (U+200D)
        text = text.replace(/[‌‍]/g, '');
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
                    'X-CSRF-TOKEN': this.getCsrfToken()
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
                    'X-CSRF-TOKEN': this.getCsrfToken()
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
        if (text && text.trim().length > 0) {
            this.transcriptEl.innerHTML = '<span class="text-success">' + text + '</span>';
        } else {
            this.transcriptEl.innerHTML = '<span class="text-muted">جارى الاستماع...</span>';
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
    }

    updateStatus(text) {
        this.statusEl.textContent = text;
    }

    showError(message) {
        alert(message);
    }


    cleanupRecording() {
        this.stopSilenceDetection();

        if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
            this.mediaRecorder.stop();
        }

        // Stop all stream tracks to release microphone
        if (this.mediaRecorder && this.mediaRecorder.stream) {
            this.mediaRecorder.stream.getTracks().forEach(track => track.stop());
        }

        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.close();
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
                    'X-CSRF-TOKEN': this.getCsrfToken()
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
        this.stopBtn.classList.add('d-none');
        this.nextVerseBtn.classList.add('d-none');
        this.updateStatus('في انتظار البدء');
        this.currentVerseEl.innerHTML = '<div class="text-muted p-5"><i class="fas fa-microphone-alt fa-4x mb-3 opacity-25"></i><h5>اضغط "ابدأ التسميع" للبدء</h5><p class="text-muted">سيتم الاستماع إليك ومقارنة نطقك بالنص القرآني</p></div>';
        this.transcriptEl.innerHTML = '<span class="text-muted">جارٍ الاستماع...</span>';
        this.sessionId = null;
        this.verses = [];
        this.currentVerseIndex = 0;
        this.totalErrors = 0;
        this.verseCardsCache = null;
        this.isEvaluating = false;
        this.isEnding = false;
        this.lastProcessedVerseIndex = -1;
        this.errorCountForCurrentVerse = 0;
    }

    showReport(report) {
        const accuracy = report.accuracy || 0;
        const totalVerses = report.total_verses || 0;
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
