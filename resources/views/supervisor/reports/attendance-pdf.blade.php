<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير الحضور - {{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tahoma', 'Segoe UI', sans-serif; font-size: 12px; line-height: 1.6; color: #333; }
        .container { max-width: 100%; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #28a745; padding-bottom: 15px; }
        .header h1 { color: #28a745; font-size: 24px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 14px; }
        .stats-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .stat-box { flex: 1; min-width: 100px; padding: 10px; text-align: center; border-radius: 5px; }
        .stat-box.primary { background: #28a745; color: white; }
        .stat-box.success { background: #28a745; color: white; }
        .stat-box.danger { background: #dc3545; color: white; }
        .stat-box.warning { background: #ffc107; color: #333; }
        .stat-box.info { background: #17a2b8; color: white; }
        .stat-box.secondary { background: #6c757d; color: white; }
        .stat-value { font-size: 20px; font-weight: bold; }
        .stat-label { font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 11px; }
        th { background: #28a745; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f8f9fa; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-secondary { background: #6c757d; color: white; }
        .footer { text-align: center; margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; color: #666; font-size: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>تقرير الحضور والغياب</h1>
            <p>الفترة: {{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}</p>
        </div>

        <div class="stats-row">
            <div class="stat-box primary">
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">إجمالي السجلات</div>
            </div>
            <div class="stat-box success">
                <div class="stat-value">{{ $stats['present'] }}</div>
                <div class="stat-label">حاضر</div>
            </div>
            <div class="stat-box danger">
                <div class="stat-value">{{ $stats['absent'] }}</div>
                <div class="stat-label">غائب</div>
            </div>
            <div class="stat-box warning">
                <div class="stat-value">{{ $stats['late'] }}</div>
                <div class="stat-label">متأخر</div>
            </div>
            <div class="stat-box info">
                <div class="stat-value">{{ $stats['excused'] }}</div>
                <div class="stat-label">بعذر</div>
            </div>
            <div class="stat-box secondary">
                <div class="stat-value">
                    @php
                        $percentage = $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100) : 0;
                    @endphp
                    {{ $percentage }}%
                </div>
                <div class="stat-label">نسبة الحضور</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الطالب</th>
                    <th>الحالة</th>
                    <th>وقت الحضور</th>
                    <th>ملاحظة</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $attendance->student->user->name }}</td>
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

        <div class="footer">
            <p>نظام تسميع القرآن الكريم - تم إنشاء التقرير في {{ now()->format('Y/m/d H:i') }}</p>
        </div>
    </div>
</body>
</html>
