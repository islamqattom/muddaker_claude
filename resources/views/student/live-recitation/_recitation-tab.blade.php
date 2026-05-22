<div class="row g-4">
    <div class="col-lg-4">
        <div class="card settings-card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-sliders-h me-2"></i>إعدادات التسميع</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">نوع التسميع</label>
                    <select class="form-select" id="modeSelect" aria-describedby="modeHelp">
                        <option value="page">صفحة واحدة</option>
                        <option value="range">نطاق صفحات</option>
                        <option value="surah">سورة</option>
                    </select>
                    <small id="modeHelp" class="text-muted">اختر نوع التسميع</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">القارئ</label>
                    <select class="form-select" id="reciterSelect" aria-describedby="reciterHelp">
                        <option value="">مشاري العفاسي (افتراضي)</option>
                    </select>
                    <small id="reciterHelp" class="text-muted">اختر القارئ المفضل</small>
                </div>

                <div class="mb-3" id="pageInputs">
                    <label class="form-label" for="startPage">من صفحة</label>
                    <input type="number" class="form-control" id="startPage" value="1" min="1" max="604" aria-describedby="pageHelp">
                    <small id="pageHelp" class="text-muted">رقم الصفحة (1-604)</small>
                </div>

                <div class="mb-3 d-none" id="endPageInput">
                    <label class="form-label" for="endPage">إلى صفحة</label>
                    <input type="number" class="form-control" id="endPage" value="1" min="1" max="604">
                </div>

                <div class="mb-3 d-none" id="surahInputs">
                    <label class="form-label" for="surahSelect">السورة</label>
                    <select class="form-select" id="surahSelect">
                        <option value="">اختر السورة</option>
                    </select>
                </div>

                <div class="mb-3 d-none" id="verseRangeInputs">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="verseStart">من آية</label>
                            <input type="number" class="form-control" id="verseStart" value="1" min="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="verseEnd">إلى آية</label>
                            <input type="number" class="form-control" id="verseEnd" value="1" min="1">
                            <small id="verseEndHint" class="text-muted"></small>
                        </div>
                    </div>
                </div>

                <button class="btn btn-outline-primary w-100" id="previewVerses">
                    <i class="fas fa-eye me-2"></i>عرض الآيات
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card verses-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-book-quran me-2"></i>الآيات</h5>
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted hint-text">اضغط على آية للانتقال إليها</small>
                    <div class="btn-group btn-group-sm" role="group" aria-label="عرض الآيات" id="viewModeToggle">
                        <button type="button" class="btn btn-outline-secondary active" id="viewModeCards" title="عرض البطاقات" onclick="window.toggleViewMode('cards')">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="viewModeMushaf" title="عرض مصحف" onclick="window.toggleViewMode('mushaf')">
                            <i class="fas fa-book-open"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body text-center">
                <div id="currentVerse">
                    <div class="idle-state">
                        <i class="fas fa-book-quran fa-4x mb-3 opacity-25"></i>
                        <h5>اختر الإعدادات ثم اضغط "عرض الآيات"</h5>
                        <p class="text-muted">ستظهر الآيات هنا</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>