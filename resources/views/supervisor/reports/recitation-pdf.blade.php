<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير التسميع - {{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border body { font-family-box; }
       : 'Tahoma', 'Segoe UI', sans-serif; font-size: 12px; line-height: 1.6; color: #333; }
        .container { max-width: 100%; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #182B48; padding-bottom: 15px; }
        .header h1 { color: #182B48; font-size: 24px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 14px; }
        .stats-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .stat-box { flex: 1; min-width: 100px; padding: 10px; text-align: center; border-radius: 5px; }
        .stat-box.primary { background: #182B48; color: white; }
        .stat-box.success { background: #28a745; color: white; }
        .stat-box.danger { background: #dc3545; color: white; }
        .stat-box.warning { background: #ffc107; color: #333; }
        .stat-box.info { background: #17a2b8; color: white; }
        .stat-box.secondary { background: #6c757d; color: white; }
        .stat-value { font-size: 20px; font-weight: bold; }
        .stat-label { font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 11px; }
        th { background: #182B48; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f8f9fa; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-secondary { background: #6c757d; color: white; }
        .badge-dark { background: #343a40; color: white; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-warning { color: #ffc107; }
        .text-muted { color: #6c757d; }
        .footer { text-align: center; margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; color: #666; font-size: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>تقرير التسميع</h1>
            <p>الفترة: {{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}</p>
        </div>

        <div class="stats-row">
            <div class="stat-box primary">
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">إجمالي الجلسات</div>
            </div>
            <div class="stat-box success">
                <div class="stat-value">{{ $stats['passed'] }}</div>
                <div class="stat-label">ناجح</div>
            </div>
            <div class="stat-box danger">
                <div class="stat-value">{{ $stats['failed'] }}</div>
                <div class="stat-label">راسب</div>
            </div>
            <div class="stat-box warning">
                <div class="stat-value">{{ $stats['total_errors'] }}</div>
                <div class="stat-label">إجمالي الأخطاء</div>
            </div>
            <div class="stat-box info">
                <div class="stat-value">{{ $stats['total_hesitations'] }}</div>
                <div class="stat-label">إجمالي الترددات</div>
            </div>
            <div class="stat-box secondary">
                <div class="stat-value">{{ $stats['total_pages'] }}</div>
                <div class="stat-label">إجمالي الصفحات</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الطالب</th>
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
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $session->student->user->name }}</td>
                    <td>{{ $session->type_label }}</td>
                    <td>{{ $session->parts_label }}</td>
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
                        <span class="badge bg-{{ $session->status_color }}">
                            {{ $session->status_label }}
                        </span>
                    </td>
                    <td>{{ $session->session_date->format('Y/m/d') }}</td>
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
