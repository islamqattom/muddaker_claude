@extends('layouts.app')

@section('title', 'تقرير الطالب - ' . $student->user->name)
@section('page-title', 'تقرير البرنامج الإيماني')
@section('role-label', 'إدارة')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('content')

<div style="background:linear-gradient(135deg,#182B48 0%,#2A4570 100%);border-radius:14px;padding:16px 22px;margin-bottom:16px;color:#fff;display:flex;align-items:center;gap:18px;flex-wrap:wrap">
    <div style="width:48px;height:48px;border-radius:13px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
        <i class="fas fa-user-graduate"></i>
    </div>
    <div style="flex:1;min-width:200px">
        <h1 style="margin:0;font-size:22px;font-weight:700">{{ $student->user->name }}</h1>
        <p class="student-header-meta" style="margin:6px 0 0 0;color:rgba(255,255,255,0.8);font-size:13px">
            @if($student->student_number) رقم الطالب: #{{ $student->student_number }} @endif
            @if($student->level) • المستوى: {{ $student->level->name }} @endif
            @if($student->supervisor) • المشرف: {{ $student->supervisor->name }} @endif
        </p>
    </div>
    <a href="{{ route('admin.iman-program.index', ['scope' => 'reports']) }}"
       style="background:rgba(255,255,255,0.15);color:#fff;border:none;padding:10px 16px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px">
        <i class="fas fa-arrow-right"></i> العودة للقائمة
    </a>
</div>

<div style="background:#fff;border-radius:13px;padding:16px 16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);margin-bottom:16px">
    <form method="GET" action="{{ route('admin.iman-program.student-report', $student->id) }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">من تاريخ</label>
            <input type="date" name="from_date" class="form-control" value="{{ $fromDate->toDateString() }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">إلى تاريخ</label>
            <input type="date" name="to_date" class="form-control" value="{{ $toDate->toDateString() }}">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> عرض التقرير</button>
        </div>
    </form>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:16px">
    <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px">
        <div style="width:38px;height:38px;background:linear-gradient(135deg,#182B48,#2A4570);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#d4a030;font-size:16px"><i class="fas fa-bookmark"></i></div>
        <div><div style="font-size:12px;color:#6b7a90">إجمالي البرامج</div><div style="font-size:22px;font-weight:800;color:#182B48">{{ $overallStats['programs_count'] }}</div></div>
    </div>
    <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px">
        <div style="width:38px;height:38px;background:linear-gradient(135deg,#27ae60,#2ecc71);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px"><i class="fas fa-check-circle"></i></div>
        <div><div style="font-size:12px;color:#6b7a90">برامج نشطة</div><div style="font-size:22px;font-weight:800;color:#182B48">{{ $overallStats['active_programs'] }}</div></div>
    </div>
    <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px">
        <div style="width:38px;height:38px;background:linear-gradient(135deg,#d4a030,#e6b84d);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px"><i class="fas fa-list-check"></i></div>
        <div><div style="font-size:12px;color:#6b7a90">إجمالي المعايير</div><div style="font-size:22px;font-weight:800;color:#182B48">{{ $overallStats['total_criteria'] }}</div></div>
    </div>
    <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px">
        <div style="width:38px;height:38px;background:linear-gradient(135deg,#3498db,#5dade2);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px"><i class="fas fa-calendar-check"></i></div>
        <div><div style="font-size:12px;color:#6b7a90">إدخالات الفترة</div><div style="font-size:22px;font-weight:800;color:#182B48">{{ $overallStats['total_entries'] }}</div></div>
    </div>
</div>

@if($programs->isEmpty())
    <div style="background:#fff;border-radius:13px;padding:40px 16px;text-align:center;color:#6b7a90;box-shadow:0 2px 10px rgba(24,43,72,0.05)">
        <i class="fas fa-star-and-crescent" style="font-size:50px;opacity:0.3"></i>
        <h5 style="color:#182B48;margin-top:14px">لا توجد برامج إيمانية لهذا الطالب</h5>
        <a href="{{ route('admin.iman-program.create', ['scope' => 'student', 'student_id' => $student->id]) }}"
           style="display:inline-block;margin-top:14px;background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;padding:12px 16px;border-radius:12px;font-weight:700;text-decoration:none">
            <i class="fas fa-plus-circle"></i> أنشئ أول برنامج للطالب
        </a>
    </div>
