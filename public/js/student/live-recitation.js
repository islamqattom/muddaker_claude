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
        this.autoPlayAudioCheckbox = document.getElementById('autoPlayAudio');
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

        // Get API token first - halt if failed
        try {
            const tokenResponse = await fetch('/student/live-recitation/token', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'include',
            });
            const tokenData = await tokenResponse.json();
            if (!tokenData.success || !tokenData.token) {
                this.showError('فشل في الحصول على رمز الدخول');
                this.startBtn.disabled = false;
                return;
            }
            this.apiToken = tokenData.token;
        } catch (error) {
            console.error('Failed to get API token:', error);
            this.showError('فشل في الاتصال بالخادم');
            this.startBtn.disabled = false;
            return;
        }

        try {
            const response = await fetch('/api/student/live-recitation/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + this.apiToken,
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                credentials: 'include',
                body: JSON.stringify({ mode: mode, start_page: startPage, end_page: endPage })
            });
            const data = await response.json();
            if (!data.success) {
                this.showError(data.error || 'فشل في بدء جلسة التسميع');
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
            this.currentVerseIndex = 0;
            this.totalErrors = 0;
            this.transcriptText = '';
            this.updateProgress();
            this.showVerse(0);
            this.stopBtn.classList.remove('d-none');
            this.nextVerseBtn.classList.remove('d-none');
            this.connectWebSocket();
        } catch (error) {
            console.error('Error starting recitation:', error);
            this.showError('حدث خطأ أثناء بدء التسميع');
            this.startBtn.disabled = false;
        }
    }

    getCsrfToken() {
        const meta = document.querySelector('meta[name=csrf-token]');
        return meta ? meta.content : '';
    }

    connectWebSocket() {
        // API returns websocket_url (snake_case), access it correctly
        const websocketUrl = this.currentSession.websocket_url || this.currentSession.websocketUrl;
        this.ws = new WebSocket(websocketUrl);
        this.ws.onopen = () => {
            console.log('WebSocket connected');
            this.updateStatus('متصل - جارى الاستماع...');
            this.isRecording = true;
            this.startRecording();
        };
        this.ws.onmessage = (event) => {
            const message = JSON.parse(event.data);
            this.handleWebSocketMessage(message);
        };
        this.ws.onerror = (error) => {
            console.error('WebSocket error:', error);
            this.showError('خطأ في الاتصال');
        };
        this.ws.onclose = () => {
            console.log('WebSocket closed');
            this.isRecording = false;
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
        const analyser = this.audioContext.createAnalyser();
        const source = this.audioContext.createMediaStreamSource(this.mediaRecorder.stream);
        source.connect(analyser);
        analyser.fftSize = 256;
        const dataArray = new Uint8Array(analyser.frequencyBinCount);
        const detectSilence = () => {
            if (!this.isRecording) return;
            analyser.getByteFrequencyData(dataArray);
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
            requestAnimationFrame(detectSilence);
        };
        detectSilence();
    }

    onSilenceDetected() {
        if (this.isEvaluating || this.currentVerseIndex >= this.verses.length) return;
        this.isEvaluating = true;
        this.mediaRecorder.stop();
        this.evaluateCurrentVerse();
    }

    async sendAudioChunk(blob) {
        if (!this.ws || this.ws.readyState !== WebSocket.OPEN) return;
        const reader = new FileReader();
        reader.onloadend = () => {
            const base64 = reader.result.split(',')[1];
            this.ws.send(JSON.stringify({ audio_data: base64 }));
        };
        reader.readAsDataURL(blob);
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
            if (message.message_type === 'FinalTranscript' && transcript.trim().length > 0) {
                clearTimeout(this.silenceTimeout);
                this.silenceTimeout = null;
            }
        }
    }

    async evaluateCurrentVerse() {
        const verse = this.verses[this.currentVerseIndex];
        const expectedText = verse.text;
        const similarity = this.calculateSimilarity(this.transcriptText, expectedText);
        if (similarity < this.similarityThreshold) {
            await this.recordError(verse, similarity);
            this.showErrorState(verse);
            this.faultAudio.play().catch(() => {});
        }
        await this.updateProgressOnServer(similarity);
        this.isEvaluating = false;
        this.transcriptText = '';
        setTimeout(() => {
            if (this.isRecording && this.currentVerseIndex < this.verses.length) {
                this.mediaRecorder.start(1000);
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
        // Remove zero-width joiners and similar
        text = text.replace(/[​-‍‎‏]/g, '');
        // Normalize whitespace
        text = text.replace(/\s+/g, ' ');
        return text.trim();
    }

    async recordError(verse, similarity) {
        try {
            await fetch('/api/student/live-recitation/error', {
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
            this.totalErrors++;
            this.errorCountEl.textContent = this.totalErrors;
        } catch (error) {
            console.error('Error recording error:', error);
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
        const verse = this.verses[index];
        const verseHtml = '<div class="verse-card"><div class="d-flex justify-content-between align-items-center mb-2"><span class="badge bg-secondary">' + verse.surah + ':' + verse.verse_number + '</span><span class="text-muted">' + (index + 1) + ' / ' + this.verses.length + '</span></div><p class="verse-text mb-0" dir="rtl">' + verse.text + '</p></div>';
        this.currentVerseEl.innerHTML = verseHtml;
        if (this.autoPlayAudioCheckbox.checked && this.currentSession.audioUrls[index]) {
            const audio = new Audio(this.currentSession.audioUrls[index]);
            audio.play().catch(() => {});
        }
    }

    showErrorState(verse) {
        const verseCard = this.currentVerseEl.querySelector('.verse-card');
        if (verseCard) {
            verseCard.classList.add('error-verse');
            setTimeout(() => {
                verseCard.classList.remove('error-verse');
            }, 2000);
        }
    }

    updateTranscript(text) {
        if (text && text.trim().length > 0) {
            this.transcriptEl.innerHTML = '<span class="text-success">' + text + '</span>';
        } else {
            this.transcriptEl.innerHTML = '<span class="text-muted">جارى الاستماع...</span>';
        }
    }

    nextVerse() {
        this.currentVerseIndex++;
        this.updateProgress();
        this.showVerse(this.currentVerseIndex);
        this.transcriptText = '';
        if (this.currentVerseIndex >= this.verses.length) {
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


    async stopRecitation() {
        if (!this.sessionId) return;
        this.isRecording = false;
        if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
            this.mediaRecorder.stop();
        }
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.close();
        }
        try {
            const response = await fetch('/api/student/live-recitation/session/' + this.sessionId + '/end', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + this.apiToken,
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                credentials: 'include',
            });
            const data = await response.json();
            if (data.success) {
                this.showReport(data.data);
            }
        } catch (error) {
            console.error('Error ending session:', error);
        }
        this.resetUI();
    }

    finishRecitation() {
        this.isRecording = false;
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.close();
        }
        this.stopRecitation();
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
