<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Tajawal', 'Cairo', sans-serif; }
        h1 { text-align: center; color: #182B48; margin-bottom: 4px; font-size: 18px; }
        .sub { text-align: center; color: #6b7280; font-size: 11px; margin-bottom: 12px; }
        .stats { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .stats td { border: 1px solid #d4a030; padding: 6px; text-align: center; font-weight: 700; background: #fff8e1; }
        table.data { width: 100%; border-collapse: collapse; font-size: 11px; }
        table.data thead th {
            background: #182B48; color: #fff; padding: 8px; border: 1px solid #182B48;
            font-weight: 700; text-align: center;
        }
        @if($orient ?? 'portrait') @endif
        table.data tbody td { border: 1px solid #e5e7eb; padding: 6px; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    @if($subtitle)<div class="sub">{{ $subtitle }}</div>@endif

    @if(!empty($stats))
    <table class="stats">
        <tr>
            @foreach($stats as $label => $value)
                <td>{{ $label }}: {{ $value }}</td>
            @endforeach
        </tr>
    </table>
    @endif

    <table class="data">
        <thead>
            <tr>
                @foreach($headers as $h)<th>{{ $h }}</th>@endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $cell)<td>{{ $cell }}</td>@endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    @php $summaryMode = $summaryMode ?? 'recitation'; @endphp
    @if(!empty($summary))
    <h1 style="font-size:14px; margin:18px 0 8px;">ملخص الطلاب</h1>
    <table class="data">
        <thead>
            <tr>
                <th>الطالب</th>
                @if($summaryMode !== 'attendance')
                    <th>الجلسات</th><th>ناجح</th><th>نسبة النجاح</th><th>الصفحات</th><th>أيام لم يسمع</th>
                @endif
                @if($summaryMode !== 'recitation')
                    <th>حضور</th><th>غياب</th><th>تأخير</th><th>نسبة الحضور</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($summary as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    @if($summaryMode !== 'attendance')
                        <td>{{ $row['sessions'] }}</td>
                        <td>{{ $row['passed'] }}</td>
                        <td>{{ $row['pass_rate'] }}%</td>
                        <td>{{ $row['pages'] }}</td>
                        <td>{{ $row['not_recite_days'] ?? 0 }}</td>
                    @endif
                    @if($summaryMode !== 'recitation')
                        <td>{{ $row['att_present'] }}</td>
                        <td>{{ $row['att_absent'] }}</td>
                        <td>{{ $row['att_late'] }}</td>
                        <td>{{ is_null($row['attendance_rate']) ? '—' : $row['attendance_rate'] . '%' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>
