@extends('layouts.app')

@section('title', 'تمرين النطق')
@section('page-title', 'تمرين النطق والتجويد')
@section('role-label', 'طالب')

@section('sidebar-menu')
    <span class="nav-section-title">الرئيسية</span>
    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i> لوحتي الشخصية
    </a>
    <span class="nav-section-title mt-2">القرآن الكريم</span>
    <a href="{{ route('student.quran') }}" class="nav-link {{ request()->routeIs('student.quran*') ? 'active' : '' }}">
        <i class="fas fa-book-quran"></i> قراءة القرآن
    </a>
    <span class="nav-section-title mt-2">المتابعة</span>
    <a href="{{ route('student.sessions.index') }}" class="nav-link {{ request()->routeIs('student.sessions.*') ? 'active' : '' }}">
        <i class="fas fa-microphone"></i> جلسات التسميع
    </a>
    <span class="nav-section-title mt-2">التقدم</span>
    <a href="{{ route('student.progress') }}" class="nav-link {{ request()->routeIs('student.progress*') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> تقدمي
    </a>
    <a href="{{ route('student.pronunciation.index') }}" class="nav-link {{ request()->routeIs('student.pronunciation*') ? 'active' : '' }}">
        <i class="fas fa-microphone-alt"></i> النطق
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-microphone-alt me-2"></i>تمرين النطق والتجويد</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            اختر سورة وآية ثم سجّل تلاوتك للحصول على تقييم التجويد
        </div>

        <!-- Verse Selection -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">السورة</label>
                <input type="number" id="surah" class="form-control" min="1" max="114" value="1">
            </div>
            <div class="col-md-4">
                <label class="form-label">آية البداية</label>
                <input type="number" id="verse_start" class="form-control" min="1" value="1">
            </div>
            <div class="col-md-4">
                <label class="form-label">آية النهاية (اختياري)</label>
                <input type="number" id="verse_end" class="form-control" min="1" placeholder="نفس الآية">
            </div>
        </div>

        <div class="d-grid gap-2 d-md-block mb-4">
            <button type="button" id="loadVerse" class="btn btn-primary">
                <i class="fas fa-search me-1"></i>تحميل الآية
            </button>
        </div>

        <!-- Verse Display -->
        <div id="verseContainer" class="d-none mb-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-2">نص الآية:</h6>
                    <div id="verseText" class="text-center" style="font-size: 1.5rem; line-height: 2.2;"></div>
                    <div id="verseMeta" class="text-center text-muted small mt-2"></div>
                </div>
            </div>
        </div>

        <!-- Recording Section -->
        <div id="recordingSection" class="d-none">
            <hr>
            <h6 class="mb-3"><i class="fas fa-record-vinyl me-2"></i>تسجيل التلاوة</h6>

            <form action="{{ route('student.pronunciation.store') }}" method="POST" enctype="multipart/form-data" id="recordingForm">
                @csrf
                <input type="hidden" name="surah" id="formSurah">
                <input type="hidden" name="verse_start" id="formVerseStart">
                <input type="hidden" name="verse_end" id="formVerseEnd">
                <input type="hidden" name="audio" id="audioData">

                <div class="text-center mb-3">
                    <button type="button" id="recordBtn" class="btn btn-danger btn-lg rounded-circle" style="width: 80px; height: 80px;">
                        <i class="fas fa-microphone fa-2x"></i>
                    </button>
                    <p class="small text-muted mt-2">اضغط للتسجيل</p>
                </div>

                <div id="recordingStatus" class="text-center mb-3 d-none">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="mt-2">جارٍ التسجيل...</p>
                </div>

                <div id="playbackSection" class="d-none text-center mb-3">
                    <audio id="audioPlayback" controls class="w-100"></audio>
                    <p class="small text-muted">استمع لتسجيلك قبل الإرسال</p>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" id="submitBtn" class="btn btn-success d-none">
                        <i class="fas fa-paper-plane me-1"></i>إرسال للتقييم
                    </button>
                    <button type="button" id="retryBtn" class="btn btn-outline-secondary d-none">
                        <i class="fas fa-redo me-1"></i>تسجيل مرة أخرى
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Info Card -->
<div class="card mt-4 bg-light">
    <div class="card-body">
        <h6><i class="fas fa-lightbulb me-2"></i>نصائح لتحسين التلاوة</h6>
        <ul class="mb-0 small">
            <li>سجّل في مكان هادئ بعيداً عن الضوضاء</li>
            <li>انطق الحروف من مخارجها الصحيحة</li>
            <li>انتبه لقواعد المد والمدد (ا، و، ي)</li>
            <li>أسمع الغنة عند النون الساكنة والتنوين</li>
        </ul>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
let mediaRecorder;
let audioChunks = [];

document.getElementById('loadVerse').addEventListener('click', function() {
    const surah = document.getElementById('surah').value;
    const verseStart = document.getElementById('verse_start').value;
    const verseEnd = document.getElementById('verse_end').value || verseStart;

    fetch(`/student/pronunciation/verse?surah=${surah}&verse_start=${verseStart}&verse_end=${verseEnd}`)
        .then(r => {
            if (!r.ok) throw new Error('فشل في تحميل الآية');
            return r.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('verseContainer').classList.remove('d-none');
                const verseText = data.data.text || (data.data.verses ? data.data.verses.map(v => v.text).join(' ') : '');
                document.getElementById('verseText').textContent = verseText;
                const surahName = data.data.surah_name || surah;
                const metaText = verseEnd !== verseStart
                    ? `سورة ${surahName} - آية ${verseStart} إلى ${verseEnd}`
                    : `سورة ${surahName} - آية ${verseStart}`;
                document.getElementById('verseMeta').textContent = metaText;

                document.getElementById('formSurah').value = surah;
                document.getElementById('formVerseStart').value = verseStart;
                document.getElementById('formVerseEnd').value = verseEnd;

                document.getElementById('recordingSection').classList.remove('d-none');
            } else {
                alert(data.error || 'فشل في تحميل الآية');
            }
        })
        .catch(err => {
            console.error('Error loading verse:', err);
            alert('فشل في تحميل الآية');
        });
});

document.getElementById('recordBtn').addEventListener('click', async function() {
    if (!mediaRecorder || mediaRecorder.state === 'inactive') {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                const audioUrl = URL.createObjectURL(audioBlob);
                document.getElementById('audioPlayback').src = audioUrl;
                document.getElementById('playbackSection').classList.remove('d-none');
                document.getElementById('submitBtn').classList.remove('d-none');
                document.getElementById('retryBtn').classList.remove('d-none');

                const reader = new FileReader();
                reader.onloadend = () => {
                    document.getElementById('audioData').value = reader.result;
                };
                reader.readAsDataURL(audioBlob);
            };

            mediaRecorder.start();
            document.getElementById('recordingStatus').classList.remove('d-none');
            document.getElementById('recordBtn').innerHTML = '<i class="fas fa-stop fa-2x"></i>';
        } catch (err) {
            alert('يرجى السماح بالوصول إلى الميكروفون');
        }
    } else {
        mediaRecorder.stop();
        mediaRecorder.stream.getTracks().forEach(t => t.stop());
        document.getElementById('recordingStatus').classList.add('d-none');
        document.getElementById('recordBtn').innerHTML = '<i class="fas fa-microphone fa-2x"></i>';
    }
});

document.getElementById('retryBtn').addEventListener('click', function() {
    document.getElementById('playbackSection').classList.add('d-none');
    document.getElementById('submitBtn').classList.add('d-none');
    document.getElementById('retryBtn').classList.add('d-none');
    document.getElementById('audioData').value = '';
});
</script>
@endsection
