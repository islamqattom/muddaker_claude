<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>سجل التدقيق</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-secondary { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>سجل التدقيق</h1>
        <p>تاريخ الطباعة: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>التاريخ</th>
                <th>المستخدم</th>
                <th>الدور</th>
                <th>الإجراء</th>
                <th>النموذج</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $log->user?->name ?? '-' }}</td>
                <td>{{ $log->role }}</td>
                <td>
                    @switch($log->action)
                        @case('created') <span class="badge badge-success">إنشاء</span> @break
                        @case('updated') <span class="badge badge-warning">تعديل</span> @break
                        @case('deleted') <span class="badge badge-danger">حذف</span> @break
                        @case('login') <span class="badge badge-info">دخول</span> @break
                        @case('logout') <span class="badge badge-secondary">خروج</span> @break
                        @default {{ $log->action }}
                    @endswitch
                </td>
                <td>
                    @if($log->model_type)
                        {{ class_basename($log->model_type) }}
                        @if($log->model_id)
                            #{{ $log->model_id }}
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td>{{ $log->ip_address ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
