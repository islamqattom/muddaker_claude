@extends('layouts.app')

@section('title', 'التفسير والترجمة - ترتيل')
@section('page-title', 'التفسير والترجمة')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/student/tarteel-center.css') }}?v={{ time() }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="api-token" content="{{ $apiToken ?? '' }}">
<meta name="tafseer-url" content="{{ route('api.student.tarteel.tafseer') }}">

<div class="recitation-wrapper">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-book me-2"></i>اختيار الآية</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">السورة</label>
                        <select class="form-select" id="surahSelect">
                            <option value="">اختر السورة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الآية</label>
                        <input type="number" class="form-control" id="verseInput" value="1" min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">التفسير</label>
                        <select class="form-select" id="tafseerType">
                            <option value="ar.muyassar">التفسير الميسر</option>
                            <option value="ar.jalalayn">تفسير الجلالين</option>
                            <option value="ar.qurtubi">تفسير القرطبي</option>
                            <option value="ar.sadi">تفسير السعدي</option>
                            <option value="ar.waseet">التفسير الوسيط</option>
                            <option value="ar.baghawi">تفسير البغوي</option>
                        </select>
                    </div>
                    <button class="btn btn-outline-primary w-100" id="loadTafseer">
                        <i class="fas fa-search me-2"></i>عرض التفسير
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-language me-2"></i>الترجمة</h5>
                </div>
                <div class="card-body">
                    <div class="tafseer-translation" id="translationContent">
                        <p class="text-muted">اختر آية لعرض ترجمتها</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-book-quran me-2"></i>الآية</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary" id="playAudio" title="تشغيل الصوت">
                            <i class="fas fa-volume-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="copyVerse" title="نسخ الآية">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body text-center">
                    <div id="verseDisplay" class="verse-display">
                        <div class="text-muted">
                            <i class="fas fa-book fa-3x mb-3 opacity-25"></i>
                            <p>اختر آية لعرضها هنا</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-book-open me-2"></i>التفسير</h5>
                </div>
                <div class="card-body">
                    <div class="tafseer-content" id="tafseerContent">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-book fa-3x mb-3 opacity-25"></i>
                            <p>اختر آية لعرض تفسيرها</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const surahSelect = document.getElementById('surahSelect');
    const verseInput = document.getElementById('verseInput');
    const tafseerType = document.getElementById('tafseerType');
    const loadTafseerBtn = document.getElementById('loadTafseer');
    const verseDisplay = document.getElementById('verseDisplay');
    const tafseerContent = document.getElementById('tafseerContent');
    const translationContent = document.getElementById('translationContent');
    const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';
    const tafseerUrl = document.querySelector('meta[name=tafseer-url]')?.content || '/api/student/tarteel/tafseer';

    const SURAH_INFO = @json(\App\Services\QuranApiService::SURAH_INFO);

    function populateSurahs() {
        Object.keys(SURAH_INFO).forEach(num => {
            const opt = document.createElement('option');
            opt.value = num;
            opt.textContent = `${num} - ${SURAH_INFO[num].name}`;
            surahSelect.appendChild(opt);
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('surah')) {
        surahSelect.value = urlParams.get('surah');
        verseInput.value = urlParams.get('verse') || 1;
    }

    populateSurahs();

    loadTafseerBtn.addEventListener('click', loadTafseer);

    async function loadTafseer() {
        const surah = surahSelect.value;
        const verse = verseInput.value;
        const type = tafseerType.value;

        if (!surah) {
            alert('الرجاء اختيار سورة');
            return;
        }

        verseDisplay.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
        tafseerContent.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

        try {
            const response = await fetch(`${tafseerUrl}?surah=${surah}&verse=${verse}&interpretation=${type}`, {
                headers: {
                    'Authorization': 'Bearer ' + (document.querySelector('meta[name=api-token]')?.content || ''),
                }
            });

            const data = await response.json();

            if (data.success) {
                verseDisplay.innerHTML = `
                    <div class="tafseer-verse-number mb-2">${SURAH_INFO[surah].name} - الآية ${verse}</div>
                    <p class="tafseer-verse-text" dir="rtl">${data.data.verse.text}</p>
                `;
                tafseerContent.innerHTML = `
                    <div class="tafseer-source mb-3">${data.data.tafseer.source}</div>
                    <p class="tafseer-text" dir="rtl">${data.data.tafseer.text.join('')}</p>
                `;

                const translations = data.data.translation;
                let transHtml = '<dl class="row mb-0">';
                if (translations.en) transHtml += `<dt>الإنجليزية:</dt><dd>${translations.en}</dd>`;
                if (translations.ur) transHtml += `<dt>الأردية:</dt><dd>${translations.ur}</dd>`;
                if (translations.id) transHtml += `<dt>الإندونيسية:</dt><dd>${translations.id}</dd>`;
                transHtml += '</dl>';
                translationContent.innerHTML = transHtml;
            } else {
                verseDisplay.innerHTML = '<div class="text-danger">فشل في تحميل الآية</div>';
                tafseerContent.innerHTML = '<div class="text-danger">فشل في تحميل التفسير</div>';
            }
        } catch (error) {
            console.error('Tafseer load error:', error);
            verseDisplay.innerHTML = '<div class="text-danger">حدث خطأ</div>';
            tafseerContent.innerHTML = '<div class="text-danger">حدث خطأ</div>';
        }
    }

    document.getElementById('playAudio')?.addEventListener('click', () => {
        const surah = surahSelect.value;
        const verse = verseInput.value;
        if (surah) {
            const audio = new Audio(`https://cdn.islamic.network/quran/audio/128/ar.alafasy/${surah}:${verse}.mp3`);
            audio.play();
        }
    });

    document.getElementById('copyVerse')?.addEventListener('click', () => {
        const verseText = verseDisplay.textContent;
        navigator.clipboard.writeText(verseText);
    });
})();
</script>
@endpush
@endsection