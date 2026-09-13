<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'يوم الهمة')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@300;400;600;700;800&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <!-- Guest styles extracted to public/css/guest.css -->
    <link rel="stylesheet" href="{{ asset('css/guest.css') }}?v={{ filemtime(public_path('css/guest.css')) }}">

    @stack('styles')
</head>
<body>
    @hasSection('hero')
        @yield('hero')
    @else
    <div class="page-hero">
        <h1><i class="fas fa-mosque me-2"></i>يوم الهمة</h1>
        <p>صفحة تقييم سرد الطالب - للأولياء والمشرفين</p>
    </div>
    @endif

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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-left", "rtl": true };
    </script>
    @stack('scripts')
</body>
</html>
