@extends('guest.layout')

@section('title', 'يوم الهمة - تعديل جلسات سابقة')

@section('content')
<div class="page-card">
    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <div style="width: 52px; height: 52px; border-radius: 12px; background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 22px;">
            <i class="fas fa-edit"></i>
        </div>
        <div>
            <h2 style="margin: 0; color: var(--navy); font-size: 20px; font-weight: 700;">تعديل جلسات سابقة</h2>
            <p style="margin: 2px 0 0; color: #6b7a90; font-size: 13px;">ابحث باسم المشرف لعرض وتعديل جلساته السابقة</p>
        </div>
    </div>

    @if(session('session_updated'))
        <div class="alert alert-success text-center" style="background: #e8f5ec; border: 1px solid #27ae60; color: #1e7e3a; border-radius: var(--radius-sm); padding: 10px 14px;">
            <i class="fas fa-check me-1"></i> تم تحديث بيانات الجلسة بنجاح
        </div>
    @endif

    <form method="GET" action="{{ route('guest.yawm-al-himma.sessions.index') }}" class="row g-2 align-items-end mb-4">
        <div class="col-md-9">
            <label class="label-text">اسم المشرف</label>
            <input type="text" name="supervisor_name" value="{{ $supervisorName }}" class="form-control" placeholder="اكتب اسم المشرف للبحث" maxlength="255" autofocus>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn-gold flex-fill" style="padding: 10px 18px; font-size: 14px;">
                <i class="fas fa-search me-1"></i> بحث
            </button>
            <a href="{{ route('guest.yawm-al-himma.sessions.index') }}" class="btn btn-outline-secondary" title="مسح">
                <i class="fas fa-redo"></i>
            </a>
        </div>
    </form>

    @if($supervisorName === '')
        <div class="text-center py-4" style="color: #6b7a90;">
            <i class="fas fa-search fa-2x mb-2" style="opacity: 0.4;"></i>
            <p class="mb-0">أدخل اسم المشرف لعرض جلساته السابقة</p>
        </div>
    @elseif($sessions->isEmpty())
        <div class="text-center py-4" style="color: #6b7a90;">
            <i class="fas fa-inbox fa-2x mb-2" style="opacity: 0.4;"></i>
            <p class="mb-0">لا توجد جلسات للمشرف "{{ $supervisorName }}"</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle" style="border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr style="background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); color: white;">
                        <th style="padding: 12px 14px; border-top-right-radius: var(--radius-sm);">#</th>
                        <th style="padding: 12px 14px;">التاريخ</th>
                        <th style="padding: 12px 14px;">المشرف</th>
                        <th style="padding: 12px 14px;">الطالب</th>
                        <th style="padding: 12px 14px;">الأجزاء</th>
                        <th style="padding: 12px 14px;">الأخطاء</th>
                        <th style="padding: 12px 14px;">الترددات</th>
                        <th style="padding: 12px 14px; border-top-left-radius: var(--radius-sm); width: 110px;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $s)
                    <tr>
                        <td><span class="badge" style="background: var(--gold-soft); color: var(--navy); padding: 4px 10px; border-radius: 8px;">#{{ $s->id }}</span></td>
                        <td style="font-weight: 600;">{{ $s->session_date->format('Y-m-d') }}</td>
                        <td>{{ $s->supervisor_name }}</td>
                        <td>{{ $s->student_name }}</td>
                        <td>
                            @foreach($s->parts as $p)
                                <span class="badge" style="background: var(--navy); color: white; padding: 3px 8px; border-radius: 6px; margin: 1px; font-size: 11px;">{{ $p }}</span>
                            @endforeach
                        </td>
                        <td><span style="color: var(--danger); font-weight: 700;">{{ $s->total_errors }}</span></td>
                        <td><span style="color: #b36b00; font-weight: 700;">{{ $s->total_hesitations }}</span></td>
                        <td>
                            <a href="{{ route('guest.yawm-al-himma.sessions.edit', ['session' => $s->id]) }}" class="btn btn-sm" style="background: var(--gold); color: white; padding: 4px 12px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-pen me-1"></i> تعديل
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            {{ $sessions->links() }}
        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('guest.yawm-al-himma.index') }}" class="btn-outline-navy">
            <i class="fas fa-arrow-right me-1"></i> العودة للتسجيل
        </a>
    </div>
</div>
@endsection
