<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام تسميع القرآن الكريم') - مشروع مدكر القرآني</title>

    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        :root {
            --primary: #182B48;
            --primary-light: #2A4570;
            --secondary: #B4802C;
            --accent: #B1C2E0;
            --success: #27ae60;
            --danger: #c0392b;
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --header-height: 65px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Tajawal', sans-serif;
            background: #f0f4f8;
            color: #2c3e50;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #fff;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            box-shadow: -4px 0 15px rgba(0,0,0,0.1);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.collapsed .sidebar-logo h6,
        .sidebar.collapsed .sidebar-logo small,
        .sidebar.collapsed .nav-section-title,
        .sidebar.collapsed .nav-link .badge {
            display: none;
        }

        .sidebar.collapsed .sidebar-logo img {
            width: 40px;
            height: 40px;
        }

        .sidebar.collapsed .sidebar-logo {
            padding: 20px 10px;
        }

        .sidebar.collapsed .sidebar-nav .nav-link {
            justify-content: center;
            padding: 12px;
        }

        .sidebar.collapsed .sidebar-nav .nav-link i {
            margin: 0;
        }

        /* Sidebar Toggle Button */
        #sidebarToggle {
            background: #f8f9fa;
            border: none;
            color: var(--primary);
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        #sidebarToggle:hover {
            background: var(--primary);
            color: #fff;
        }

        .sidebar-logo {
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }

        .sidebar-logo img {
            width: 60px;
            height: 60px;
        }

        .sidebar-logo h6 {
            color: #182B48;
            font-size: 14px;
            margin: 8px 0 0;
            font-weight: 600;
        }

        .sidebar-logo small {
            color: #B4802C;
            font-size: 11px;
        }

        .sidebar-nav {
            padding: 15px 0;
        }

        .nav-section-title {
            color: #182B48;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 20px 5px;
            font-weight: 600;
        }

        .sidebar .nav-link {
            color: #182B48;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
            border-radius: 0;
            font-size: 14px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #182B48;
            background: #f0f4f8;
            border-right: 3px solid #B4802C;
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .sidebar .nav-link .badge {
            margin-right: auto;
            margin-left: 0;
        }

        /* Main Content */
        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-right 0.3s ease;
        }

        .main-content.sidebar-collapsed {
            margin-right: var(--sidebar-collapsed-width);
        }

        /* Header */
        .top-header {
            position: sticky;
            top: 0;
            background: #fff;
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            z-index: 999;
            gap: 15px;
        }

        .page-title {
            flex: 1;
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f8f9fa;
            color: var(--primary);
            transition: all 0.2s;
        }

        .notification-bell:hover { background: var(--primary); color: #fff; }

        .notification-badge {
            position: absolute;
            top: -3px;
            left: -3px;
            background: var(--danger);
            color: #fff;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .user-avatar-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 25px;
            transition: all 0.2s;
        }

        .user-avatar-btn:hover { background: #f0f4f8; }

        .user-avatar-btn img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--secondary);
        }

        .user-info { text-align: right; }
        .user-info .name { font-size: 13px; font-weight: 600; color: var(--primary); line-height: 1.2; }
        .user-info .role { font-size: 11px; color: #7f8c8d; }

        /* Page Content */
        .page-content {
            flex: 1;
            padding: 25px;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: transform 0.2s;
        }

        .card-header {
            background: #fff;
            border-bottom: 2px solid #f0f4f8;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0 !important;
        }

        .card-header h5, .card-header h6 {
            color: var(--primary);
            font-weight: 700;
            margin: 0;
        }

        /* Stat Cards */
        .stat-card {
            border-radius: 12px;
            padding: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
        }

        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .stat-card .number { font-size: 2rem; font-weight: 800; line-height: 1; }
        .stat-card .label { font-size: 13px; opacity: 0.9; margin-top: 5px; }

        /* Buttons */
        .btn { border-radius: 8px; font-weight: 500; font-size: 14px; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-light); border-color: var(--primary-light); }

        /* Tables */
        .table { font-size: 14px; }
        .table th { color: var(--primary); font-weight: 600; white-space: nowrap; }
        .table tbody tr:hover { background: #f8f9fa; }

        /* Badges */
        .badge { font-size: 12px; padding: 5px 10px; border-radius: 6px; }

        /* Forms */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1.5px solid #dee2e6;
            font-size: 14px;
            padding: 10px 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(24,43,72,0.15);
        }

        .form-label { font-weight: 600; color: #495057; font-size: 14px; }

        /* Alerts */
        .alert { border-radius: 10px; border: none; }

        /* Page colors for recitation */
        .page-green { background: #27ae60; color: white; }
        .page-orange { background: #e67e22; color: white; }
        .page-red { background: #c0392b; color: white; }
        .page-black { background: #2c3e50; color: white; }
        .page-pending { background: #ecf0f1; color: #7f8c8d; }

        /* Breadcrumb */
        .breadcrumb { font-size: 13px; }
        .breadcrumb-item.active { color: var(--primary); font-weight: 600; }

        /* Mobile sidebar backdrop - hidden by default */
        .sidebar-backdrop {
            display: none;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(100%);
                width: var(--sidebar-width) !important;
            }
            .sidebar.mobile-open { transform: translateX(0); }
            .sidebar.collapsed { transform: translateX(100%); }
            .main-content { margin-right: 0 !important; }

            /* Mobile sidebar backdrop */
            .sidebar-backdrop {
                display: block;
                position: fixed;
                top: 0;
                right: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            .sidebar-backdrop.show {
                opacity: 1;
                visibility: visible;
            }

            /* Mobile header optimizations */
            .top-header { padding: 0 15px; height: 55px; }
            .page-title { font-size: 16px; }
            .user-info { display: none; }
            .user-avatar-btn { padding: 5px; }
            .user-avatar-btn img { width: 32px; height: 32px; }
            .notification-bell { width: 44px; height: 44px; }

            /* Mobile page content padding */
            .page-content { padding: 15px; }

            /* Mobile card padding */
            .card-body { padding: 15px; }

            /* Mobile button sizes - touch friendly */
            .btn { min-height: 44px; padding: 10px 16px; }
            .btn-sm { min-height: 36px; padding: 8px 12px; }

            /* Mobile stat cards */
            .stat-card .number { font-size: 1.5rem; }
            .stat-card .label { font-size: 11px; }
            .stat-card .icon { font-size: 1.8rem; }
            .stat-card { padding: 15px; }

            /* Mobile table adjustments */
            .table { font-size: 13px; }
        }

        /* Mobile close button for sidebar */
        @media (max-width: 768px) {
            .sidebar-close-btn {
                display: flex;
            }
        }
        .sidebar-close-btn {
            display: none;
            position: absolute;
            top: 15px;
            left: 15px;
            width: 36px;
            height: 36px;
            border: none;
            background: #f0f0f0;
            color: #182B48;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .sidebar-close-btn:hover { background: #e0e0e0; }

        /* Tablet and above - sidebar toggle */
        @media (min-width: 769px) {
            #mobileMenuBtn { display: none; }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #bdc3c7; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary); }

        /* Quran Font */
        .quran-text { font-family: 'Amiri', serif; font-size: 18px; line-height: 2; }

        /* Print */
        @media print {
            .sidebar, .top-header, .no-print { display: none !important; }
            .main-content { margin-right: 0 !important; }
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Sidebar Backdrop (Mobile) -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeMobileSidebar()"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <button class="sidebar-close-btn" onclick="closeMobileSidebar()" title="إغلاق">
            <i class="fas fa-times"></i>
        </button>
        <div class="sidebar-logo">
            <img src="{{ asset('images/muddaker-logo-main-nobg.png') }}" alt="Logo" style="width:140px;height:100px;">
            <h6></h6>
            <small>نظام التسميع الإلكتروني</small>
        </div>

        <div class="sidebar-nav">
            @yield('sidebar-menu')
        </div>

        <div style="padding: 15px 20px; border-top: 1px solid #e9ecef; margin-top: auto;">
            <div style="color: #6c757d; font-size: 11px; text-align: center;">
                النسخة 1.0.0 | {{ date('Y') }}
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Header -->
        <header class="top-header">
            <!-- Mobile menu toggle -->
            <button class="btn btn-sm d-lg-none" id="mobileMenuBtn" onclick="toggleMobileSidebar()" title="القائمة">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Desktop sidebar collapse toggle -->
            <button class="btn btn-sm d-none d-lg-flex" id="sidebarToggle" onclick="toggleSidebar()" title="إظهار/إخفاء القائمة الجانبية">
                <i class="fas fa-bars"></i>
            </button>

            <h1 class="page-title">@yield('page-title', 'الرئيسية')</h1>

            <div class="header-actions">
                <!-- Notifications -->
                <div class="dropdown">
                    <div class="notification-bell" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        @php $notifCount = auth()->user()->unreadNotificationsCount(); @endphp
                        @if($notifCount > 0)
                            <span class="notification-badge">{{ $notifCount }}</span>
                        @endif
                    </div>
                    <div class="dropdown-menu dropdown-menu-start p-0" style="width: 300px; max-height: 400px; overflow-y: auto;">
                        <div class="p-3 border-bottom">
                            <strong>الإشعارات</strong>
                            @if($notifCount > 0)
                                <span class="badge bg-danger ms-2">{{ $notifCount }} جديد</span>
                            @endif
                        </div>
                        @forelse(auth()->user()->notifications()->latest()->take(10)->get() as $notif)
                            <a href="{{ $notif->link ?? '#' }}" class="dropdown-item py-2 border-bottom {{ $notif->is_read ? '' : 'bg-light' }}" style="white-space: normal;">
                                <div class="fw-bold" style="font-size:13px;">{{ $notif->title }}</div>
                                <div class="text-muted" style="font-size:12px;">{{ $notif->message }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $notif->created_at->diffForHumans() }}</div>
                            </a>
                        @empty
                            <div class="p-3 text-center text-muted">لا توجد إشعارات</div>
                        @endforelse
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="user-avatar-btn" data-bs-toggle="dropdown">
                        <div class="user-info">
                            <div class="name">{{ auth()->user()->name }}</div>
                            <div class="role">@yield('role-label', auth()->user()->role)</div>
                        </div>
                        <img src="{{ auth()->user()->avatar_url }}" alt="avatar">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-start">
                        <li><a class="dropdown-item" href="{{ route(auth()->user()->role . '.profile.index') }}"><i class="fas fa-user me-2"></i>الملف الشخصي</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">
            <!-- Breadcrumb -->
            @hasSection('breadcrumb')
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            @endif

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer style="padding: 15px 25px; background: #fff; border-top: 1px solid #eee; text-align: center; color: #7f8c8d; font-size: 12px;">
            جميع الحقوق محفوظة &copy; {{ date('Y') }} - مشروع مدكر القرآني
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Desktop sidebar collapse (tablet and above)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');

            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar_collapsed', isCollapsed);
        }

        // Mobile sidebar toggle (slides in/out)
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.toggle('mobile-open');
            backdrop.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }

        // Close mobile sidebar
        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.remove('mobile-open');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Swipe to close sidebar (touch support)
        let touchStartX = 0;
        let touchEndX = 0;

        document.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        document.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });

        function handleSwipe() {
            const sidebar = document.getElementById('sidebar');
            // Swipe left to close (in RTL, sidebar slides from right)
            if (touchStartX - touchEndX > 50 && sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            }
        }

        // Close sidebar on Escape key (accessibility)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('sidebar');
                if (sidebar.classList.contains('mobile-open')) {
                    closeMobileSidebar();
                }
            }
        });

        // Restore sidebar state on page load (desktop only)
        document.addEventListener('DOMContentLoaded', function() {
            // Only apply desktop collapse state on larger screens
            if (window.innerWidth >= 769) {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.querySelector('.main-content');
                const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';

                if (isCollapsed) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('sidebar-collapsed');
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');

            if (window.innerWidth < 769) {
                // Mobile: remove collapse classes, ensure proper state
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('sidebar-collapsed');
            }
        });

        // Toastr config
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-left',
            rtl: true,
            timeOut: 4000,
        };

        // CSRF setup for AJAX
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>

    @stack('scripts')
</body>
</html>