@else
    @foreach($programs as $program)
        @php $programEntries = $entriesByProgram[$program->id] ?? collect(); @endphp
        <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);margin-bottom:16px">
            <div class="program-card-header" style="display:flex;align-items:center;gap:14px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #f0f4f8">
                <div style="width:38px;height:38px;background:linear-gradient(135deg,#d4a030,#e6b84d);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;flex-shrink:0">
                    <i class="fas fa-star-and-crescent"></i>
                </div>
                <div style="flex:1">
                    <h5 style="margin:0;color:#182B48;font-weight:700;font-size:17px">{{ $program->name }}</h5>
                    @if($program->description)
                        <p style="margin:4px 0 0 0;color:#6b7a90;font-size:13px">{{ $program->description }}</p>
                    @endif
                </div>
                <div>
                    @if($program->is_active)
                        <span style="background:rgba(39,174,96,0.15);color:#27ae60;padding:6px 14px;border-radius:13px;font-size:12px;font-weight:700">نشط</span>
                    @else
                        <span style="background:rgba(108,122,139,0.15);color:#6b7a90;padding:6px 14px;border-radius:13px;font-size:12px;font-weight:700">غير نشط</span>
                    @endif
                </div>
            </div>

            @if($program->criteria->isEmpty())
                <div style="text-align:center;color:#6b7a90;padding:16px">لا توجد معايير لهذا البرنامج</div>
            @else
                <div style="overflow-x:auto">
                    <table style="width:100%;border-collapse:collapse">
                        <thead>
                            <tr style="background:#f8f9fa">
                                <th style="padding:12px 14px;text-align:right;font-weight:700;font-size:12px;color:#182B48">المعيار</th>
                                <th style="padding:12px 14px;text-align:center;font-weight:700;font-size:12px;color:#182B48">النوع</th>
                                <th style="padding:12px 14px;text-align:center;font-weight:700;font-size:12px;color:#182B48">الهدف</th>
                                <th style="padding:12px 14px;text-align:center;font-weight:700;font-size:12px;color:#182B48">المنجز</th>
                                <th style="padding:12px 14px;text-align:center;font-weight:700;font-size:12px;color:#182B48">نسبة الإنجاز</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($program->criteria as $criterion)
                                @php
                                    $stat = $criteriaStats[$program->id][$criterion->id] ?? null;
                                    $c = $stat['completion'] ?? 0;
                                    $color = $c >= 70 ? '#27ae60' : ($c >= 40 ? '#f39c12' : '#c0392b');
                                @endphp
                                <tr style="border-bottom:1px solid #f0f4f8">
                                    <td style="padding:12px 14px;color:#182B48;font-weight:600;font-size:13px">{{ $criterion->name }}</td>
                                    <td style="padding:12px 14px;text-align:center">
                                        @if($criterion->isCheckType())
                                            <span style="background:rgba(24,43,72,0.1);color:#182B48;padding:4px 10px;border-radius:12px;font-size:11px;font-weight:600">تأشير</span>
                                        @else
                                            <span style="background:rgba(212,160,48,0.15);color:#B4802C;padding:4px 10px;border-radius:12px;font-size:11px;font-weight:600">عدد</span>
                                        @endif
                                    </td>
                                    <td style="padding:12px 14px;text-align:center;color:#6b7a90;font-size:13px">
                                        @if($criterion->isCheckType())
                                            —
                                        @else
                                            {{ $criterion->target_value ?? '—' }}
                                        @endif
                                    </td>
                                    <td style="padding:12px 14px;text-align:center;font-weight:700;color:#182B48;font-size:13px">
                                        @if($criterion->isCheckType())
                                            {{ $stat['checked'] ?? 0 }}
                                        @else
                                            {{ $stat['value_sum'] ?? 0 }}
                                        @endif
                                    </td>
                                    <td style="padding:12px 14px;text-align:center">
                                        <div style="display:flex;align-items:center;gap:8px;justify-content:center">
                                            <div style="flex:1;max-width:120px;height:8px;background:#f0f4f8;border-radius:4px;overflow:hidden">
                                                <div style="height:100%;background:{{ $color }};width:{{ $c }}%"></div>
                                            </div>
                                            <span style="font-weight:700;color:{{ $color }};font-size:13px;min-width:40px">{{ $c }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach

    @if(!empty($dailyCompletion) && count($dailyCompletion) > 1)
        <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px">
                <h5 style="color:#182B48;font-weight:700;margin:0;display:flex;align-items:center;gap:10px">
                    <i class="fas fa-chart-area" style="color:#d4a030"></i>
                    <span id="chartTitle">نقاط الإنجاز خلال الفترة</span>
                </h5>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                    <div id="chartToggleGroup" style="display:inline-flex;background:#f0f4f8;border-radius:10px;padding:4px;gap:4px">
                        <button type="button" data-range="week"
                                style="padding:8px 16px;border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;background:transparent;color:#182B48">
                            <i class="fas fa-calendar-week"></i> أسبوعي
                        </button>
                        <button type="button" data-range="month"
                                style="padding:8px 16px;border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;background:linear-gradient(135deg,#182B48,#2A4570);color:#fff">
                            <i class="fas fa-calendar"></i> شهري
                        </button>
                    </div>
                    <button type="button" id="compareToggleBtn"
                            style="padding:8px 16px;border:none;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer;background:#f0f4f8;color:#182B48;display:inline-flex;align-items:center;gap:6px">
                        <i class="fas fa-code-compare"></i> مقارنة الأسابيع
                    </button>
                    <button type="button" id="criteriaToggleBtn"
                            style="padding:8px 16px;border:none;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer;background:#f0f4f8;color:#182B48;display:inline-flex;align-items:center;gap:6px">
                        <i class="fas fa-tasks"></i> مقارنة المعايير
                    </button>
                </div>
            </div>

            <div id="comparePanel" style="display:none;background:#f8f9fc;border:1px solid rgba(24,43,72,0.08);border-radius:12px;padding:16px;margin-bottom:16px">
                <form method="GET" action="{{ route('admin.iman-program.student-report', $student->id) }}" id="compareForm">
                    @if($fromDate)
                        <input type="hidden" name="from_date" value="{{ $fromDate->toDateString() }}">
                    @endif
                    @if($toDate)
                        <input type="hidden" name="to_date" value="{{ $toDate->toDateString() }}">
                    @endif
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px">
                        <div style="font-weight:700;color:#182B48;font-size:14px">
                            <i class="fas fa-calendar-alt" style="color:#d4a030"></i> اختر الأسابيع للمقارنة (حتى 4 أسابيع)
                        </div>
                        <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:12px;color:#182B48;font-weight:600">
                            <input type="checkbox" name="compare_last_4" value="1" form="compareForm" style="accent-color:#d4a030">
                            آخر 4 أسابيع
                        </label>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px">
                        @foreach($availableWeeks as $wk)
                            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fff;border:1px solid rgba(24,43,72,0.08);border-radius:10px;cursor:pointer;transition:all 0.2s;font-size:12px;color:#182B48"
                                   onmouseover="this.style.borderColor='#d4a030'" onmouseout="this.style.borderColor='rgba(24,43,72,0.08)'">
                                <input type="checkbox" name="compare_weeks[]" value="{{ $wk['start'] }}" form="compareForm"
                                       {{ in_array($wk['start'], $requestedWeeks, true) ? 'checked' : '' }}
                                       style="accent-color:#d4a030">
                                <span style="font-weight:600">{{ $wk['short'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="compare-panel-actions" style="display:flex;gap:8px;margin-top:14px;justify-content:flex-end">
                        <a href="{{ route('admin.iman-program.student-report', $student->id) }}{{ $fromDate && $toDate ? '?from_date='.$fromDate->toDateString().'&to_date='.$toDate->toDateString() : '' }}"
                           style="padding:8px 16px;background:transparent;color:#6b7a90;border:1px solid #d6dbe5;border-radius:10px;font-weight:700;font-size:12px;text-decoration:none">
                            إعادة تعيين
                        </a>
                        <button type="submit" form="compareForm"
                                style="padding:8px 16px;background:linear-gradient(135deg,#182B48,#2A4570);color:#fff;border:none;border-radius:10px;font-weight:700;font-size:12px;cursor:pointer">
                            <i class="fas fa-sync"></i> تطبيق المقارنة
                        </button>
                    </div>
                </form>
            </div>

            <div id="singleChartWrap" style="position:relative;height:280px">
                <canvas id="pointsAreaChart"></canvas>
            </div>
            <div id="compareChartWrap" style="display:none;position:relative;height:340px">
                <canvas id="weekCompareChart"></canvas>
            </div>
            <div id="criteriaChartWrap" style="display:none;margin-top:24px">
                <div style="background:#f8f9fc;border:1px solid rgba(24,43,72,0.08);border-radius:12px;padding:16px;margin-bottom:16px">
                    <form method="GET" action="{{ route('admin.iman-program.student-report', $student->id) }}" id="criteriaForm">
                        @if($fromDate)
                            <input type="hidden" name="from_date" value="{{ $fromDate->toDateString() }}">
                        @endif
                        @if($toDate)
                            <input type="hidden" name="to_date" value="{{ $toDate->toDateString() }}">
                        @endif
                        @foreach($requestedWeeks as $wk)
                            <input type="hidden" name="compare_weeks[]" value="{{ $wk }}">
                        @endforeach
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px">
                            <div style="font-weight:700;color:#182B48;font-size:14px">
                                <i class="fas fa-tasks" style="color:#d4a030"></i> اختر المعايير للمقارنة اليومية (حتى 8)
                            </div>
                            <button type="button" id="selectAllCriteria"
                                    style="padding:6px 14px;background:transparent;color:#182B48;border:1px solid #d6dbe5;border-radius:8px;font-weight:600;font-size:11px;cursor:pointer">
                                تحديد/إلغاء الكل
                            </button>
                        </div>
                        <div style="background:#fff;border:1px dashed rgba(24,43,72,0.12);border-radius:10px;padding:10px 14px;margin-bottom:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-size:12px;color:#182B48">
                            <i class="fas fa-calendar-alt" style="color:#d4a030"></i>
                            <span style="font-weight:700">نطاق البيانات اليومية:</span>
                            <span style="color:#182B48;font-weight:600">{{ $criteriaFrom->format('Y/m/d') }} - {{ $criteriaTo->format('Y/m/d') }}</span>
                            <span style="color:#6b7a90">({{ $criteriaTo->diffInDays($criteriaFrom) + 1 }} يوم)</span>
                            @if($criteriaRangeSource === 'weeks')
                                <span style="background:rgba(39,174,96,0.15);color:#27ae60;padding:3px 10px;border-radius:10px;font-size:10px;font-weight:700">
                                    <i class="fas fa-link"></i> مربوط بأسابيع المقارنة
                                </span>
                            @else
                                <span style="background:rgba(24,43,72,0.08);color:#182B48;padding:3px 10px;border-radius:10px;font-size:10px;font-weight:700">
                                    <i class="fas fa-calendar"></i> نطاق الفترة المحدد
                                </span>
                            @endif
                        </div>
                        @if($allCriteria->isEmpty())
                            <div style="text-align:center;color:#6b7a90;padding:16px;font-size:13px">لا توجد معايير متاحة</div>
                        @else
                            @foreach($programs as $program)
                                <div style="margin-bottom:14px">
                                    <div style="font-weight:700;color:#182B48;font-size:12px;margin-bottom:8px;display:flex;align-items:center;gap:6px">
                                        <i class="fas fa-bookmark" style="color:#d4a030;font-size:11px"></i>
                                        {{ $program->name }}
                                        <span style="background:rgba(212,160,48,0.15);color:#B4802C;padding:2px 8px;border-radius:10px;font-size:10px">{{ $program->criteria->count() }} معيار</span>
                                    </div>
                                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:8px">
                                        @foreach($program->criteria as $criterion)
                                            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fff;border:1px solid rgba(24,43,72,0.08);border-radius:10px;cursor:pointer;transition:all 0.2s;font-size:12px;color:#182B48"
                                                   onmouseover="this.style.borderColor='#d4a030'" onmouseout="this.style.borderColor='rgba(24,43,72,0.08)'">
                                                <input type="checkbox" name="criteria_ids[]" value="{{ $criterion->id }}" form="criteriaForm" class="criteria-cb"
                                                       {{ in_array($criterion->id, $requestedCriteria, true) ? 'checked' : '' }}
                                                       style="accent-color:#d4a030">
                                                <span style="font-weight:600;flex:1">{{ $criterion->name }}</span>
                                                @if($criterion->isCheckType())
                                                    <span style="background:rgba(24,43,72,0.08);color:#182B48;padding:2px 8px;border-radius:10px;font-size:9px">تأشير</span>
                                                @else
                                                    <span style="background:rgba(212,160,48,0.15);color:#B4802C;padding:2px 8px;border-radius:10px;font-size:9px">{{ $criterion->target_value ?? '—' }}</span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        <div style="display:flex;gap:8px;margin-top:14px;justify-content:flex-end">
                            <button type="submit" form="criteriaForm"
                                    style="padding:8px 16px;background:linear-gradient(135deg,#182B48,#2A4570);color:#fff;border:none;border-radius:10px;font-weight:700;font-size:12px;cursor:pointer">
                                <i class="fas fa-sync"></i> تطبيق
                            </button>
                        </div>
                    </form>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px">
                    <h6 style="color:#182B48;font-weight:700;margin:0;font-size:14px">
                        <i class="fas fa-calendar-day" style="color:#d4a030"></i>
                        الأداء اليومي للمعايير خلال الفترة
                        <span style="color:#6b7a90;font-weight:500;font-size:12px">({{ $criteriaFrom->format('Y/m/d') }} - {{ $criteriaTo->format('Y/m/d') }})</span>
                    </h6>
                </div>
                <div class="criteria-canvas-wrap" style="position:relative;height:340px">
                    <canvas id="criteriaCompareChart"></canvas>
                </div>
                <div id="criteriaSummary" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:18px;padding-top:16px;border-top:1px solid #f0f4f8">
                    <div>
                        <div style="font-size:11px;color:#6b7a90;margin-bottom:4px">عدد المعايير</div>
                        <div id="critCount" style="font-size:20px;font-weight:800;color:#182B48">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#6b7a90;margin-bottom:4px">أعلى مجموع</div>
                        <div id="critMax" style="font-size:20px;font-weight:800;color:#27ae60">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#6b7a90;margin-bottom:4px">متوسط يومي</div>
                        <div id="critAvg" style="font-size:20px;font-weight:800;color:#d4a030">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#6b7a90;margin-bottom:4px">أيام النشاط</div>
                        <div id="critActive" style="font-size:20px;font-weight:800;color:#182B48">—</div>
                    </div>
                </div>
            </div>

            <div id="chartSummary" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:18px;padding-top:16px;border-top:1px solid #f0f4f8">
                <div>
                    <div style="font-size:11px;color:#6b7a90;margin-bottom:4px">إجمالي النقاط</div>
                    <div id="sumPoints" style="font-size:20px;font-weight:800;color:#182B48">—</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#6b7a90;margin-bottom:4px">المتوسط اليومي</div>
                    <div id="sumAvg" style="font-size:20px;font-weight:800;color:#182B48">—</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#6b7a90;margin-bottom:4px">أعلى يوم</div>
                    <div id="sumMax" style="font-size:20px;font-weight:800;color:#27ae60">—</div>
                </div>
                <div>
                    <div style="font-size:11px;color:#6b7a90;margin-bottom:4px">نسبة الإنجاز</div>
                    <div id="sumCompletion" style="font-size:20px;font-weight:800;color:#d4a030">—</div>
                </div>
            </div>
        </div>
    @endif
@endif

@endsection

@push('styles')
<style>
.student-header-meta {
        word-break: break-word;
        overflow-wrap: anywhere;
    }
.program-card-header { flex-wrap: wrap; }
.program-card-header > div:last-child { flex-shrink: 0; }
.compare-panel-actions { flex-wrap: wrap; }
.compare-panel-actions > * { flex: 1 1 140px; text-align: center; }
@media (max-width: 768px) {
#singleChartWrap { height: 260px !important; }
#compareChartWrap { height: 300px !important; }
.criteria-canvas-wrap { height: 300px !important; }
}
@media (max-width: 480px) {
#singleChartWrap { height: 220px !important; }
#compareChartWrap { height: 260px !important; }
.criteria-canvas-wrap { height: 260px !important; }
}
@media (max-width: 768px) {
#chartSummary, #criteriaSummary {
            grid-template-columns: repeat(2, 1fr) !important;
        }
}
@media (max-width: 380px) {
#chartSummary, #criteriaSummary {
            grid-template-columns: 1fr 1fr !important;
            gap: 8px !important;
        }
#chartSummary > div > div:last-child, #criteriaSummary > div > div:last-child {
            font-size: 17px !important;
        }
}
@media (max-width: 480px) {
#chartToggleGroup button, #compareToggleBtn, #criteriaToggleBtn {
            padding: 6px 10px !important;
            font-size: 11px !important;
            gap: 4px !important;
        }
}
@media (max-width: 576px) {
.program-card-header h5 { font-size: 15px !important; }
}
</style>
@endpush

