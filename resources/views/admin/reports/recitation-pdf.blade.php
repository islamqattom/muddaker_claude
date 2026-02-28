<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تقرير التسميع</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tahoma', sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin-bottom: 5px; }
        .header p { font-size: 12px; color: #666; }
        .date-range { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .status-passed { color: green; }
        .status-failed { color: red; }
        .footer { text-align: center; font-size: 10px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>تقرير جلسات التسميع</h1>
        <p>نظام إدارة حفظ القرآن الكريم</p>
    </div>

    <div class="date-range">
        <p>الفترة: من {{ $fromDate->format('d/m/Y') }} إلى {{ $toDate->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم الطالب</th>
                <th>المشرف</th>
                <th>الجزء</th>
                <th>الصفحات</th>
                <th>التاريخ</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sessions as $index => $session)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $session->student->user->name }}</td>
                <td>{{ $session->supervisor->name ?? '-' }}</td>
                <td>{{ $session->part->name ?? '-' }}</td>
                <td>{{ $session->from_page }} - {{ $session->to_page }}</td>
                <td>{{ $session->session_date?->format('d/m/Y') }}</td>
                <td class="{{ $session->status === 'passed' ? 'status-passed' : 'status-failed' }}">
                    {{ $session->status === 'passed' ? 'ناجح' : ($session->status === 'failed' ? 'راسب' : $session->status) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>تاريخ الطباعة: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
