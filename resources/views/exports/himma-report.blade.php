<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>{{ str_replace(['\\', '/', '?', '*', '[', ']', ':'], '-', $title) }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #182B48; }
        h1 { font-size: 16px; text-align: center; margin: 0 0 4px; }
        h2 { font-size: 14px; margin: 16px 0 6px; color: #182B48; }
        p.subtitle { text-align: center; color: #6b7280; margin: 0 0 12px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: center; }
        table.data thead th { background: #182B48; color: #fff; font-weight: bold; }
        table.stats td { background: #f8fafc; font-weight: bold; }
        table.data tbody tr:nth-child(even) { background: #f8fafc; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    @if($subtitle)<p class="subtitle">{{ $subtitle }}</p>@endif

    @if(!empty($stats))
    <table class="data stats">
        <tbody>
            <tr>
                @foreach($stats as $label => $value)
                    <td><b>{{ $label }}</b><br>{{ $value }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
    @endif

    @foreach($tables as $table)
        @if(!empty($table['title']))<h2>{{ $table['title'] }}</h2>@endif
        <table class="data">
            <thead>
                <tr>
                    @foreach($table['headers'] as $header)<th>{{ $header }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($table['rows'] as $row)
                    <tr>
                        @foreach($row as $cell)<td>{{ $cell }}</td>@endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ count($table['headers']) }}">لا توجد بيانات</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
</body>
</html>
