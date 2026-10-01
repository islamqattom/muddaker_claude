@extends('layouts.app')

@section('title', 'التفسير - ترتيل')
@section('page-title', 'التفسير')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')

<x-page-hero icon="fas fa-book-open">
    <h1 class="page-banner-title">التفسير</h1>
</x-page-hero>
<link rel="stylesheet" href="{{ asset('css/student/tarteel-center.css') }}?v={{ time() }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="api-token" content="{{ $apiToken ?? '' }}">
<meta name="tafseer-url" content="{{ route('api.student.tarteel.tafseer') }}">

<style>
.tafseer-page {
    max-width: 1200px;
    margin: 0 auto;
}
.tafseer-controls {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
    border-radius: 13px;
    padding: 16px;
    margin-bottom: 16px;
    color: white;
}
.tafseer-controls .form-label {
    color: rgba(255,255,255,0.8);
    font-size: 0.85rem;
    margin-bottom: 6px;
}
.tafseer-controls .form-control,
.tafseer-controls .form-select {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    border-radius: 10px;
    padding: 10px 14px;
}
.tafseer-controls .form-control:focus,
.tafseer-controls .form-select:focus {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.4);
    box-shadow: 0 0 0 3px rgba(255,255,255,0.1);
    color: white;
}
.tafseer-controls .form-select option {
    background: #1e3a5f;
    color: white;
}
.tafseer-controls .btn-load {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
    border-radius: 9px;
    padding: 8px 16px;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
}
.tafseer-controls .btn-load:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
}
.tafseer-verse-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 16px;
}
.tafseer-verse-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.tafseer-verse-header h4 {
    margin: 0;
    font-size: 1.1rem;
}
.tafseer-verse-actions {
    display: flex;
    gap: 8px;
}
.tafseer-verse-actions button {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    transition: all 0.2s;
}
.tafseer-verse-actions button:hover {
    background: rgba(255,255,255,0.3);
}
.tafseer-verse-body {
    padding: 24px;
    text-align: center;
    background: linear-gradient(180deg, #faf9ff 0%, white 100%);
}
.tafseer-verse-text {
    font-size: 1.8rem;
    line-height: 2.2;
    color: #1e3a5f;
    font-family: 'Amiri', serif;
    margin: 0;
}
.tafseer-content-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}
.tafseer-content-header {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.tafseer-content-header h4 {
    margin: 0;
    font-size: 1rem;
}
.tafseer-badge {
    background: rgba(255,255,255,0.2);
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 0.8rem;
}
.tafseer-content-body {
    padding: 16px;
}
.tafseer-text {
    font-size: 1.1rem;
    line-height: 2;
    color: #333;
    text-align: justify;
}
.tafseer-empty {
    text-align: center;
    padding: 40px 16px;
    color: #999;
}
.tafseer-empty i {
    font-size: 4rem;
    margin-bottom: 16px;
    opacity: 0.3;
}
.tafseer-loading {
    text-align: center;
    padding: 40px 16px;
}
.loading-spinner {
    width: 50px;
    height: 50px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<div class="tafseer-page">
    <div class="tafseer-controls">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">السورة</label>
                <select class="form-select" id="surahSelect">
                    <option value="">اختر السورة</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">الآية</label>
                <input type="number" class="form-control" id="verseInput" value="1" min="1">
            </div>
            <div class="col-md-3">
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
            <div class="col-md-3">
                <button class="btn btn-load w-100" id="loadTafseer">
                    <i class="fas fa-search me-2"></i>عرض التفسير
                </button>
            </div>
        </div>
    </div>

    <div id="tafseerResults">
        <div class="tafseer-empty">
            <i class="fas fa-book-open"></i>
            <p class="h5">اختر سورة وآية لعرض التفسير</p>
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
    const tafseerResults = document.getElementById('tafseerResults');
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

        tafseerResults.innerHTML = `
            <div class="tafseer-loading">
                <div class="loading-spinner"></div>
                <p>جارٍ تحميل التفسير...</p>
            </div>
        `;

        try {
            const response = await fetch(`${tafseerUrl}?surah=${surah}&verse=${verse}&interpretation=${type}`, {
                headers: {
                    'Authorization': 'Bearer ' + (document.querySelector('meta[name=api-token]')?.content || ''),
                }
            });

            const data = await response.json();

            if (data.success) {
                const verseText = data.data.verse.text;
                const tafseerText = Array.isArray(data.data.tafseer.text) ? data.data.tafseer.text.join('') : data.data.tafseer.text;
                const sourceName = data.data.tafseer.source;

                tafseerResults.innerHTML = `
                    <div class="tafseer-verse-card">
                        <div class="tafseer-verse-header">
                            <h4><i class="fas fa-book-quran me-2"></i>${SURAH_INFO[surah].name} - الآية ${verse}</h4>
                            <div class="tafseer-verse-actions">
                                <button onclick="playAudio('${surah}', '${verse}')" title="تشغيل الصوت">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                                <button onclick="copyVerse()" title="نسخ الآية">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="tafseer-verse-body">
                            <p class="tafseer-verse-text" dir="rtl">${verseText}</p>
                        </div>
                    </div>
                    <div class="tafseer-content-card">
                        <div class="tafseer-content-header">
                            <h4><i class="fas fa-book-open me-2"></i>التفسير</h4>
                            <span class="tafseer-badge">${sourceName}</span>
                        </div>
                        <div class="tafseer-content-body">
                            <p class="tafseer-text" dir="rtl">${tafseerText}</p>
                        </div>
                    </div>
                `;
            } else {
                tafseerResults.innerHTML = `
                    <div class="tafseer-empty">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p class="h5">فشل في تحميل التفسير</p>
                        <p>${data.error || 'حدث خطأ غير معروف'}</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Tafseer load error:', error);
            tafseerResults.innerHTML = `
                <div class="tafseer-empty">
                    <i class="fas fa-wifi"></i>
                    <p class="h5">حدث خطأ في الاتصال</p>
                    <p>تأكد من اتصال الإنترنت وحاول مرة أخرى</p>
                </div>
            `;
        }
    }

    window.playAudio = function(surah, verse) {
        const audio = new Audio(`https://cdn.islamic.network/quran/audio/128/ar.alafasy/${surah}:${verse}.mp3`);
        audio.play();
    };

    window.copyVerse = function() {
        const verseText = document.querySelector('.tafseer-verse-text')?.textContent || '';
        navigator.clipboard.writeText(verseText).then(() => {
            alert('تم نسخ الآية');
        });
    };
})();
</script>
@endpush
@endsection