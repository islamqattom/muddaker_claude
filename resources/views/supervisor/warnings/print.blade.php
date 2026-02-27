<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنذار - {{ $warning->warning_number }}</title>
    <style>
        @font-face {
            font-family: 'Tajawal';
            src: url('https://fonts.gstatic.com/s/tajawal/v9/Iura6YBj_oCad4k1rzaLCr5IlLA.woff2') format('woff2');
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Tajawal', Arial, sans-serif;
            font-size: 14px;
            color: #000;
            direction: rtl;
            padding: 20px;
        }
        .page { max-width: 210mm; margin: 0 auto; }
        .header {
            text-align: center;
            border-bottom: 3px solid #1a5276;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h2 { color: #1a5276; font-size: 22px; margin-bottom: 5px; }
        .header h3 { color: #c9a227; font-size: 18px; font-weight: bold; }
        .header p { color: #666; font-size: 12px; }

        .warning-meta {
            display: flex;
            justify-content: space-between;
            background: #f8f9fa;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .meta-item { text-align: center; }
        .meta-label { color: #666; font-size: 11px; }
        .meta-value { font-weight: bold; color: #1a5276; font-size: 14px; }

        .section { margin-bottom: 20px; }
        .section-title {
            background: #1a5276;
            color: #fff;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 14px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            text-align: right;
        }
        th { background: #e8f4fd; color: #1a5276; font-weight: bold; }
        tr:nth-child(even) { background: #f8f9fa; }

        .content-box {
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 6px;
            background: #fffbf0;
            line-height: 2;
            font-size: 13px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        .stat-box {
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        .stat-box .num { font-size: 24px; font-weight: bold; color: #1a5276; }
        .stat-box .lbl { font-size: 11px; color: #666; }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .signature-box { text-align: center; width: 200px; }
        .signature-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; font-size: 12px; }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 11px;
        }

        .badge-warning {
            display: inline-block;
            background: #ffc107;
            color: #000;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="page">
    <!-- Header -->
    <div class="header">
        <h2>جمعية المحافظة على القرآن الكريم</h2>
        <h3>⚠️ إنذار رسمي ⚠️</h3>
        <p>تعمل هذه الجمعية على المحافظة على كتاب الله وتعليم حفاظه</p>
    </div>

    <!-- Meta Info -->
    <div class="warning-meta">
        <div class="meta-item">
            <div class="meta-label">رقم الإنذار</div>
            <div class="meta-value">{{ $warning->warning_number }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">تاريخ الإنذار</div>
            <div class="meta-value">{{ $warning->issue_date->format('d/m/Y') }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">نوع الإنذار</div>
            <div class="meta-value">{{ $warning->type_label }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">المشرف</div>
            <div class="meta-value">{{ $warning->issuedBy->name }}</div>
        </div>
    </div>

    <!-- Student Info -->
    <div class="section">
        <div class="section-title">📋 معلومات الطالب</div>
        <table>
            <tr>
                <th width="25%">اسم الطالب</th>
                <td>{{ $warning->student->user->name }}</td>
                <th width="25%">رقم الطالب</th>
                <td>{{ $warning->student->student_number }}</td>
            </tr>
            <tr>
                <th>المستوى</th>
                <td>{{ $warning->student->level->name ?? '-' }}</td>
                <th>المشرف المسؤول</th>
                <td>{{ $warning->student->supervisor->name ?? '-' }}</td>
            </tr>
            @if($warning->from_date && $warning->to_date)
            <tr>
                <th>الفترة المعنية</th>
                <td colspan="3">من {{ $warning->from_date->format('d/m/Y') }} إلى {{ $warning->to_date->format('d/m/Y') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Warning Content -->
    <div class="section">
        <div class="section-title">📝 موضوع الإنذار: {{ $warning->title }}</div>
        <div class="content-box">{{ $warning->content }}</div>
    </div>

    <!-- Attendance Stats (if applicable) -->
    @if($warning->type === 'attendance' && $warning->total_days)
    <div class="section">
        <div class="section-title">📅 إحصائيات الحضور والغياب</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="num" style="color:#27ae60;">{{ $warning->total_days - $warning->absent_days - $warning->late_days }}</div>
                <div class="lbl">أيام الحضور</div>
            </div>
            <div class="stat-box">
                <div class="num" style="color:#c0392b;">{{ $warning->absent_days }}</div>
                <div class="lbl">أيام الغياب</div>
            </div>
            <div class="stat-box">
                <div class="num" style="color:#f39c12;">{{ $warning->late_days }}</div>
                <div class="lbl">أيام التأخير</div>
            </div>
            <div class="stat-box">
                <div class="num">{{ $warning->total_days }}</div>
                <div class="lbl">إجمالي الأيام</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recitation Stats (if applicable) -->
    @if($warning->type === 'recitation' && $warning->total_sessions)
    <div class="section">
        <div class="section-title">🎤 إحصائيات التسميع</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="num">{{ $warning->total_sessions }}</div>
                <div class="lbl">إجمالي الجلسات</div>
            </div>
            <div class="stat-box">
                <div class="num" style="color:#27ae60;">{{ $warning->total_sessions - $warning->failed_sessions }}</div>
                <div class="lbl">جلسات ناجحة</div>
            </div>
            <div class="stat-box">
                <div class="num" style="color:#c0392b;">{{ $warning->failed_sessions }}</div>
                <div class="lbl">جلسات راسبة</div>
            </div>
            <div class="stat-box">
                <div class="num" style="color:#c0392b;">
                    {{ $warning->total_sessions > 0 ? round(($warning->failed_sessions / $warning->total_sessions) * 100) : 0 }}%
                </div>
                <div class="lbl">نسبة الرسوب</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Additional Notes -->
    @if($warning->additional_notes)
    <div class="section">
        <div class="section-title">📌 ملاحظات إضافية</div>
        <div class="content-box">{{ $warning->additional_notes }}</div>
    </div>
    @endif

    <!-- Signature -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">توقيع المشرف</div>
            <div style="font-size:12px;color:#666;margin-top:5px;">{{ $warning->issuedBy->name }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">توقيع ولي الأمر</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">توقيع إدارة الجمعية</div>
        </div>
    </div>

    <div class="footer">
        جمعية المحافظة على القرآن الكريم | نظام التسميع الإلكتروني | {{ $warning->issue_date->format('Y') }}
    </div>
</div>
</body>
</html>
