<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'يوم الهمة')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #d4a030;
            --gold-light: #e6b84d;
            --gold-soft: #fcf6e8;
            --navy: #182B48;
            --navy-light: #2A4570;
            --success: #27ae60;
            --danger: #c0392b;
            --radius: 16px;
            --radius-sm: 12px;
        }

        body {
            font-family: 'Cairo', 'Tahoma', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4eaf2 100%);
            min-height: 100vh;
            margin: 0;
        }

        .page-hero {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            color: white;
            padding: 32px 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(24,43,72,0.15);
        }

        .page-hero h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 8px 0;
        }

        .page-hero p {
            opacity: 0.85;
            margin: 0;
            font-size: 14px;
        }

        .container-main {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .page-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: 0 4px 18px rgba(24,43,72,0.08);
            padding: 28px;
            margin-bottom: 22px;
        }

        .page-card h2 {
            color: var(--navy);
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--gold-soft);
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 15px;
            transition: all 0.2s;
        }

        .btn-gold:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(212,160,48,0.4); color: white; }
        .btn-gold:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .btn-outline-navy {
            background: white;
            color: var(--navy);
            border: 2px solid var(--navy);
            padding: 10px 22px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-outline-navy:hover { background: var(--navy); color: white; }

        .form-control, .form-select {
            border: 2px solid #e4eaf2;
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 0.2rem rgba(212,160,48,0.15);
        }

        .label-text {
            color: var(--navy);
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
        }

        .part-card {
            border: 2px solid #e4eaf2;
            border-radius: var(--radius-sm);
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .part-card:hover { border-color: var(--gold); transform: translateY(-2px); }
        .part-card.selected { background: var(--gold-soft); border-color: var(--gold); }

        .part-card .num {
            font-size: 22px;
            font-weight: 800;
            color: var(--navy);
        }

        .part-card .lbl {
            font-size: 12px;
            color: #6b7a90;
            margin-top: 4px;
        }

        .part-checkbox { display: none; }
        .part-checkbox:checked + .part-card { background: var(--gold-soft); border-color: var(--gold); }

        .progress-bar-wrap {
            background: #e4eaf2;
            border-radius: 8px;
            height: 10px;
            overflow: hidden;
            margin-top: 8px;
        }
        .progress-bar-fill {
            background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%);
            height: 100%;
            width: 0%;
            transition: width 0.3s;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 8px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px 6px;
            background: white;
            border-radius: var(--radius-sm);
            box-shadow: 0 2px 8px rgba(24,43,72,0.05);
            font-size: 13px;
            color: #6b7a90;
            font-weight: 600;
        }
        .step.active {
            background: var(--navy);
            color: white;
        }
        .step .step-num {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            margin-left: 6px;
            font-size: 12px;
        }
        .step.active .step-num { background: var(--gold); color: var(--navy); }

        .part-section {
            background: white;
            border-radius: var(--radius);
            box-shadow: 0 4px 18px rgba(24,43,72,0.08);
            margin-bottom: 18px;
            overflow: hidden;
        }

        .part-section-header {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            color: white;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .part-section-header h3 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .part-section-header .pages-range {
            font-size: 13px;
            opacity: 0.9;
        }

        .part-section-body {
            padding: 0;
        }

        .page-row {
            padding: 14px 22px;
            border-bottom: 1px solid #eef1f6;
            display: grid;
            grid-template-columns: 110px 1fr 1fr;
            gap: 24px;
            align-items: center;
            justify-items: center;
        }
        .page-row:last-child { border-bottom: none; }
        .page-row.header {
            background: linear-gradient(135deg, #f8f9fc 0%, #eef1f6 100%);
            font-weight: 700;
            color: var(--navy);
            font-size: 14px;
            padding: 14px 22px;
            border-bottom: 2px solid var(--gold-soft);
            justify-items: center;
        }

        .page-number-badge {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            color: white;
            font-weight: 800;
            padding: 10px 16px;
            border-radius: 10px;
            text-align: center;
            font-size: 15px;
            min-width: 80px;
            box-shadow: 0 3px 8px rgba(212,160,48,0.25);
        }

        .scoring-input-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            max-width: 260px;
        }

        .scoring-input-group input {
            flex: 1;
            min-width: 80px;
            text-align: center;
            border: 2px solid #e4eaf2;
            border-radius: 10px;
            padding: 10px 12px;
            font-weight: 800;
            font-size: 18px;
            background: #fafbfd;
        }

        .scoring-input-group input:focus {
            border-color: var(--gold);
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(212,160,48,0.15);
        }

        .counter-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: none;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .counter-btn.minus { background: var(--danger); }
        .counter-btn.plus { background: var(--success); }
        .counter-btn:hover { transform: scale(1.08); }

        .errors-col, .hesitations-col { width: 100%; display: flex; justify-content: center; }
        .errors-col .scoring-input-group input { color: var(--danger); }
        .hesitations-col .scoring-input-group input { color: #b36b00; }

        .summary-box {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            color: white;
            border-radius: var(--radius);
            padding: 22px;
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }

        .summary-item {
            background: rgba(255,255,255,0.1);
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            text-align: center;
        }

        .summary-item .lbl { font-size: 12px; opacity: 0.85; margin-bottom: 4px; }
        .summary-item .val { font-size: 22px; font-weight: 800; }

        .alert-gold {
            background: var(--gold-soft);
            border: 1px solid var(--gold);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            color: var(--navy);
            font-size: 14px;
        }

        @media (max-width: 700px) {
            .page-row { grid-template-columns: 80px 1fr 1fr; gap: 12px; padding: 12px 14px; }
            .scoring-input-group { max-width: none; }
            .scoring-input-group input { min-width: 50px; font-size: 15px; padding: 8px; }
            .counter-btn { width: 34px; height: 34px; font-size: 14px; }
            .page-number-badge { font-size: 13px; padding: 6px 10px; min-width: 60px; }
            .page-hero h1 { font-size: 22px; }
            .page-card { padding: 18px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="page-hero">
        <h1><i class="fas fa-mosque me-2"></i>يوم الهمة</h1>
        <p>صفحة تقييم سرد الطالب - للأولياء والمشرفين</p>
    </div>

    <div class="container-main">
        @if(isset($step))
        <div class="step-indicator">
            <div class="step {{ $step >= 1 ? 'active' : '' }}"><span class="step-num">1</span> البيانات الأساسية</div>
            <div class="step {{ $step >= 2 ? 'active' : '' }}"><span class="step-num">2</span> اختيار الأجزاء</div>
            <div class="step {{ $step >= 3 ? 'active' : '' }}"><span class="step-num">3</span> تقييم الصفحات</div>
        </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
