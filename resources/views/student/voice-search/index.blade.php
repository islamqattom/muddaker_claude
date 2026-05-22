@extends('layouts.app')

@section('title', 'البحث الصوتي - ترتيل')
@section('page-title', 'البحث الصوتي')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/student/tarteel-center.css') }}?v={{ time() }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<meta name="voice-search-url" content="{{ config('app.url') }}/api/student/tarteel/voice-search">

<div class="recitation-wrapper">
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-microphone-alt me-2"></i>البحث في القرآن بالصوت</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">استخدم صوتك للبحث عن آيات في القرآن الكريم</p>

            <div class="voice-search-container text-center">
                <button class="voice-search-btn" id="voiceSearchBtn">
                    <i class="fas fa-microphone fa-3x"></i>
                </button>
                <div class="voice-search-status mt-3" id="voiceSearchStatus">
                    <span class="text-muted">اضغط للبحث الصوتي</span>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-8 mx-auto">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchQuery" placeholder="أو اكتب كلمات البحث هنا...">
                        <button class="btn btn-outline-primary" id="textSearchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-search me-2"></i>نتائج البحث</h5>
        </div>
        <div class="card-body">
            <div id="searchResults" class="search-results">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                    <p>ستظهر نتائج البحث هنا</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const voiceSearchBtn = document.getElementById('voiceSearchBtn');
    const voiceSearchStatus = document.getElementById('voiceSearchStatus');
    const searchQuery = document.getElementById('searchQuery');
    const textSearchBtn = document.getElementById('textSearchBtn');
    const searchResults = document.getElementById('searchResults');
    const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';
    const voiceSearchUrl = document.querySelector('meta[name=voice-search-url]')?.content || '/api/student/tarteel/voice-search';

    let recognition = null;
    let isSearching = false;

    if ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
        recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'ar-SA';
    }

    voiceSearchBtn.addEventListener('click', () => {
        if (!recognition) {
            alert('المتصفح لا يدعم التعرف على الصوت');
            return;
        }

        if (isSearching) {
            recognition.stop();
            isSearching = false;
            return;
        }

        isSearching = true;
        voiceSearchBtn.classList.add('recording');
        voiceSearchStatus.innerHTML = '<span class="text-warning">جارٍ الاستماع...</span>';

        recognition.start();
    });

    if (recognition) {
        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            searchQuery.value = transcript;
            performSearch(transcript);
        };

        recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            voiceSearchStatus.innerHTML = '<span class="text-danger">خطأ في التعرف على الصوت</span>';
            voiceSearchBtn.classList.remove('recording');
            isSearching = false;
        };

        recognition.onend = () => {
            voiceSearchBtn.classList.remove('recording');
            isSearching = false;
        };
    }

    textSearchBtn.addEventListener('click', () => {
        const query = searchQuery.value.trim();
        if (query) {
            performSearch(query);
        }
    });

    searchQuery.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const query = searchQuery.value.trim();
            if (query) {
                performSearch(query);
            }
        }
    });

    async function performSearch(query) {
        if (!query) return;

        voiceSearchStatus.innerHTML = '<span class="text-info">جارٍ البحث...</span>';
        searchResults.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

        try {
            const response = await fetch(voiceSearchUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Authorization': 'Bearer ' + (document.querySelector('meta[name=api-token]')?.content || ''),
                },
                body: JSON.stringify({ query })
            });

            const data = await response.json();

            if (data.success && data.data.results.length > 0) {
                renderResults(data.data.results);
                voiceSearchStatus.innerHTML = '<span class="text-success">تم العثور على ' + data.data.results.length + ' نتيجة</span>';
            } else {
                searchResults.innerHTML = '<div class="text-center text-muted py-5"><p>لم يتم العثور على نتائج</p></div>';
                voiceSearchStatus.innerHTML = '<span class="text-muted">اضغط للبحث الصوتي</span>';
            }
        } catch (error) {
            console.error('Search error:', error);
            searchResults.innerHTML = '<div class="text-center text-danger py-5"><p>حدث خطأ في البحث</p></div>';
            voiceSearchStatus.innerHTML = '<span class="text-muted">اضغط للبحث الصوتي</span>';
        }
    }

    function renderResults(results) {
        let html = '';
        results.forEach((result, index) => {
            html += `
                <div class="search-result-item mb-3 p-3 border rounded" data-surah="${result.surah}" data-verse="${result.verse}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary">سورة ${result.surah_name}</span>
                        <span class="text-muted">الآية ${result.verse}</span>
                    </div>
                    <p class="verse-text mb-2" dir="rtl">${result.text}</p>
                    <small class="text-muted">${result.surah_name} - آية ${result.verse}</small>
                </div>
            `;
        });

        searchResults.innerHTML = html;

        document.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', () => {
                const surah = item.dataset.surah;
                const verse = item.dataset.verse;
                window.location.href = `/student/tafseer?surah=${surah}&verse=${verse}`;
            });
        });
    }
})();
</script>
@endpush
@endsection