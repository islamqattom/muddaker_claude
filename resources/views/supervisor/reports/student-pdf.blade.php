<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير {{ $student->user->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tahoma', 'Segoe UI', sans-serif; font-size: 12px; line-height: 1.6; color: #333; }
        .container { max-width: 100%; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #182B48; padding-bottom: 15px; }
        .header h1 { color: #182B48; font-size: 24px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 14px; }
        .student-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .student-info-row { display: flex; justify-content: space-around; margin-bottom: 10px; }
        .student-info-item { text-align: center; }
        .stats-section { margin-bottom: 20px; }
        .stats-section h3 { color: #182B48; font-size: 16px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .stats-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px; }
        .stat-box { flex: 1; min-width: 80px; padding: 8px; text-align: center; border-radius: 5px; }
        .stat-box.success { background: #28a745; color: white; }
        .stat-box.danger { background: #dc3545; color: white; }
        .stat-box.warning { background: #ffc107; color: #333; }
        .stat-box.info { background: #17a2b8; color: white; }
        .stat-box.primary { background: #182B48; color: white; }
        .stat-value { font-size: 18px; font-weight: bold; }
        .stat-label { font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: center; font-size: 10px; }
        th { background: #182B48; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f8f9fa; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-secondary { background: #6c757d; color: white; }
        .badge-dark { background: #343a40; color: white; }
        .footer { text-align: center; margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; color: #666; font-size: 10px; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>تقرير طالب</h1>
            <p>الفترة: {{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}</p>
        </div>

        <div class="student-info">
            <div class="student-info-row">
                <div class="student-info-item">
                    <strong>الاسم:</strong> {{ $student->user->name }}
                </div>
                <div class="student-info-item">
                    <strong>المستوى:</strong> {{ $student->level->name ?? '-' }}
                </div>
            </div>
            <div class="student-info-row">
                <div class="student-info-item">
                    <strong>رقم الطالب:</strong> {{ $student->student_number }}
                </div>
                <div class="student-info-item">
                    <strong>المشرف:</strong> {{ $student->supervisor->user->name ?? '-' }}
                </div>
            </div>
        </div>

        <div class="stats-section">
            <h3>إحصائيات الحضور</h3>
            <div class="stats-row">
                <div class="stat-box success">
                    <div class="stat-value">{{ $attendanceStats['present'] }}</div>
                    <div class="stat-label">حاضر</div>
                </div>
                <div class="stat-box danger">
                    <div class="stat-value">{{ $attendanceStats['absent'] }}</div>
                    <div class="stat-label">غائب</div>
                </div>
                <div class="stat-box warning">
                    <div class="stat-value">{{ $attendanceStats['late'] }}</div>
                    <div class="stat-label">متأخر</div>
                </div>
                <div class="stat-box info">
                    <div class="stat-value">{{ $attendanceStats['excused'] }}</div>
                    <div class="stat-label">بعذر</div>
                </div>
                <div class="stat-box primary">
                    <div class="stat-value">
                        @php
                            $percentage = $attendanceStats['total'] > 0 ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100) : 0;
                        @endphp
                        {{ $percentage }}%
                    </div>
                    <div class="stat-label">نسبة الحضور</div>
                </div>
            </div>
        </div>

        <div class="stats-section">
            <h3>إحصائيات التسميع</h3>
            <div class="stats-row">
                <div class="stat-box primary">
                    <div class="stat-value">{{ $sessionStats['total'] }}</div>
                    <div class="stat-label">إجمالي الجلسات</div>
                </div>
                <div class="stat-box success">
                    <div class="stat-value">{{ $sessionStats['passed'] }}</div>
                    <div class="stat-label">ناجح</div>
                </div>
                <div class="stat-box danger">
                    <div class="stat-value">{{ $sessionStats['failed'] }}</div>
                    <div class="stat-label">راسب</div>
                </div>
                <div class="stat-box warning">
                    <div class="stat-value">
                        @php
                            $passRate = $sessionStats['total'] > 0 ? round(($sessionStats['passed'] / $sessionStats['total']) * 100) : 0;
                        @endphp
                        {{ $passRate }}%
                    </div>
                    <div class="stat-label">نسبة النجاح</div>
                </div>
            </div>
        </div>

        @if($sessions->isNotEmpty())
        <div class="stats-section">
            <h3>جلسات التسميع</h3>
            <table>
                <thead>
                    <tr>
                        <th>النوع</th>
                        <th>الجزء</th>
                        <th>الصفحات</th>
                        <th>الأخطاء</th>
                        <th>الترددات</th>
                        <th>العلامة</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                    <tr>
                        <td>{{ $session->type_label }}</td>
                        <td>
                            @if($session->part)
                                الجزء {{ $session->part->part_number }}
                            @elseif($session->mafsil_type)
                                مفصل ({{ $session->mafsil_type }})
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $session->total_pages ?? '-' }}</td>
                        <td>{{ $session->total_errors ?? 0 }}</td>
                        <td>{{ $session->total_hesitations ?? 0 }}</td>
                        <td>
                            @if($session->final_score !== null)
                                {{ number_format($session->final_score, 1) }}%
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $session->status === 'passed' ? 'badge-success' : 'badge-danger' }}">
                                {{ $session->status_label }}
                            </span>
                        </td>
                        <td>{{ $session->session_date->format('Y/m/d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($attendances->isNotEmpty())
        <div class="stats-section">
            <h3>سجلات الحضور</h3>
            <table>
                <thead>
                    <tr>
                        <th>الحالة</th>
                        <th>وقت الحضور</th>
                        <th>ملاحظة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                    <tr>
                        <td>
                            @switch($attendance->status)
                                @case('present')
                                    <span class="badge badge-success">حاضر</span>
                                    @break
                                @case('absent')
                                    <span class="badge badge-danger">غائب</span>
                                    @break
                                @case('late')
                                    <span class="badge badge-warning">متأخر</span>
                                    @break
                                @case('excused')
                                    <span class="badge badge-info">بعذر</span>
                                    @break
                                @case('holiday')
                                    <span class="badge" style="background-color: #6f42c1;">عطلة</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">غير محدد</span>
                            @endswitch
                        </td>
                        <td>{{ $attendance->arrival_time ?? '-' }}</td>
                        <td>{{ $attendance->notes ?? '-' }}</td>
                        <td>{{ $attendance->date->format('Y/m/d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="footer">
            <p>نظام تسميع القرآن الكريم - تم إنشاء التقرير في {{ now()->format('Y/m/d H:i') }}</p>
        </div>
    </div>
</body>
</html>
