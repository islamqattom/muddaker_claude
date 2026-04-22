@extends('layouts.app')

@section('title', 'اختبار المواضع')
@section('page-title', 'اختبار المواضع')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
    .exam-card {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }

    .verse-display {
        background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
        border-radius: 15px;
        padding: 40px;
        color: white;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-family: 'Amiri', 'Scheherazade', serif;
        font-size: 28px;
        line-height: 2.2;
        direction: rtl;
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);
    }

    .verse-display .verse-text {
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }

    .verse-display .placeholder {
        opacity: 0.6;
        font-size: 18px;
        font-family: 'Tajawal', sans-serif;
    }

    .scope-selector {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .scope-option {
        cursor: pointer;
        padding: 12px 20px;
        border-radius: 10px;
        border: 2px solid transparent;
        transition: all 0.2s;
        background: white;
    }

    .scope-option:hover {
        border-color: #B4802C;
    }

    .scope-option.active {
        border-color: #182B48;
        background: #182B48;
        color: white;
    }

    .scope-option input {
        display: none;
    }

    .btn-generate {
        background: linear-gradient(135deg, #27ae60, #2ecc71);
        border: none;
        padding: 14px 40px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s;
    }

    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(39, 174, 96, 0.4);
    }

    .btn-next {
        background: linear-gradient(135deg, #182B48, #2A4570);
        border: none;
        padding: 12px 30px;
        font-size: 14px;
        border-radius: 8px;
    }

    .btn-copy {
        background: #6c757d;
        border: none;
        padding: 12px 30px;
        font-size: 14px;
        border-radius: 8px;
    }

    .verse-reference {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
        color: #6c757d;
    }

    @media (max-width: 768px) {
        .verse-display {
            padding: 25px;
            font-size: 22px;
        }

        .scope-option {
            padding: 10px 15px;
            font-size: 14px;
        }
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade:wght@400;700&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Selection Card --}}
        <div class="card exam-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>اختر نطاق السؤال</h5>
            </div>
            <div class="card-body">
                {{-- Scope Type Tabs --}}
                <div class="d-flex gap-2 mb-4 flex-wrap">
                    <label class="scope-option scope-tab active" data-scope="juz">
                        <input type="radio" name="scope_type" value="juz" checked>
                        <i class="fas fa-layer-group me-2"></i>بالجزء
                    </label>
                    <label class="scope-option scope-tab" data-scope="surah">
                        <input type="radio" name="scope_type" value="surah">
                        <i class="fas fa-book me-2"></i>بالسورة
                    </label>
                    <label class="scope-option scope-tab" data-scope="page">
                        <input type="radio" name="scope_type" value="page">
                        <i class="fas fa-file-alt me-2"></i>بالصفحة
                    </label>
                </div>

                {{-- Juz Range --}}
                <div id="juz-options" class="scope-inputs">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">من جزء</label>
                            <select name="start_juz" class="form-select">
                                @for($i = 1; $i <= 30; $i++)
                                    <option value="{{ $i }}">الجزء {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">إلى جزء</label>
                            <select name="end_juz" class="form-select">
                                @for($i = 1; $i <= 30; $i++)
                                    <option value="{{ $i }}" {{ $i == 30 ? 'selected' : '' }}>الجزء {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Surah Selection --}}
                <div id="surah-options" class="scope-inputs d-none">
                    <label class="form-label">اختر السورة</label>
                    <select name="surah_id" class="form-select">
                        <option value="">-- اختر السورة --</option>
                        @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}">
                                {{ $surah->surah_number }}. {{ $surah->name_ar }} ({{ $surah->total_ayahs }} آية)
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Page Range --}}
                <div id="page-options" class="scope-inputs d-none">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">من صفحة</label>
                            <input type="number" name="start_page" class="form-control" min="1" max="604" placeholder="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label">إلى صفحة</label>
                            <input type="number" name="end_page" class="form-control" min="1" max="604" placeholder="604">
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="button" id="generateBtn" class="btn btn-generate text-white">
                        <i class="fas fa-dice me-2"></i>توليد سؤال
                    </button>
                </div>
            </div>
        </div>

        {{-- Verse Display Card --}}
        <div class="card exam-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-book-quran me-2"></i>آية القرآن</h5>
                <div class="d-flex gap-2" id="actionButtons" style="display: none;">
                    <button type="button" class="btn btn-sm btn-next text-white" id="nextBtn">
                        <i class="fas fa-arrow-right me-1"></i>السؤال التالي
                    </button>
                    <button type="button" class="btn btn-sm btn-copy text-white" id="copyBtn">
                        <i class="fas fa-copy me-1"></i>نسخ المرجع
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="verse-display" id="verseDisplay">
                    <div class="placeholder">
                        <i class="fas fa-hand-pointer fa-2x mb-3 d-block"></i>
                        اضغط على "توليد سؤال" لعرض آية عشوائية
                    </div>
                </div>
                <div class="verse-reference" id="verseReference" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentScope = 'juz';
    let currentReference = '';

    // Scope tab switching
    $('.scope-tab').click(function() {
        const scope = $(this).data('scope');
        currentScope = scope;

        $('.scope-tab').removeClass('active');
        $(this).addClass('active');

        $('.scope-inputs').addClass('d-none');
        $(`#${scope}-options`).removeClass('d-none');
    });

    // Generate question
    $('#generateBtn').click(function() { generateQuestion(); });
    $('#nextBtn').click(function() { generateQuestion(); });
    $('#copyBtn').click(function() { copyToClipboard(currentReference); });

    function generateQuestion() {
        const $btn = $('#generateBtn');
        const originalText = $btn.html();

        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>جاري التحميل...').prop('disabled', true);

        $.ajax({
            url: '{{ route("supervisor.exam-assistant.generate") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                scope_type: currentScope,
                start_juz: $('select[name="start_juz"]').val(),
                end_juz: $('select[name="end_juz"]').val(),
                surah_id: $('select[name="surah_id"]').val(),
                start_page: $('input[name="start_page"]').val(),
                end_page: $('input[name="end_page"]').val(),
            },
            success: function(response) {
                if (response.success) {
                    $('#verseDisplay').html(`<div class="verse-text">${response.ayah.text}</div>`);
                    $('#verseReference').html(response.reference).show();
                    $('#actionButtons').show();
                    currentReference = response.reference;
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                let msg = 'حدث خطأ';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            toastr.success('تم نسخ المرجع: ' + text);
        }, function() {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            toastr.success('تم نسخ المرجع: ' + text);
        });
    }
});
</script>
@endpush
