@extends('layouts.app')

@section('title', 'إدارة الأهداف')
@section('page-title', 'إدارة الأهداف')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-bullseye me-2"></i>إدارة الأهداف</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('student.progress.goals.update') }}" method="POST" id="goalForm">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label fw-bold">نوع الهدف</label>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="form-check p-3 border rounded {{ $goal->period === 'daily' ? 'border-primary bg-light' : '' }}">
                            <input class="form-check-input" type="radio" name="period" value="daily"
                                   id="dailyGoal" {{ $goal->period === 'daily' ? 'checked' : '' }}>
                            <label class="form-check-label w-100" for="dailyGoal">
                                <strong>هدف يومي</strong>
                                <small class="d-block text-muted">حفظ عدد محدد من الصفحات كل يوم</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check p-3 border rounded {{ $goal->period === 'weekly' ? 'border-primary bg-light' : '' }}">
                            <input class="form-check-input" type="radio" name="period" value="weekly"
                                   id="weeklyGoal" {{ $goal->period === 'weekly' ? 'checked' : '' }}>
                            <label class="form-check-label w-100" for="weeklyGoal">
                                <strong>هدف أسبوعي</strong>
                                <small class="d-block text-muted">حفظ عدد محدد من الصفحات خلال الأسبوع</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">عدد الصفحات المستهدف</label>
                <div class="row g-2">
                    @for($i = 1; $i <= 10; $i++)
                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="target_pages"
                                       value="{{ $i }}" id="pages{{ $i }}"
                                       {{ $goal->target_pages == $i ? 'checked' : '' }}>
                                <label class="form-check-label" for="pages{{ $i }}">{{ $i }} {{ $i == 1 ? 'صفحة' : 'صفحات' }}</label>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="mb-4 weekly-option" style="{{ $goal->period === 'weekly' ? '' : 'display:none;' }}">
                <label class="form-label fw-bold">عدد الأيام في الأسبوع</label>
                <div class="row g-2">
                    @for($i = 1; $i <= 7; $i++)
                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="target_days_per_week"
                                       value="{{ $i }}" id="days{{ $i }}"
                                       {{ $goal->target_days_per_week == $i ? 'checked' : '' }}>
                                <label class="form-check-label" for="days{{ $i }}">{{ $i }} {{ $i == 1 ? 'يوم' : 'أيام' }}</label>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active"
                           id="isActive" value="1" {{ $goal->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="isActive">
                        <strong>تفعيل الهدف</strong>
                        <small class="d-block text-muted">ستظهر إشعارات عند عدم تحقيق الهدف</small>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>حفظ التغييرات
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
document.querySelectorAll('input[name="period"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const weeklyOption = document.querySelector('.weekly-option');
        if (this.value === 'weekly') {
            weeklyOption.style.display = 'block';
        } else {
            weeklyOption.style.display = 'none';
        }
    });
});
</script>
@endsection