@push('scripts')
@if(!empty($dailyCompletion) && count($dailyCompletion) > 1)
<script>
(function () {
    const weekData = @json($weekData);
    const monthData = @json($monthData);
    const weekComparison = @json($weekComparison);
    @php
        $expectedPerDay = max(1, $programs->sum(fn ($p) => $p->criteria->count()));
    @endphp
    const expectedPerDay = @json($expectedPerDay);
    const hasCompare = Array.isArray(weekComparison) && weekComparison.length > 0;

    const palette = [
        { line: 'rgba(212, 160, 48, 1)',  fill: 'rgba(212, 160, 48, 0.30)',  point: '#d4a030' },
        { line: 'rgba(39, 174, 96, 1)',   fill: 'rgba(39, 174, 96, 0.25)',   point: '#27ae60' },
        { line: 'rgba(52, 152, 219, 1)',  fill: 'rgba(52, 152, 219, 0.25)',  point: '#3498db' },
        { line: 'rgba(231, 76, 60, 1)',   fill: 'rgba(231, 76, 60, 0.25)',   point: '#e74c3c' },
    ];

    const ctx = document.getElementById('pointsAreaChart');
    if (!ctx) return;

    const c2d = ctx.getContext('2d');
    const grad = c2d.createLinearGradient(0, 0, 0, 280);
    grad.addColorStop(0, 'rgba(212, 160, 48, 0.55)');
    grad.addColorStop(1, 'rgba(212, 160, 48, 0.02)');
    const gradLine = c2d.createLinearGradient(0, 0, 0, 280);
    gradLine.addColorStop(0, 'rgba(212, 160, 48, 1)');
    gradLine.addColorStop(1, 'rgba(180, 128, 44, 1)');

    let chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthData.map(d => d.label),
            datasets: [
                {
                    label: 'نقاط الإنجاز',
                    data: monthData.map(d => d.points),
                    borderColor: gradLine,
                    backgroundColor: grad,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: monthData.length > 20 ? 2 : 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#d4a030',
                    pointBorderWidth: 2,
                },
                {
                    label: 'نسبة الإنجاز %',
                    data: monthData.map(d => d.completion),
                    borderColor: 'rgba(24, 43, 72, 0.9)',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [6, 4],
                    fill: false,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    rtl: true,
                    labels: {
                        color: '#182B48',
                        font: { size: 12, weight: '600' },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 14,
                    },
                },
                tooltip: {
                    backgroundColor: 'rgba(24, 43, 72, 0.95)',
                    titleColor: '#d4a030',
                    bodyColor: '#fff',
                    borderColor: '#d4a030',
                    borderWidth: 1,
                    rtl: true,
                    padding: 12,
                    callbacks: {
                        title: function (items) {
                            const idx = items[0].dataIndex;
                            return monthData[idx] ? monthData[idx].date : '';
                        },
                        label: function (context) {
                            if (context.datasetIndex === 0) {
                                return ' النقاط: ' + context.parsed.y + ' / ' + expectedPerDay;
                            }
                            return ' الإنجاز: ' + context.parsed.y + '%';
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(24,43,72,0.04)' },
                    ticks: { color: '#6b7a90', font: { size: 11 } },
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: Math.max(expectedPerDay, Math.ceil(expectedPerDay * 1.2)),
                    position: 'right',
                    title: { display: true, text: 'النقاط (من ' + expectedPerDay + ')', color: '#6b7a90', font: { size: 12 } },
                    grid: { color: 'rgba(24,43,72,0.05)' },
                    ticks: {
                        color: '#6b7a90',
                        precision: 0,
                        stepSize: Math.max(1, Math.ceil(expectedPerDay / 5)),
                    },
                },
                y1: {
                    beginAtZero: true,
                    max: 100,
                    position: 'left',
                    title: { display: true, text: 'الإنجاز %', color: '#6b7a90', font: { size: 12 } },
                    grid: { display: false },
                    ticks: { color: '#6b7a90', callback: v => v + '%', stepSize: 25 },
                },
            },
        },
    });

    function updateSummary(data) {
        const total = data.reduce((s, d) => s + d.points, 0);
        const avg = data.length ? Math.round(total / data.length) : 0;
        const max = data.length ? Math.max(...data.map(d => d.points)) : 0;
        const completion = data.length
            ? Math.round(data.reduce((s, d) => s + d.completion, 0) / data.length)
            : 0;
        document.getElementById('sumPoints').textContent = total;
        document.getElementById('sumAvg').textContent = avg;
        document.getElementById('sumMax').textContent = max;
        document.getElementById('sumCompletion').textContent = completion + '%';
    }

    function switchRange(range) {
        const data = range === 'week' ? weekData : monthData;
        chart.data.labels = data.map(d => d.label);
        chart.data.datasets[0].data = data.map(d => d.points);
        chart.data.datasets[0].pointRadius = data.length > 20 ? 2 : 4;
        chart.data.datasets[1].data = data.map(d => d.completion);
        chart.update();
        updateSummary(data);
    }

    updateSummary(monthData);

    function refreshChart() {
        if (!chart) return;
        chart.resize();
    }

    document.querySelectorAll('#chartToggleGroup button').forEach(btn => {
        btn.addEventListener('click', function () {
            const range = this.dataset.range;
            document.querySelectorAll('#chartToggleGroup button').forEach(b => {
                b.style.background = 'transparent';
                b.style.color = '#182B48';
            });
            this.style.background = 'linear-gradient(135deg,#182B48,#2A4570)';
            this.style.color = '#fff';
            switchRange(range);
            requestAnimationFrame(refreshChart);
        });
    });

    // ---- Compare weeks ----
    const comparePanel = document.getElementById('comparePanel');
    const compareBtn = document.getElementById('compareToggleBtn');
    const singleWrap = document.getElementById('singleChartWrap');
    const compareWrap = document.getElementById('compareChartWrap');
    const summaryEl = document.getElementById('chartSummary');
    const toggleGroup = document.getElementById('chartToggleGroup');
    const chartTitle = document.getElementById('chartTitle');

    let compareChart = null;
    let criteriaChart = null;

    if (hasCompare) {
        comparePanel.style.display = 'block';
        compareBtn.style.background = 'linear-gradient(135deg,#182B48,#2A4570)';
        compareBtn.style.color = '#fff';
        singleWrap.style.display = 'none';
        compareWrap.style.display = 'block';
        summaryEl.style.display = 'none';
        chartTitle.textContent = 'مقارنة نقاط الإنجاز بين الأسابيع';

        const cmpCtx = document.getElementById('weekCompareChart');
        const labels = weekComparison[0].labels;
        const datasets = weekComparison.map((wk, i) => {
            const c = palette[i % palette.length];
            return {
                label: wk.short,
                data: wk.points,
                borderColor: c.line,
                backgroundColor: c.fill,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#fff',
                pointBorderColor: c.point,
                pointBorderWidth: 2,
            };
        });

        compareChart = new Chart(cmpCtx, {
            type: 'line',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        rtl: true,
                        labels: {
                            color: '#182B48',
                            font: { size: 12, weight: '600' },
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 14,
                        },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(24, 43, 72, 0.95)',
                        titleColor: '#d4a030',
                        bodyColor: '#fff',
                        borderColor: '#d4a030',
                        borderWidth: 1,
                        rtl: true,
                        padding: 10,
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.dataset.label + ': ' + context.parsed.y + ' نقطة';
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(24,43,72,0.04)' },
                        ticks: { color: '#6b7a90', font: { size: 11 } },
                    },
                    y: {
                        beginAtZero: true,
                        position: 'right',
                        title: { display: true, text: 'النقاط', color: '#6b7a90', font: { size: 12 } },
                        grid: { color: 'rgba(24,43,72,0.05)' },
                        ticks: { color: '#6b7a90', precision: 0 },
                    },
                },
            },
        });
    }

    compareBtn.addEventListener('click', function () {
        const showing = comparePanel.style.display === 'block';
        const willShow = !showing;
        comparePanel.style.display = willShow ? 'block' : 'none';
        this.style.background = willShow ? 'linear-gradient(135deg,#182B48,#2A4570)' : '#f0f4f8';
        this.style.color = willShow ? '#fff' : '#182B48';
        if (willShow) {
            if (criteriaWrap) criteriaWrap.style.display = 'none';
            if (criteriaBtn) {
                criteriaBtn.style.background = '#f0f4f8';
                criteriaBtn.style.color = '#182B48';
            }
            singleWrap.style.display = hasCompare ? 'none' : 'block';
            if (hasCompare) compareWrap.style.display = 'block';
            summaryEl.style.display = hasCompare ? 'none' : 'grid';
            chartTitle.textContent = hasCompare ? 'مقارنة نقاط الإنجاز بين الأسابيع' : 'نقاط الإنجاز خلال الفترة';
            requestAnimationFrame(() => {
                if (hasCompare && compareChart) compareChart.resize();
                else if (!hasCompare) refreshChart();
            });
        } else {
            if (criteriaWrap && criteriaWrap.style.display === 'block') {
                // criteria view is open, leave it as-is
            } else {
                singleWrap.style.display = 'block';
                compareWrap.style.display = 'none';
                summaryEl.style.display = 'grid';
                chartTitle.textContent = 'نقاط الإنجاز خلال الفترة';
                requestAnimationFrame(refreshChart);
            }
        }
    });

    // ---- Compare criteria (daily) ----
    const criteriaComparison = @json($criteriaComparison);
    const criteriaDayLabels = @json($criteriaDayLabels);
    const criteriaWrap = document.getElementById('criteriaChartWrap');
    const criteriaBtn = document.getElementById('criteriaToggleBtn');
    const criteriaSelectAll = document.getElementById('selectAllCriteria');

    if (criteriaSelectAll) {
        criteriaSelectAll.addEventListener('click', function () {
            const cbs = document.querySelectorAll('.criteria-cb');
            const allChecked = Array.from(cbs).every(c => c.checked);
            cbs.forEach(c => { c.checked = !allChecked; });
        });
    }

    function renderCriteriaChart() {
        const ctx = document.getElementById('criteriaCompareChart');
        if (!ctx) return;
        if (criteriaChart) {
            criteriaChart.destroy();
        }
        const labels = criteriaDayLabels.map(d => d.label);
        const datasets = criteriaComparison.map((c, i) => {
            const col = palette[i % palette.length];
            return {
                label: c.name,
                data: c.data,
                borderColor: col.line,
                backgroundColor: col.fill,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#fff',
                pointBorderColor: col.point,
                pointBorderWidth: 2,
            };
        });

        const isWeeksSourced = @json($criteriaRangeSource) === 'weeks';
        const weekBoundaries = [];
        if (isWeeksSourced) {
            for (let i = 1; i < criteriaDayLabels.length; i++) {
                if (criteriaDayLabels[i].week_start !== criteriaDayLabels[i - 1].week_start) {
                    weekBoundaries.push(i);
                }
            }
        }
        const boundaryPlugin = {
            id: 'weekBoundary',
            afterDraw(chart) {
                if (!weekBoundaries.length) return;
                const xScale = chart.scales.x;
                const yScale = chart.scales.y;
                const ctx2 = chart.ctx;
                ctx2.save();
                ctx2.strokeStyle = 'rgba(24,43,72,0.25)';
                ctx2.lineWidth = 1;
                ctx2.setLineDash([4, 4]);
                weekBoundaries.forEach(idx => {
                    const x = xScale.getPixelForValue(idx - 0.5);
                    ctx2.beginPath();
                    ctx2.moveTo(x, yScale.top);
                    ctx2.lineTo(x, yScale.bottom);
                    ctx2.stroke();
                });
                ctx2.restore();
            }
        };
        if (window.Chart && Chart.registry) {
            try { Chart.register(boundaryPlugin); } catch (e) {}
        }

        criteriaChart = new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: datasets },
            plugins: [boundaryPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        rtl: true,
                        labels: {
                            color: '#182B48',
                            font: { size: 12, weight: '600' },
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 14,
                        },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(24, 43, 72, 0.95)',
                        titleColor: '#d4a030',
                        bodyColor: '#fff',
                        borderColor: '#d4a030',
                        borderWidth: 1,
                        rtl: true,
                        padding: 10,
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.dataset.label + ': ' + context.parsed.y;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(24,43,72,0.04)' },
                        ticks: { color: '#6b7a90', font: { size: 11 } },
                    },
                    y: {
                        beginAtZero: true,
                        position: 'right',
                        title: { display: true, text: 'النقاط', color: '#6b7a90', font: { size: 12 } },
                        grid: { color: 'rgba(24,43,72,0.05)' },
                        ticks: { color: '#6b7a90', precision: 0 },
                    },
                },
            },
        });

        const dayCount = criteriaDayLabels.length || 1;
        const totals = criteriaComparison.map(c => c.total);
        const maxT = Math.max(0, ...totals);
        const sumT = totals.reduce((a, b) => a + b, 0);
        const avg = Math.round((sumT / Math.max(1, criteriaComparison.length * dayCount)) * 10) / 10;
        let activeDays = 0;
        if (criteriaDayLabels.length && criteriaComparison.length) {
            for (let i = 0; i < criteriaDayLabels.length; i++) {
                const anyHit = criteriaComparison.some(c => (c.data[i] || 0) > 0);
                if (anyHit) activeDays++;
            }
        }
        document.getElementById('critCount').textContent = criteriaComparison.length;
        document.getElementById('critMax').textContent = maxT;
        document.getElementById('critAvg').textContent = avg;
        document.getElementById('critActive').textContent = activeDays + ' / ' + dayCount;
    }

    if (criteriaBtn && criteriaWrap) {
        criteriaBtn.addEventListener('click', function () {
            const showing = criteriaWrap.style.display === 'block';
            const willShow = !showing;
            criteriaWrap.style.display = willShow ? 'block' : 'none';
            this.style.background = willShow ? 'linear-gradient(135deg,#182B48,#2A4570)' : '#f0f4f8';
            this.style.color = willShow ? '#fff' : '#182B48';
            if (willShow) {
                if (comparePanel) comparePanel.style.display = 'none';
                if (compareBtn) {
                    compareBtn.style.background = '#f0f4f8';
                    compareBtn.style.color = '#182B48';
                }
                singleWrap.style.display = 'none';
                compareWrap.style.display = 'none';
                summaryEl.style.display = 'none';
                chartTitle.textContent = 'مقارنة المعايير يومياً';
                renderCriteriaChart();
            } else {
                if (comparePanel && comparePanel.style.display === 'block') {
                    // weeks view is open, leave it
                } else {
                    singleWrap.style.display = 'block';
                    compareWrap.style.display = 'none';
                    summaryEl.style.display = 'grid';
                    chartTitle.textContent = 'نقاط الإنجاز خلال الفترة';
                    requestAnimationFrame(refreshChart);
                }
            }
        });
    }
})();
</script>
@endif
@endpush
