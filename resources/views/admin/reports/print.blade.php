<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} - طباعة</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        @page { margin: 14mm; }
        body {
            font-family: 'Tajawal', 'Cairo', 'Amiri', sans-serif;
            color: #182B48;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .print-bar {
            position: sticky;
            top: 0;
            background: #182B48;
            color: #fff;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
            z-index: 10;
        }
        .print-bar button {
            background: #d4a030;
            color: #182B48;
            border: 0;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }
        .print-bar button:hover { background: #B4802C; color: #fff; }
        .meta { text-align: center; margin: 18px 0 12px; }
        .meta h1 { font-size: 20px; margin: 0 0 4px; color: #182B48; }
        .meta p { margin: 0; color: #6b7280; font-size: 13px; }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 14px;
        }
        .stats-table td {
            border: 1px solid #d4a030;
            padding: 8px 12px;
            text-align: center;
            font-weight: 700;
            background: #fff8e1;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        table.data thead th {
            background: #182B48;
            color: #fff;
            padding: 9px 8px;
            border: 1px solid #182B48;
            font-weight: 700;
            text-align: center;
        }
        table.data tbody td {
            border: 1px solid #e5e7eb;
            padding: 7px 8px;
            text-align: center;
        }
        table.data tbody tr:nth-child(even) { background: #f8fafc; }
        footer { text-align: center; margin-top: 18px; color: #6b7280; font-size: 11px; }

        @media print {
            .print-bar { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="print-bar">
        <span>{{ $title }} — معاينة قبل الطباعة</span>
        <div>
            <button onclick="window.print()">🖨️ طباعة</button>
            <button onclick="window.close()" style="background:transparent;color:#fff;border:1px solid rgba(255,255,255,.3);">إغلاق</button>
        </div>
    </div>

    <div class="meta">
        <h1>{{ $title }}</h1>
        @if($subtitle)<p>{{ $subtitle }}</p>@endif
    </div>

    @if(!empty($stats))
    <table class="stats-table">
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
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)<td>{{ $cell }}</td>@endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headers) }}" style="padding:24px;color:#6b7280;">لا توجد بيانات</td></tr>
            @endforelse
        </tbody>
    </table>

    <footer>
        تم إنشاء التقرير في {{ now()->format('Y/m/d H:i') }} — {{ config('app.name', config('app.center_name', 'نظام مدكر')) }}
    </footer>
</body>
</html>
