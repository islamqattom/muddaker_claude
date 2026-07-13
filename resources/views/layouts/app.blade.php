<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#182B48">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="مدكر">
    <link rel="manifest" href="/manifest.json">
    <meta name="vapid-public-key" content="{{ config('web-push.vapid.public_key', '') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <title>@yield('title', 'نظام تسميع القرآن الكريم') - مشروع مدكر القرآني</title>

    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts Arabic & Quran -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
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
            --header-height: 70px;
        }

        * { box-sizing: border-box; }

        /* Fix Arabic text character separation -
           letter-spacing breaks Arabic ligatures, causing characters to appear split */
        body, input, textarea, select, optgroup,
        .form-control, .form-select, .form-label,
        p, span, div, h1, h2, h3, h4, h5, h6,
        td, th, label, a, li, button {
            letter-spacing: 0 !important;
            word-spacing: 0 !important;
        }

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
            background: linear-gradient(180deg, #182B48 0%, #1a3356 50%, #1e3a5f 100%);
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .sidebar::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 80%, rgba(212,160,48,0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(212,160,48,0.05) 0%, transparent 40%);
            pointer-events: none;
        }

        /* Islamic Geometric Top Border */
        .sidebar-top-decoration {
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg,
                #d4a030 0%,
                #B4802C 25%,
                #d4a030 50%,
                #B4802C 75%,
                #d4a030 100%);
            z-index: 2;
        }

        /* Islamic Pattern Divider */
        .islamic-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 20px;
            position: relative;
            z-index: 1;
        }

        .islamic-divider::before,
        .islamic-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(212,160,48,0.4), transparent);
        }

        .islamic-divider-icon {
            width: 40px;
            height: 40px;
            margin: 0 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .islamic-divider-icon i {
            font-size: 20px;
            color: #d4a030;
            text-shadow: 0 0 15px rgba(212,160,48,0.5);
        }

        /* Corner Islamic Decorations */
        .sidebar-corner-decoration {
            position: absolute;
            width: 80px;
            height: 80px;
            opacity: 0.1;
            z-index: 0;
            pointer-events: none;
        }

        .sidebar-corner-decoration.top-right {
            top: 10px;
            right: 10px;
        }

        .sidebar-corner-decoration.bottom-left {
            bottom: 10px;
            left: 10px;
            transform: rotate(180deg);
        }

        .sidebar-corner-decoration svg {
            width: 100%;
            height: 100%;
        }

        /* Mosque Dome Shape */
        .sidebar-dome {
            position: absolute;
            bottom: 60px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 60px;
            opacity: 0.05;
            pointer-events: none;
        }

        .sidebar-dome::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 50px;
            background: radial-gradient(ellipse at center bottom, #d4a030 0%, transparent 70%);
            border-radius: 50% 50% 0 0;
        }

        /* Crescent Moon Decoration */
        .sidebar-crescent {
            position: absolute;
            top: 30px;
            left: 20px;
            font-size: 24px;
            color: #d4a030;
            opacity: 0.15;
            z-index: 0;
        }

        /* Star Decoration */
        .sidebar-star {
            position: absolute;
            color: #d4a030;
            opacity: 0.1;
            z-index: 0;
        }

        .sidebar-star-1 { top: 50px; left: 30px; font-size: 12px; }
        .sidebar-star-2 { top: 100px; right: 25px; font-size: 10px; }
        .sidebar-star-3 { top: 200px; left: 25px; font-size: 8px; }
        .sidebar-star-4 { bottom: 150px; right: 30px; font-size: 14px; }

        /* Islamic Border Frame */
        .sidebar-frame {
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            border: 2px solid rgba(212,160,48,0.1);
            border-radius: 0;
            pointer-events: none;
            z-index: 0;
        }

        .sidebar-frame::before {
            content: '';
            position: absolute;
            top: 4px;
            right: 4px;
            bottom: 4px;
            left: 4px;
            border: 1px solid rgba(212,160,48,0.05);
        }

.sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.collapsed .sidebar-logo h6,
        .sidebar.collapsed .sidebar-logo small,
        .sidebar.collapsed .nav-section-title,
        .sidebar.collapsed .nav-link .badge,
        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .islamic-divider,
        .sidebar.collapsed .sidebar-dome,
        .sidebar.collapsed .sidebar-corner-decoration,
        .sidebar.collapsed .sidebar-crescent,
        .sidebar.collapsed .sidebar-star,
        .sidebar.collapsed .sidebar-frame {
            display: none;
        }

        .sidebar.collapsed .sidebar-logo {
            padding: 20px 10px;
            border-bottom: none;
        }

        .sidebar.collapsed .sidebar-logo img {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .sidebar.collapsed .sidebar-footer {
            display: none;
        }

        .sidebar.collapsed .sidebar-nav .nav-link {
            justify-content: center;
            padding: 14px;
            position: relative;
        }

        .sidebar.collapsed .sidebar-nav .nav-link i {
            margin: 0;
            font-size: 18px;
        }

        .sidebar.collapsed .sidebar-nav .nav-link .icon-wrapper {
            width: 40px;
            height: 40px;
        }

        .sidebar.collapsed .nav-link[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            right: -6px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #B4802C, #d4a030);
            color: #fff;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            white-space: nowrap;
            z-index: 1001;
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
            font-weight: 500;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .sidebar.collapsed .nav-link[data-tooltip]:hover::before {
            content: '';
            position: absolute;
            right: -10px;
            top: 50%;
            transform: translateY(-50%);
            border: 8px solid transparent;
            border-right-color: #d4a030;
            z-index: 1001;
        }

        /* Sidebar Toggle Button */
        #sidebarToggle {
            background: #fff;
            border: none;
            color: #182B48;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(24,43,72,0.15);
        }

        #sidebarToggle:hover {
            background: #182B48;
            color: #fff;
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(24,43,72,0.3);
        }

        .sidebar-logo {
            padding: 25px 20px;
            text-align: center;
            position: relative;
            z-index: 1;
            border-bottom: 1px solid rgba(212,160,48,0.2);
            background: rgba(255,255,255,0.03);
        }

        .sidebar.collapsed .sidebar-logo h6,
        .sidebar.collapsed .sidebar-logo small,
        .sidebar.collapsed .nav-section-title,
        .sidebar.collapsed .nav-link .badge,
        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .sidebar-logo img {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .sidebar.collapsed .sidebar-logo {
            padding: 20px 10px;
            border-bottom: none;
        }

        .sidebar.collapsed .sidebar-footer {
            display: none;
        }

        .sidebar.collapsed .sidebar-nav .nav-link {
            justify-content: center;
            padding: 14px;
            position: relative;
        }

        .sidebar.collapsed .sidebar-nav .nav-link i {
            margin: 0;
            font-size: 18px;
        }

        .sidebar.collapsed .sidebar-nav .nav-link .icon-wrapper {
            width: 40px;
            height: 40px;
        }

        .sidebar.collapsed .nav-link[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            right: -6px;
            top: 50%;
            transform: translateY(-50%);
            background: #B4802C;
            color: #fff;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            white-space: nowrap;
            z-index: 1001;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            font-weight: 500;
        }

        .sidebar.collapsed .nav-link[data-tooltip]:hover::before {
            content: '';
            position: absolute;
            right: -10px;
            top: 50%;
            transform: translateY(-50%);
            border: 8px solid transparent;
            border-right-color: #B4802C;
            z-index: 1001;
        }

        /* Sidebar Toggle Button */
        #sidebarToggle {
            background: #fff;
            border: none;
            color: #182B48;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(24,43,72,0.15);
        }

        #sidebarToggle:hover {
            background: #182B48;
            color: #fff;
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(24,43,72,0.3);
        }

        .sidebar-logo {
            padding: 25px 20px;
            text-align: center;
            position: relative;
            z-index: 1;
            border-bottom: 1px solid rgba(212,160,48,0.2);
            background: rgba(255,255,255,0.03);
        }

        .sidebar-logo img {
            width: 120px;
            height: 90px;
            object-fit: contain;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.95);
            padding: 12px;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3), 0 0 30px rgba(255,255,255,0.1);
        }

        .sidebar-logo img:hover {
            transform: scale(1.08);
            box-shadow: 0 8px 30px rgba(0,0,0,0.4), 0 0 40px rgba(255,255,255,0.2);
        }

        .sidebar-logo h6 {
            color: #fff;
            font-size: 18px;
            margin: 12px 0 0;
            font-weight: 700;
            text-shadow: 0 0 15px rgba(255,255,255,0.3);
        }

        .sidebar-logo small {
            color: #d4a030;
            font-size: 11px;
            font-weight: 600;
            text-shadow: 0 0 10px rgba(212,160,48,0.3);
        }

        .sidebar-nav {
            padding: 20px 0;
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .nav-section-title {
            color: #d4a030;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 20px 20px 8px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 0 10px rgba(212,160,48,0.3);
            position: relative;
        }

        .nav-section-title::before {
            content: '✦';
            font-size: 8px;
            color: #d4a030;
            margin-left: 5px;
        }

        .nav-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, rgba(212,160,48,0.4), transparent);
            margin-right: 10px;
        }

        .sidebar .nav-link {
            color: #fff;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.3s ease;
            border-radius: 0;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            margin: 2px 12px;
            border-radius: 10px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            background: linear-gradient(180deg, #d4a030, #B4802C);
            border-radius: 0 8px 8px 0;
            transition: all 0.3s ease;
            opacity: 0;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.12);
            text-shadow: 0 0 10px rgba(255,255,255,0.3);
        }

        .sidebar .nav-link:hover::before {
            width: 4px;
            height: 60%;
            opacity: 1;
        }

        .sidebar .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(180,128,44,0.4) 0%, rgba(180,128,44,0.2) 100%);
            box-shadow: 0 4px 15px rgba(180,128,44,0.25);
            text-shadow: 0 0 10px rgba(255,255,255,0.3);
        }

        .sidebar .nav-link.active::before {
            width: 4px;
            height: 70%;
            opacity: 1;
            background: linear-gradient(180deg, #d4a030, #B4802C);
            box-shadow: 0 0 10px rgba(212,160,48,0.5);
        }

        .sidebar .nav-link.active .icon-wrapper {
            background: linear-gradient(135deg, #B4802C 0%, #d4a030 100%);
            box-shadow: 0 4px 12px rgba(180,128,44,0.5);
        }

        .sidebar .nav-link .icon-wrapper {
            width: 38px;
            height: 38px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            flex-shrink: 0;
            border: 1px solid rgba(212,160,48,0.2);
        }

        .sidebar .nav-link:hover .icon-wrapper {
            background: rgba(212,160,48,0.2);
            transform: scale(1.05);
            border-color: rgba(212,160,48,0.4);
            box-shadow: 0 0 15px rgba(212,160,48,0.3);
        }

        .sidebar .nav-link i {
            font-size: 16px;
            color: #d4a030;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover i,
        .sidebar .nav-link.active i {
            color: #fff;
            text-shadow: 0 0 10px rgba(255,255,255,0.5);
        }

        /* Bismillah Calligraphy Effect */
        .sidebar-bismillah {
            text-align: center;
            padding: 10px 20px;
            position: relative;
            z-index: 1;
        }

        .sidebar-bismillah span {
            font-family: 'Amiri', serif;
            font-size: 18px;
            color: #d4a030;
            text-shadow: 0 0 20px rgba(212,160,48,0.4);
            letter-spacing: 2px;
        }

        .sidebar .nav-link span {
            flex: 1;
            white-space: nowrap;
        }

        .sidebar .nav-link .badge {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 20px;
            margin-right: auto;
            box-shadow: 0 2px 10px rgba(231,76,60,0.5);
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(212,160,48,0.2);
            position: relative;
            z-index: 1;
            background: rgba(0,0,0,0.15);
        }

        .sidebar-footer-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: rgba(255,255,255,0.7);
            font-size: 11px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .sidebar-footer-content i {
            color: #d4a030;
            text-shadow: 0 0 10px rgba(212,160,48,0.5);
        }

        /* Islamic Section Dividers */
        .sidebar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -1px;
            right: 20px;
            left: 20px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(212,160,48,0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-nav .nav-link:hover::after {
            opacity: 1;
        }

        /* Scrollbar with Islamic Touch */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(24,43,72,0.5); }
        ::-webkit-scrollbar-thumb { background: linear-gradient(180deg, #d4a030, #B4802C); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: linear-gradient(180deg, #e6b84d, #c99535); }

        /* Active Link with Glow */
        .sidebar .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(180,128,44,0.4) 0%, rgba(180,128,44,0.2) 100%);
            box-shadow: 0 4px 15px rgba(180,128,44,0.25), inset 0 0 20px rgba(212,160,48,0.1);
            text-shadow: 0 0 10px rgba(255,255,255,0.3);
            border: 1px solid rgba(212,160,48,0.2);
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
            font-size: 32px;
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
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(24, 43, 72, 0.08) 0%, rgba(42, 69, 112, 0.12) 100%);
            color: var(--primary);
            transition: all 0.3s ease;
            border: 1px solid rgba(24, 43, 72, 0.06);
        }

        .notification-bell:hover {
            background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
            color: #fff;
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(24, 43, 72, 0.25);
        }

        .notification-bell i {
            font-size: 18px;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            left: -5px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
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
            border-radius: 16px;
            padding: 25px;
            color: #fff;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.2;
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .stat-card .number { font-size: 2.5rem; font-weight: 800; line-height: 1; }
        .stat-card .label { font-size: 13px; opacity: 0.9; margin-top: 8px; font-weight: 500; }

        /* Cards */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f4f8;
            padding: 20px 25px;
            border-radius: 16px 16px 0 0 !important;
        }

        .card-header h5, .card-header h6 {
            color: var(--primary);
            font-weight: 700;
            margin: 0;
        }

        .card-body {
            padding: 25px;
        }

        /* Buttons */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover {
            background: var(--primary-light);
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(24,43,72,0.3);
        }

        .btn-success { background: #27ae60; border-color: #27ae60; }
        .btn-success:hover { background: #2ecc71; border-color: #2ecc71; color: #fff; }

        .btn-warning { background: #f39c12; border-color: #f39c12; color: #fff; }
        .btn-warning:hover { background: #e67e22; border-color: #e67e22; color: #fff; }

        .btn-danger { background: #c0392b; border-color: #c0392b; }
        .btn-danger:hover { background: #e74c3c; border-color: #e74c3c; color: #fff; }

        .btn-outline-primary { border-color: #182B48; color: #182B48; }
        .btn-outline-primary:hover { background: #182B48; color: #fff; }

        /* Tables */
        .table { font-size: 14px; border-collapse: separate; border-spacing: 0; }
        .table th {
            color: var(--primary);
            font-weight: 700;
            white-space: nowrap;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            padding: 14px 16px;
        }
        .table tbody tr {
            transition: all 0.2s;
        }
        .table tbody tr:hover {
            background: #fafbfc;
        }
        .table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f0f4f8;
            vertical-align: middle;
        }

        /* Badges */
        .badge {
            font-size: 11px;
            padding: 5px 10px;
            border-radius: 8px;
            font-weight: 600;
        }

        /* Forms */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid #dee2e6;
            font-size: 14px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(24,43,72,0.15);
        }

        .form-label { font-weight: 600; color: #495057; font-size: 14px; margin-bottom: 8px; }

        /* Alerts */
        .alert { border-radius: 12px; border: none; padding: 16px 20px; }

        /* Page colors for recitation */
        .page-green { background: #27ae60; color: white; }
        .page-orange { background: #e67e22; color: white; }
        .page-red { background: #c0392b; color: white; }
        .page-black { background: #2c3e50; color: white; }
        .page-pending { background: #ecf0f1; color: #7f8c8d; }

        /* Breadcrumb */
        .breadcrumb { font-size: 13px; }
        .breadcrumb-item.active { color: var(--primary); font-weight: 600; }

        /* Page Header Component */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; }
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-icon {
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #182B48, #2A4570);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #d4a030;
            box-shadow: 0 8px 25px rgba(24,43,72,0.25);
        }

        .header-icon.quran {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            box-shadow: 0 8px 25px rgba(39,174,96,0.25);
        }

        .header-icon.supervisors {
            background: linear-gradient(135deg, #8e44ad, #9b59b6);
            box-shadow: 0 8px 25px rgba(142,68,173,0.25);
        }

        .header-text h4 {
            color: #182B48;
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 4px 0;
        }

        .header-text p {
            color: #7f8c8d;
            font-size: 14px;
            margin: 0;
        }

        .header-stats {
            display: flex;
            gap: 10px;
        }

        .stat-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
        }

        .stat-pill.primary {
            background: linear-gradient(135deg, #182B48, #2A4570);
            color: #fff;
        }

        .stat-pill i {
            color: #d4a030;
        }

        /* Table Card Component */
        .table-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #f0f4f8;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #f0f4f8;
            background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
        }

        .table-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            font-weight: 700;
            color: #182B48;
        }

        .table-title i {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #182B48, #1a3356);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4a030;
            font-size: 16px;
        }

        .table-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .record-count {
            color: #7f8c8d;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .record-count i {
            color: #d4a030;
        }

        .table-footer {
            padding: 20px 25px;
            border-top: 1px solid #f0f4f8;
            display: flex;
            justify-content: center;
        }

        .pagination-wrapper .pagination {
            margin: 0;
            gap: 5px;
        }

        .pagination-wrapper .page-link {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 14px;
            border: 1px solid #e9ecef;
            color: #182B48;
        }

        .pagination-wrapper .page-item.active .page-link {
            background: linear-gradient(135deg, #182B48, #2A4570);
            border-color: #182B48;
            color: #fff;
        }

        .pagination-wrapper .page-item.disabled .page-link {
            color: #bdc3c7;
        }

        /* Filter Card Component */
        .filter-card {
            background: #fff;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #f0f4f8;
            margin-bottom: 25px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1.5fr;
            gap: 20px;
            align-items: end;
        }

        @media (max-width: 992px) {
            .filter-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 576px) {
            .filter-grid { grid-template-columns: 1fr; }
        }

        .filter-label {
            display: block;
            color: #182B48;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .search-input {
            position: relative;
        }

        .search-input i {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #B4802C;
        }

        .search-input .form-control {
            padding-right: 40px;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        /* Modern Table */
        .modern-table {
            width: 100%;
            border-collapse: collapse;
        }

        .modern-table thead {
            background: #f8f9fa;
        }

        .modern-table th {
            padding: 16px 20px;
            font-size: 11px;
            font-weight: 700;
            color: #182B48;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e9ecef;
            white-space: nowrap;
        }

        .modern-table td {
            padding: 16px 20px;
            font-size: 14px;
            color: #2c3e50;
            border-bottom: 1px solid #f0f4f8;
            vertical-align: middle;
        }

        .modern-table tbody tr {
            transition: all 0.2s;
        }

        .modern-table tbody tr:hover {
            background: #fafbfc;
        }

        .row-num {
            color: #bdc3c7;
            font-weight: 600;
            font-size: 13px;
        }

        .user-info-cell {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .user-avatar-table {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #182B48, #2A4570);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4a030;
            font-size: 18px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #182B48, #2A4570);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4a030;
            font-size: 16px;
        }

        .user-avatar-sm {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #182B48, #2A4570);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4a030;
            font-size: 14px;
        }

        .user-avatar-lg {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #182B48, #2A4570);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4a030;
            font-size: 20px;
        }

        .user-avatar-xl {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #182B48, #2A4570);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4a030;
            font-size: 48px;
            margin: 0 auto;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            color: #182B48;
            font-weight: 600;
            font-size: 14px;
        }

        .user-username {
            color: #7f8c8d;
            font-size: 12px;
        }

        .student-number {
            font-family: monospace;
            background: #f0f4f8;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 13px;
            color: #182B48;
        }

        .level-badge {
            background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
            color: #182B48;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .no-data {
            color: #bdc3c7;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .action-btn.view {
            background: rgba(52,152,219,0.1);
            color: #3498db;
        }

        .action-btn.view:hover {
            background: #3498db;
            color: #fff;
            transform: scale(1.05);
        }

        .action-btn.edit {
            background: rgba(243,156,18,0.1);
            color: #f39c12;
        }

        .action-btn.edit:hover {
            background: #f39c12;
            color: #fff;
            transform: scale(1.05);
        }

        .action-btn.delete {
            background: rgba(192,57,43,0.1);
            color: #c0392b;
        }

        .action-btn.delete:hover {
            background: #c0392b;
            color: #fff;
            transform: scale(1.05);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            color: #d4a030;
        }

        .empty-state h5 {
            color: #182B48;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #7f8c8d;
            margin-bottom: 25px;
        }

        .supervisor-cell, .phone-cell, .date-cell {
            color: #7f8c8d;
            font-size: 13px;
        }

        /* Mobile sidebar backdrop - hidden by default */
        .sidebar-backdrop {
            display: none;
        }

        /* Mobile / Tablet (matches Bootstrap lg breakpoint) */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(100%);
                width: var(--sidebar-width) !important;
                background: linear-gradient(180deg, #182B48 0%, #1a3356 50%, #1e3a5f 100%);
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
                background: rgba(0,0,0,0.6);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                backdrop-filter: blur(4px);
            }
            .sidebar-backdrop.show {
                opacity: 1;
                visibility: visible;
            }

            /* Hide Islamic decorations on mobile for better performance */
            .sidebar-corner-decoration,
            .sidebar-crescent,
            .sidebar-star,
            .sidebar-dome {
                display: none;
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

        /* PWA Install Button - Base Styles */
        .pwa-install-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            padding: 12px 24px;
            background: linear-gradient(135deg, #182B48 0%, #2d4a7c 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(24, 43, 72, 0.4);
            transition: all 0.3s ease;
        }

        .pwa-install-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(24, 43, 72, 0.5);
        }

        .pwa-install-btn i {
            font-size: 16px;
        }

        /* Notification Permission Button */
        .notification-permission-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-family: 'Tajawal', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.35);
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .notification-permission-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(39, 174, 96, 0.45);
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        }

        .notification-permission-btn i {
            font-size: 14px;
        }

        .notification-permission-btn.granted {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            box-shadow: none;
            cursor: default;
        }

        @media (max-width: 768px) {
            .notification-permission-btn {
                padding: 8px 12px;
                font-size: 12px;
            }
            .notification-permission-btn span {
                display: none;
            }
        }

        /* Mobile close button for sidebar */
        @media (max-width: 991.98px) {
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
            background: rgba(255,255,255,0.15);
            color: #fff;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .sidebar-close-btn:hover { background: rgba(255,255,255,0.25); transform: rotate(90deg); }

        /* Tablet and above - hide mobile menu button (matches Bootstrap lg) */
        @media (min-width: 992px) {
            #mobileMenuBtn { display: none; }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #bdc3c7; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary); }

        /* Quran Font */
        .quran-text { font-family: 'Amiri', serif; font-size: 18px; line-height: 2; }

        /* Pagination */
        .pagination {
            margin: 0;
            gap: 4px;
        }
        .pagination .page-link {
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 6px;
            min-width: 40px;
            text-align: center;
        }
        .pagination .page-item .page-link {
            color: var(--primary);
            border-color: #dee2e6;
        }
        .pagination .page-item .page-link:hover {
            background: #f8f9fa;
            border-color: #dee2e6;
        }
        .pagination .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
        }
        .pagination .page-link[aria-hidden="true"] {
            font-size: 16px;
            line-height: 1;
        }
        .pagination .page-item span.page-link {
            font-size: 14px;
            padding: 6px 10px;
        }

        /* Print */
        @media print {
            .sidebar, .top-header, .no-print { display: none !important; }
            .main-content { margin-right: 0 !important; }
        }

        /* ========== NOTIFICATION DROPDOWN STYLES ========== */
        .notif-dropdown {
            background: #fff;
            border: none;
            border-radius: 16px;
            box-shadow: 0 15px 50px rgba(24, 43, 72, 0.15), 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .notif-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 20px;
            background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
            color: #fff;
            position: relative;
        }

        .notif-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
        }

        .notif-header-icon {
            width: 42px;
            height: 42px;
            background: rgba(212, 160, 48, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #d4a030;
        }

        .notif-header-content {
            flex: 1;
        }

        .notif-header-content h6 {
            margin: 0;
            font-weight: 700;
            font-size: 16px;
        }

        .notif-header-content span {
            font-size: 12px;
            opacity: 0.8;
        }

        .notif-mark-all {
            padding: 8px 14px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            color: #d4a030;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .notif-mark-all:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        .notif-list {
            max-height: 350px;
            overflow-y: auto;
        }

        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 18px;
            border-bottom: 1px solid #f0f4f8;
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
        }

        .notif-item:hover {
            background: linear-gradient(135deg, rgba(24, 43, 72, 0.03) 0%, rgba(42, 69, 112, 0.05) 100%);
        }

        .notif-item.unread {
            background: linear-gradient(135deg, rgba(212, 160, 48, 0.05) 0%, rgba(180, 128, 44, 0.08) 100%);
        }

        .notif-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(24, 43, 72, 0.1) 0%, rgba(42, 69, 112, 0.15) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #182B48;
            font-size: 16px;
            flex-shrink: 0;
        }

        .notif-icon.new {
            background: linear-gradient(135deg, #d4a030 0%, #B4802C 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(212, 160, 48, 0.35);
        }

        .notif-content {
            flex: 1;
            min-width: 0;
        }

        .notif-title {
            font-weight: 600;
            font-size: 13px;
            color: #182B48;
            margin-bottom: 4px;
        }

        .notif-message {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .notif-time {
            font-size: 11px;
            color: #95a5a6;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .notif-time i {
            color: #d4a030;
            font-size: 10px;
        }

        .notif-dot {
            width: 10px;
            height: 10px;
            background: linear-gradient(135deg, #d4a030 0%, #B4802C 100%);
            border-radius: 50%;
            position: absolute;
            top: 18px;
            left: 8px;
            box-shadow: 0 2px 8px rgba(212, 160, 48, 0.4);
        }

        .notif-empty {
            padding: 40px 20px;
            text-align: center;
        }

        .notif-empty i {
            font-size: 48px;
            color: #d4a030;
            opacity: 0.3;
            margin-bottom: 12px;
        }

        .notif-empty p {
            color: #7f8c8d;
            font-size: 14px;
            margin: 0;
        }

        .notif-footer {
            padding: 14px 18px;
            background: #f8fafc;
            border-top: 1px solid #f0f4f8;
            text-align: center;
        }

        .notif-footer-link {
            color: #182B48;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: color 0.2s;
        }

        .notif-footer-link:hover {
            color: #d4a030;
        }

        .notif-footer-link i {
            font-size: 12px;
        }

        /* ========== USER DROPDOWN STYLES ========== */
        .user-avatar-wrapper {
            position: relative;
            width: 42px;
            height: 42px;
        }

        .user-avatar-img {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #d4a030;
            transition: all 0.3s;
        }

        .user-avatar-btn:hover .user-avatar-img {
            border-color: #182B48;
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(24, 43, 72, 0.2);
        }

        .user-avatar-status {
            position: absolute;
            bottom: -3px;
            right: -3px;
            width: 12px;
            height: 12px;
            background: #27ae60;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .user-chevron {
            font-size: 10px;
            color: #95a5a6;
            margin-right: 8px;
            transition: all 0.2s;
        }

        .user-avatar-btn:hover .user-chevron {
            color: #d4a030;
            transform: rotate(180deg);
        }

        .user-dropdown {
            background: #fff;
            border: none;
            border-radius: 16px;
            box-shadow: 0 15px 50px rgba(24, 43, 72, 0.15), 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            padding: 0;
            min-width: 240px;
        }

        .user-dropdown-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px;
            background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
            position: relative;
        }

        .user-dropdown-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
        }

        .user-dropdown-avatar {
            width: 50px;
            height: 50px;
        }

        .user-dropdown-avatar img {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid rgba(212, 160, 48, 0.5);
        }

        .user-dropdown-info h6 {
            color: #fff;
            margin: 0 0 4px;
            font-weight: 700;
            font-size: 15px;
        }

        .user-dropdown-info span {
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
        }

        .user-dropdown .dropdown-item {
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
            font-size: 14px;
            color: #182B48;
            font-weight: 500;
            position: relative;
        }

        .user-dropdown .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(24, 43, 72, 0.05) 0%, rgba(42, 69, 112, 0.08) 100%);
            color: #182B48;
        }

        .user-dropdown .dropdown-item i {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, rgba(24, 43, 72, 0.08) 0%, rgba(42, 69, 112, 0.12) 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4a030;
            font-size: 14px;
        }

        .user-dropdown .dropdown-item:hover i {
            background: linear-gradient(135deg, #d4a030 0%, #B4802C 100%);
            color: #fff;
        }

        .user-dropdown .dropdown-item span {
            flex: 1;
        }

        .user-dropdown .dropdown-item.text-danger {
            color: #c0392b;
        }

        .user-dropdown .dropdown-item.text-danger:hover {
            background: rgba(192, 57, 43, 0.08);
            color: #c0392b;
        }

        .user-dropdown .dropdown-item.text-danger i {
            background: rgba(192, 57, 43, 0.1);
            color: #c0392b;
        }

        .user-dropdown .dropdown-item.text-danger:hover i {
            background: #c0392b;
            color: #fff;
        }

        .user-dropdown .dropdown-divider {
            margin: 8px 0;
            border-color: #f0f4f8;
        }

        .badge-count {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 20px;
            min-width: 20px;
            text-align: center;
        }

        /* Notification Scrollbar */
        .notif-list::-webkit-scrollbar {
            width: 4px;
        }

        .notif-list::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .notif-list::-webkit-scrollbar-thumb {
            background: #d4a030;
            border-radius: 2px;
        }
    </style>

    @stack('styles')

    <!-- Mobile fixes for recitation pages -->
    <style>
    @media (max-width: 768px) {
        .rec-session .page-hero {
            padding: 16px;
            border-radius: 16px;
        }

        .rec-session .hero-content {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }

        .rec-session .student-info {
            flex-direction: column;
            gap: 10px;
        }

        .rec-session .student-avatar {
            width: 48px;
            height: 48px;
            font-size: 18px;
        }

        .rec-session .student-name {
            font-size: 18px;
        }

        .rec-session .hero-meta {
            justify-content: center;
        }

        .rec-session .page-card-header {
            flex-direction: column;
            gap: 10px;
            text-align: center;
            padding: 12px;
        }

        .rec-session .page-title {
            justify-content: center;
        }

        .rec-session .page-card-body {
            padding: 14px;
        }

        .rec-session .page-card-body .row.g-4 {
            row-gap: 1.25rem !important;
        }

        .rec-session .page-card-body .col-4 {
            flex: 0 0 100%;
            width: 100%;
            text-align: center;
        }

        .rec-session .counter-input {
            width: 80px;
            height: 48px;
            font-size: 18px;
        }

        .rec-session .input-btn {
            width: 42px;
            height: 42px;
            font-size: 16px;
        }

        .rec-session .result-indicator {
            width: 44px;
            height: 44px;
            font-size: 16px;
        }

        .rec-session .summary-card .row .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .rec-session .summary-stat {
            padding: 12px;
        }

        .rec-session .summary-stat .stat-value {
            font-size: 20px;
        }

        .rec-session .notes-textarea {
            font-size: 16px;
        }

        .rec-session .info-alert {
            flex-direction: column;
            gap: 8px;
            text-align: center;
            padding: 12px;
        }

        .rec-session form .d-flex.flex-wrap.gap-3.justify-content-between {
            flex-direction: column;
            gap: 10px !important;
        }

        .rec-session form .d-flex.flex-wrap.gap-3.justify-content-between > a,
        .rec-session form .d-flex.flex-wrap.gap-3.justify-content-between > div {
            width: 100%;
        }

        .rec-session form .d-flex.flex-wrap.gap-3.justify-content-between > div {
            flex-direction: column;
            gap: 10px !important;
        }

        .rec-session form .d-flex.flex-wrap.gap-3.justify-content-between .save-btn,
        .rec-session form .d-flex.flex-wrap.gap-3.justify-content-between .btn-outline-secondary,
        .rec-session form .d-flex.flex-wrap.gap-3.justify-content-between .btn-stop,
        .rec-session form .d-flex.flex-wrap.gap-3.justify-content-between .btn-secondary,
        .rec-session form .d-flex.flex-wrap.gap-3.justify-content-between .btn-cancel {
            width: 100%;
            padding: 14px;
            font-size: 14px;
        }

        .rec-session .quran-verse {
            font-size: 12px;
            padding: 8px 12px;
        }

        .recitation-index .hero-section {
            padding: 16px;
        }

        .recitation-index .hero-content {
            flex-direction: column;
            gap: 12px;
        }

        .recitation-index .hero-content > .hero-icon {
            position: static;
            margin: 0 auto;
        }

        .recitation-index .hero-content > div:not(.hero-icon) {
            text-align: center;
        }

        .recitation-index .session-type-card {
            min-height: 100px;
            padding: 12px 8px;
        }

        .recitation-index .session-type-card .type-icon {
            width: 40px;
            height: 40px;
            font-size: 18px;
        }

        .recitation-index .session-type-card .type-label {
            font-size: 13px;
        }

        .recitation-index .session-type-card .type-desc {
            display: none;
        }

        .recitation-index .form-card .card-body {
            padding: 14px;
        }

        .recitation-index .student-stats {
            flex-wrap: wrap;
            gap: 8px;
        }

        .recitation-index .student-stat {
            flex: 1 1 60px;
        }

        .recitation-index .student-stat .stat-value {
            font-size: 16px;
        }

        .recitation-index .start-btn {
            width: 100%;
            padding: 14px;
        }

        .recitation-index .color-item {
            padding: 10px 12px;
        }

        .recitation-index .color-dot {
            width: 32px;
            height: 32px;
        }

        .recitation-index .color-item .fw-bold {
            font-size: 12px;
        }

        .recitation-index .session-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .recitation-index .sessions-list .card-header {
            padding: 12px 16px;
        }

        .recitation-index .form-select,
        .recitation-index .form-control {
            font-size: 16px;
        }

        .rec-session .selection-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
            padding: 12px;
        }

        .rec-session .search-box {
            min-width: 100%;
        }

        .rec-session .action-bar-sticky {
            flex-direction: column;
            gap: 10px;
        }

        .rec-session .action-bar-sticky .btn {
            width: 100%;
        }

        .rec-session .score-dashboard-body {
            padding: 14px;
        }

        .rec-session .distribution-grid {
            gap: 12px;
        }

        .rec-session .distribution-grid .form-label {
            font-size: 12px;
        }

        .rec-session .position-card {
            gap: 0;
        }

        .rec-session .position-card-head {
            border-bottom: 1px solid #f1f5f9;
            border-inline-end: none;
            padding: 10px 12px;
        }

        .rec-session .position-card-body {
            grid-template-columns: 1fr;
            padding: 10px;
            gap: 8px;
        }

        .rec-session .scoring-input-group input {
            width: 48px;
            height: 44px;
            font-size: 16px;
        }

        .rec-session .scoring-input-group button {
            width: 40px;
            height: 40px;
        }

        .rec-session .ayahs-content .amiri-text {
            font-size: 18px;
            line-height: 2;
        }

        .rec-session .modal-body {
            padding: 16px;
        }

        .rec-session .stat-pill {
            padding: 8px 12px;
        }

        .rec-session .stat-pill .stat-text .val {
            font-size: 14px;
        }

        .rec-session .session-hero .student-avatar {
            width: 52px;
            height: 52px;
            font-size: 20px;
        }

        .rec-session .session-hero .student-name {
            font-size: 18px;
        }

        .session-history .page-hero {
            padding: 18px;
        }

        .session-history .hero-icon {
            width: 48px;
            height: 48px;
            font-size: 22px;
        }

        .session-history .hero-title {
            font-size: 18px;
        }

        .session-history .filter-body {
            padding: 14px;
        }

        .session-history .stats-bar {
            padding: 12px;
            gap: 10px;
        }

        .session-history .action-btn {
            width: 40px;
            height: 40px;
            font-size: 15px;
        }

        .session-history .sessions-table {
            font-size: 12px;
        }

        .session-history .sessions-table thead th,
        .session-history .sessions-table tbody td {
            padding: 10px 6px;
        }
    }

    @media (max-width: 576px) {
        .rec-session .page-card-body .col-4 {
            padding: 0 8px;
        }

        .rec-session .summary-card .row .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .rec-session .summary-stat {
            padding: 10px;
        }

        .rec-session .summary-stat .stat-label {
            font-size: 10px;
        }

        .recitation-index .session-type-card {
            min-height: 90px;
        }

        .recitation-index .session-type-card .type-label {
            font-size: 12px;
        }

        .rec-session .scoring-input-group input {
            width: 44px;
        }
    }
    </style>

    <!-- ===== Global mobile view (Student-page style) ===== -->
    <style>
    /* --- Page header: stack vertically on mobile --- */
    @media (max-width: 768px) {
        .page-header,
        .page-header-modern {
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
        }

        .page-header .header-content,
        .page-header-modern .header-content {
            gap: 12px;
        }

        .page-header .header-icon,
        .page-header-modern .header-icon {
            width: 52px;
            height: 52px;
            font-size: 22px;
            border-radius: 14px;
        }

        .page-header .header-text h4,
        .page-header-modern .header-text h4 {
            font-size: 18px;
        }

        .page-header .header-text p,
        .page-header-modern .header-text p {
            font-size: 12px;
        }

        .page-header .header-stats,
        .page-header-modern .header-stats {
            align-self: flex-start;
        }

        .page-header .stat-pill,
        .page-header-modern .stat-pill {
            padding: 8px 16px;
            font-size: 13px;
        }
    }

    /* --- Filter card: single column on mobile --- */
    @media (max-width: 768px) {
        .filter-card {
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 16px;
        }

        .filter-grid {
            grid-template-columns: 1fr 1fr !important;
            gap: 12px !important;
        }

        .filter-actions {
            grid-column: 1 / -1;
            flex-wrap: wrap;
        }

        .filter-actions .btn {
            flex: 1;
            min-width: 0;
            padding: 10px 12px;
            font-size: 13px;
        }

        .filter-actions .btn i {
            margin: 0 !important;
        }
    }

    @media (max-width: 576px) {
        .filter-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* --- Form controls: 16px font prevents iOS auto-zoom --- */
    @media (max-width: 768px) {
        .form-control,
        .form-select,
        .form-label,
        textarea {
            font-size: 16px;
        }

        .form-control,
        .form-select {
            padding: 10px 14px;
            border-radius: 10px;
        }
    }

    /* --- Table card: better mobile padding --- */
    @media (max-width: 768px) {
        .table-card {
            border-radius: 14px;
        }

        .table-header {
            padding: 14px 16px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .table-title {
            font-size: 14px;
            gap: 10px;
        }

        .table-title i {
            width: 34px;
            height: 34px;
            font-size: 14px;
        }

        .table-footer {
            padding: 14px 16px;
        }

        .record-count {
            font-size: 12px;
        }

        /* Make table cards scroll horizontally without breaking layout */
        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        .modern-table {
            font-size: 13px;
        }

        .modern-table th,
        .modern-table td {
            padding: 12px 14px;
            white-space: nowrap;
        }

        .modern-table th {
            font-size: 10px;
        }

        .user-avatar-table {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }

        .user-name {
            font-size: 13px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            font-size: 13px;
        }
    }

    /* --- Card-based data grid (used by Students page) --- */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .cards-grid {
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 12px;
        }
    }

    @media (max-width: 576px) {
        .cards-grid {
            grid-template-columns: 1fr;
        }
    }

    .data-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.05);
        border: 1px solid #f0f4f8;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .data-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(24,43,72,0.1);
    }

    .data-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(42,69,112,0.04));
        border-bottom: 1px solid #f0f4f8;
    }

    .data-card-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #182B48, #2A4570);
        color: #d4a030;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .data-card-info {
        flex: 1;
        min-width: 0;
    }

    .data-card-title {
        color: #182B48;
        font-weight: 700;
        font-size: 14px;
        margin: 0 0 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .data-card-subtitle {
        color: #7f8c8d;
        font-size: 11px;
    }

    .data-card-actions {
        display: flex;
        gap: 6px;
    }

    .data-card-body {
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .data-card-footer {
        display: flex;
        gap: 8px;
        padding: 12px 16px;
        background: #fafbfc;
        border-top: 1px solid #f0f4f8;
    }

    .data-card-footer .btn {
        flex: 1;
        font-size: 12px;
        padding: 8px 10px;
        border-radius: 8px;
    }

    @media (max-width: 768px) {
        .data-card {
            border-radius: 14px;
        }

        .data-card-header {
            padding: 12px 14px;
            gap: 10px;
        }

        .data-card-avatar {
            width: 40px;
            height: 40px;
            font-size: 16px;
            border-radius: 10px;
        }

        .data-card-body {
            padding: 12px 14px;
        }

        .data-card-footer {
            padding: 10px 14px;
        }
    }

    /* --- Empty state: cleaner & centered on mobile --- */
    @media (max-width: 768px) {
        .empty-state {
            padding: 36px 18px;
        }

        .empty-state .empty-icon {
            width: 72px;
            height: 72px;
            font-size: 28px;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            font-size: 16px;
        }

        .empty-state p {
            font-size: 13px;
            margin-bottom: 18px;
        }
    }

    /* --- Pagination: compact on mobile --- */
    @media (max-width: 768px) {
        .pagination-wrapper {
            margin-top: 18px;
        }

        .pagination-wrapper .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-wrapper .page-link {
            padding: 7px 11px;
            font-size: 13px;
            border-radius: 7px;
        }

        .pagination-wrapper .page-item:not(.active):not(.disabled):nth-child(n+5):nth-last-child(n+3) {
            display: none;
        }
    }

    /* --- Stat cards: tighter on mobile --- */
    @media (max-width: 768px) {
        .stat-card {
            padding: 14px 16px;
            border-radius: 14px;
        }

        .stat-card .number {
            font-size: 1.4rem;
        }

        .stat-card .label {
            font-size: 11px;
            margin-top: 4px;
        }

        .stat-card .icon {
            font-size: 1.6rem;
        }
    }

    /* --- Breadcrumb: hide on small screens --- */
    @media (max-width: 576px) {
        .breadcrumb {
            font-size: 12px;
            margin-bottom: 0.5rem;
        }
    }

    /* --- Modals: full-screen on small phones --- */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0;
            min-height: 100vh;
            align-items: flex-end;
        }

        .modal-content {
            border-radius: 16px 16px 0 0;
            max-height: 92vh;
        }

        .modal-header,
        .modal-footer {
            padding: 14px 16px;
        }

        .modal-body {
            padding: 16px;
            max-height: calc(92vh - 130px);
            overflow-y: auto;
        }
    }

    /* --- Page content padding --- */
    @media (max-width: 768px) {
        main.page-content {
            padding: 14px;
        }
    }

    /* --- Generic h1-h4 sizing for mobile --- */
    @media (max-width: 768px) {
        h1 { font-size: 1.5rem; }
        h2 { font-size: 1.3rem; }
        h3 { font-size: 1.2rem; }
        h4 { font-size: 1.1rem; }
        h5 { font-size: 1rem; }
        h6 { font-size: 0.9rem; }
    }

    /* --- Prevent iOS rubber-band scroll on chat container --- */
    .chat-container { overscroll-behavior: contain; }

    /* --- Smooth scrolling & touch behavior --- */
    html { -webkit-text-size-adjust: 100%; }
    body { -webkit-tap-highlight-color: rgba(212, 160, 48, 0.15); }
    </style>
</head>
<body>

    <!-- PWA Install Button -->
    <button id="pwa-install-btn" class="pwa-install-btn" style="display: none;">
        <i class="fas fa-download"></i>
        <span>تثبيت التطبيق</span>
    </button>

    <!-- Sidebar Backdrop (Mobile) -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeMobileSidebar()"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <!-- Top Gold Border -->
        <div class="sidebar-top-decoration"></div>

        <!-- Corner Decorations -->
        <div class="sidebar-corner-decoration top-right">
            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 0 L100 0 L100 20 L20 20 L20 100 L0 100 Z" fill="#d4a030"/>
                <path d="M25 0 L50 0 L50 10 L35 10 L35 25 L0 25 L0 0 Z" fill="#d4a030" opacity="0.5"/>
                <circle cx="15" cy="15" r="5" fill="#d4a030"/>
            </svg>
        </div>
        <div class="sidebar-corner-decoration bottom-left">
            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 0 L100 0 L100 20 L20 20 L20 100 L0 100 Z" fill="#d4a030"/>
                <path d="M25 0 L50 0 L50 10 L35 10 L35 25 L0 25 L0 0 Z" fill="#d4a030" opacity="0.5"/>
                <circle cx="15" cy="15" r="5" fill="#d4a030"/>
            </svg>
        </div>

        <!-- Crescent Moon -->
        <div class="sidebar-crescent"><i class="fas fa-moon"></i></div>

        <!-- Stars -->
        <div class="sidebar-star sidebar-star-1"><i class="fas fa-star"></i></div>
        <div class="sidebar-star sidebar-star-2"><i class="fas fa-star"></i></div>
        <div class="sidebar-star sidebar-star-3"><i class="fas fa-star"></i></div>
        <div class="sidebar-star sidebar-star-4"><i class="fas fa-star"></i></div>

        <!-- Frame Border -->
        <div class="sidebar-frame"></div>

        <button class="sidebar-close-btn" onclick="closeMobileSidebar()" title="إغلاق">
            <i class="fas fa-times"></i>
        </button>

        <!-- Bismillah Calligraphy -->
        <div class="sidebar-bismillah">
            <span>بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</span>
        </div>

        <!-- Islamic Divider -->
        <div class="islamic-divider">
            <div class="islamic-divider-icon">
                <i class="fas fa-book-quran"></i>
            </div>
        </div>

        <div class="sidebar-logo">
            <img src="{{ asset('images/muddaker-logo-main-nobg.png') }}" alt="Logo">
            <h6>مدكر</h6>
            <small>نظام التسميع الإلكتروني</small>
        </div>

        <div class="sidebar-nav">
            @yield('sidebar-menu')
        </div>

        <!-- Bottom Mosque Dome Decoration -->
        <div class="sidebar-dome"></div>

        <div class="sidebar-footer">
            <div class="islamic-divider" style="padding: 10px 20px;">
                <div class="islamic-divider-icon" style="width: 30px; height: 30px;">
                    <i class="fas fa-mosque"></i>
                </div>
            </div>
            <div class="sidebar-footer-content">
                <i class="fas fa-shield-alt"></i>
                <span>النسخة 1.0.0 | {{ date('Y') }}</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Header -->
        <header class="top-header">
            <!-- Mobile menu toggle -->
            <button class="btn btn-sm d-lg-none" id="mobileMenuBtn" onclick="toggleMobileSidebar()" title="القائمة" aria-label="القائمة" style="background:#fff;color:#182B48;border:none;width:44px;height:44px;border-radius:10px;box-shadow:0 2px 8px rgba(24,43,72,0.15);">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Desktop sidebar collapse toggle -->
            <button class="btn btn-sm d-none d-lg-flex" id="sidebarToggle" onclick="toggleSidebar()" title="إظهار/إخفاء القائمة الجانبية" aria-label="إظهار/إخفاء القائمة الجانبية">
                <i class="fas fa-bars"></i>
            </button>

            <h1 class="page-title">@yield('page-title', 'الرئيسية')</h1>

            <div class="header-actions">
                <!-- Chat -->
                <a href="{{ route('chat.index') }}" class="notification-bell" id="chatIcon" title="المحادثات">
                    <i class="fas fa-comments"></i>
                    <span class="notification-badge" id="chatBadge" style="display: none;">0</span>
                </a>

                <!-- Enable Notifications Button -->
                <button class="notification-permission-btn" id="headerEnableNotifBtn" style="display: none;">
                    <i class="fas fa-bell"></i>
                    <span>تفعيل الإشعارات</span>
                </button>

                <!-- Notifications -->
                <div class="dropdown">
                    <div class="notification-bell" data-bs-toggle="dropdown" id="notifBell">
                        <i class="fas fa-bell"></i>
                        @php $notifCount = auth()->user()->unreadNotificationsCount(); @endphp
                        @if($notifCount > 0)
                            <span class="notification-badge" id="notifBadge">{{ $notifCount }}</span>
                        @endif
                    </div>
                    <div class="dropdown-menu dropdown-menu-start p-0 notif-dropdown" style="width: 340px; max-height: 480px; overflow-y: auto;">
                        <div class="notif-header">
                            <div class="notif-header-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="notif-header-content">
                                <h6>الإشعارات</h6>
                                <span>لديك {{ $notifCount > 0 ? $notifCount : '٠' }} إشعار</span>
                            </div>
                            @if($notifCount > 0)
                            <a href="{{ route('notifications.markAllAsRead') }}" class="notif-mark-all" onclick="event.stopPropagation(); this.click();">
                                <i class="fas fa-check-double"></i>
                                قراءة الكل
                            </a>
                            @endif
                        </div>
                        <div class="notif-list">
                            @forelse(auth()->user()->notifications()->latest()->take(10)->get() as $notif)
                                <a href="{{ $notif->link ?? ($notif->is_read ? '#' : route('notifications.markAsRead', $notif->id)) }}"
                                   class="notif-item {{ !$notif->is_read ? 'unread' : '' }} mark-as-read-header"
                                   data-notif-id="{{ $notif->id }}"
                                   data-link="{{ $notif->link ?? '' }}">
                                    <div class="notif-icon {{ !$notif->is_read ? 'new' : '' }}">
                                        @if(str_contains($notif->type, 'warning'))
                                            <i class="fas fa-exclamation-triangle"></i>
                                        @elseif(str_contains($notif->type, 'session'))
                                            <i class="fas fa-quran"></i>
                                        @elseif(str_contains($notif->type, 'billing'))
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        @elseif($notif->type === 'message')
                                            <i class="fas fa-comment"></i>
                                        @else
                                            <i class="fas fa-bell"></i>
                                        @endif
                                    </div>
                                    <div class="notif-content">
                                        <div class="notif-title">{{ $notif->title }}</div>
                                        <div class="notif-message">{{ Str::limit($notif->message, 50) }}</div>
                                        <div class="notif-time">
                                            <i class="fas fa-clock"></i>
                                            {{ $notif->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    @if(!$notif->is_read)
                                        <div class="notif-dot"></div>
                                    @endif
                                </a>
                            @empty
                                <div class="notif-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>لا توجد إشعارات</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="notif-footer">
                            <a href="{{ route('notifications.index') }}" class="notif-footer-link">
                                <i class="fas fa-list"></i>
                                عرض جميع الإشعارات
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="user-avatar-btn" data-bs-toggle="dropdown" aria-label="الملف الشخصي">
                        <div class="user-avatar-wrapper">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="user-avatar-img">
                            <div class="user-avatar-status"></div>
                        </div>
                        <div class="user-info">
                            <div class="name">{{ auth()->user()->name }}</div>
                            <div class="role">
                                <i class="fas fa-circle"></i>
                                @yield('role-label', auth()->user()->role)
                            </div>
                        </div>
                        <i class="fas fa-chevron-down user-chevron"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-start user-dropdown">
                        <li class="user-dropdown-header">
                            <div class="user-dropdown-avatar">
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
                            </div>
                            <div class="user-dropdown-info">
                                <h6>{{ auth()->user()->name }}</h6>
                                <span>{{ auth()->user()->email }}</span>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route(auth()->user()->role . '.profile.index') }}">
                                <i class="fas fa-user"></i>
                                <span>الملف الشخصي</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('notifications.index') }}">
                                <i class="fas fa-bell"></i>
                                <span>الإشعارات</span>
                                @if($notifCount > 0)
                                    <span class="badge-count">{{ $notifCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>تسجيل الخروج</span>
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

            // Update nav link titles for collapsed state
            updateNavTooltips();
        }

        // Add tooltips to collapsed sidebar nav items
        function updateNavTooltips() {
            const sidebar = document.getElementById('sidebar');
            const navLinks = sidebar.querySelectorAll('.nav-link');

            navLinks.forEach(link => {
                if (sidebar.classList.contains('collapsed')) {
                    // Get the text content from the link (excluding the icon)
                    const text = link.cloneNode(true);
                    text.querySelector('i')?.remove();
                    const title = text.textContent.trim();
                    link.setAttribute('data-tooltip', title);
                } else {
                    link.removeAttribute('data-tooltip');
                }
            });
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
            if (window.innerWidth >= 992) {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.querySelector('.main-content');
                const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';

                if (isCollapsed) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('sidebar-collapsed');
                    updateNavTooltips();
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');

            if (window.innerWidth < 992) {
                // Mobile/tablet: remove collapse classes, ensure proper state
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

    <!-- PWA Push + Service Worker -->
    <script src="/js/push-init.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateBadges() {
            fetch('/chat/unread/count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('chatBadge');
                    if (!badge) return;
                    if (window.location.pathname === '/chat') {
                        badge.textContent = '0';
                        badge.style.display = 'none';
                    } else if (data.unread > 0) {
                        badge.textContent = data.unread;
                        badge.style.display = 'flex';
                    } else {
                        badge.textContent = '0';
                        badge.style.display = 'none';
                    }
                });

            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const notifBell = document.getElementById('notifBell');
                    let badge = document.getElementById('notifBadge');
                    if (data.count > 0) {
                        if (!badge && notifBell) {
                            badge = document.createElement('span');
                            badge.className = 'notification-badge';
                            badge.id = 'notifBadge';
                            notifBell.appendChild(badge);
                        }
                        if (badge) {
                            badge.textContent = data.count;
                            badge.style.display = 'flex';
                        }
                    } else if (badge) {
                        badge.style.display = 'none';
                    }
                    const dropdownBadge = document.querySelector('.badge-count');
                    if (data.count > 0) {
                        if (dropdownBadge) {
                            dropdownBadge.textContent = data.count;
                        }
                    } else if (dropdownBadge) {
                        dropdownBadge.remove();
                    }
                });
        }

        updateBadges();

        setInterval(updateBadges, 3000);

        // Show/hide the "Enable Notifications" button based on the current
        // browser permission state. Hide it once the user has granted
        // permission (or if the browser doesn't support it at all).
        function updateHeaderNotifBtn() {
            const btn = document.getElementById('headerEnableNotifBtn');
            if (!btn || !window.PushNotifications) return;
            const status = window.PushNotifications.getStatus();
            if (status.state === 'granted' || status.state === 'unsupported') {
                btn.style.display = 'none';
            } else {
                btn.style.display = '';
            }
        }
        // Wire up the button (single delegated handler instead of inline
        // onclick so we don't depend on global scope).
        const headerNotifBtn = document.getElementById('headerEnableNotifBtn');
        if (headerNotifBtn) {
            headerNotifBtn.addEventListener('click', function () {
                window.location.href = "{{ route('notifications.index') }}";
            });
        }
        updateHeaderNotifBtn();

        // iOS Safari browser (not PWA) — show a small banner telling the
        // user to add the app to the home screen for notifications to work.
        if (window.PushNotifications && window.PushNotifications.isIOSBrowser && window.PushNotifications.isIOSBrowser()) {
            const banner = document.createElement('div');
            banner.style.cssText = 'position:fixed;bottom:0;left:0;right:0;background:#182B48;color:#fff;padding:10px 16px;text-align:center;z-index:99999;font-size:13px;font-family:Tajawal,sans-serif;';
            banner.innerHTML = '<i class="fas fa-info-circle"></i> ' +
                'للحصول على الإشعارات على iPhone، أضف الموقع إلى الشاشة الرئيسية: ' +
                '<strong>مشاركة</strong> ← <strong>إضافة إلى الشاشة الرئيسية</strong>.';
            document.body.appendChild(banner);
        }

        @if(config('broadcasting.default') === 'pusher')
        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
        });

        const channel = pusher.subscribe('chat.{{ auth()->id() }}');
        channel.bind('new-message', function(data) {
            if (window.location.pathname === '/chat') return;
            const badge = document.getElementById('chatBadge');
            if (badge) {
                let count = parseInt(badge.textContent) || 0;
                badge.textContent = count + 1;
                badge.style.display = 'flex';
            }
        });
        @endif
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.mark-as-read-header').forEach(function(item) {
            item.addEventListener('click', function(e) {
                const notifId = this.dataset.notifId;
                const link = this.dataset.link;
                const isUnread = this.classList.contains('unread');

                if (isUnread && notifId) {
                    e.preventDefault();
                    fetch('/notifications/' + notifId + '/read', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.classList.remove('unread');
                        const dot = this.querySelector('.notif-dot');
                        if (dot) dot.remove();
                        const badge = document.getElementById('notifBadge');
                        if (badge) {
                            const currentCount = parseInt(badge.textContent) || 0;
                            if (currentCount > 0) {
                                badge.textContent = currentCount - 1;
                                if (currentCount - 1 <= 0) badge.remove();
                            }
                        }
                        if (data.success && data.link) {
                            window.location.href = data.link;
                        } else if (link) {
                            window.location.href = link;
                        } else {
                            window.location.reload();
                        }
                    })
                    .catch(() => {
                        if (link) window.location.href = link;
                        else window.location.reload();
                    });
                } else if (link) {
                    window.location.href = link;
                }
            });
        });
    });
    </script>

    @stack('scripts')
</body>
</html>