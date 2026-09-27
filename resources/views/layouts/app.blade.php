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

    <!-- App-wide styles extracted to public/css/app.css -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">

    @stack('styles')

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
                <!-- Enable Notifications Button -->
                <button class="notification-permission-btn" id="headerEnableNotifBtn" style="display: none;">
                    <i class="fas fa-bell"></i>
                    <span>تفعيل الإشعارات</span>
                </button>

                <!-- Notifications -->
                <div class="dropdown">
                    <div class="notification-bell" data-bs-toggle="dropdown" id="notifBell">
                        <i class="fas fa-bell"></i>
                        @php $notifCount = auth()->user()->unreadNonChatNotificationsCount(); @endphp
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
                            @forelse(auth()->user()->notifications()->nonChat()->latest()->take(10)->get() as $notif)
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    <script>
    // ===== Global sortable listing tables =====
    // Auto-enhances every data table (a <table> that has a <thead> and a
    // <tbody> with at least one row body) with click-to-sort headers.
    // Columns are excluded from sorting when their header text is empty,
    // a single "#", or an action/button column.
    (function () {
        var EXCLUDE_HEADERS = ['#', 'الإجراءات', 'إجراء', 'إجراءات', 'عرض', 'العمليات', 'خيارات'];

        function isSortableTable(table) {
            if (table.dataset.noSort === '1' || table.classList.contains('rpt-table')) return false;
            if (table.dataset.sortableBound === '1') return false;
            var thead = table.tHead;
            var tbody = table.tBodies[0];
            if (!thead || !tbody) return false;
            if (thead.rows.length !== 1) return false;
            var headerCount = thead.rows[0].cells.length;
            // Require at least one data row whose cell count matches the
            // header (empty-state rows use colspan and are ignored).
            var hasDataRow = Array.prototype.some.call(tbody.rows, function (r) {
                return r.cells.length === headerCount;
            });
            return hasDataRow;
        }

        function textOf(el) {
            if (!el) return '';
            var t = (el.innerText !== undefined ? el.innerText : el.textContent) || '';
            return t.trim();
        }

        function cellValue(row, idx) {
            var cell = row.cells[idx];
            if (!cell) return '';
            var raw = cell.getAttribute('data-sort-value');
            if (raw !== null) return raw;
            return textOf(cell);
        }

        function toNumber(v) {
            // Strip Arabic-Indic digits, percentages, commas, and any
            // non-numeric decorations before parsing (e.g. "٩٠٪", "1 - 3").
            var ascii = String(v).replace(/[٠-٩]/g, function (d) {
                return String.fromCharCode(d.charCodeAt(0) - 0x0660 + 48);
            }).replace(/[۰-۹]/g, function (d) {
                return String.fromCharCode(d.charCodeAt(0) - 0x06F0 + 48);
            }).replace(/[٪%]/g, '').replace(/,/g, '').trim();
            var m = ascii.match(/-?\d+(\.\d+)?/);
            return m ? parseFloat(m[0]) : NaN;
        }

        function enhance(table) {
            if (!isSortableTable(table)) return;
            var thead = table.tHead;
            var tbody = table.tBodies[0];
            var ths = Array.prototype.slice.call(thead.rows[0].cells);

            table.dataset.sortableBound = '1';
            table.setAttribute('data-sortable', '');

            // Decide which columns are sortable.
            var sortableIdx = [];
            ths.forEach(function (th, idx) {
                var text = textOf(th);
                var excluded = EXCLUDE_HEADERS.indexOf(text) !== -1 || text === '';
                if (excluded) {
                    th.classList.add('no-sort');
                    return;
                }
                sortableIdx.push(idx);
                th.setAttribute('data-sort-handle', '');
                th.setAttribute('role', 'button');
                th.setAttribute('tabindex', '0');
                th.setAttribute('aria-label', 'ترتيب حسب ' + text);
            });

            function compare(idx, dir) {
                var rows = Array.prototype.slice.call(tbody.rows);
                var numericCol = rows.every(function (r) {
                    var v = cellValue(r, idx);
                    return v === '' || !isNaN(toNumber(v));
                }) && rows.some(function (r) { return cellValue(r, idx) !== ''; });

                rows.sort(function (a, b) {
                    var av = cellValue(a, idx);
                    var bv = cellValue(b, idx);
                    if (numericCol) {
                        var an = toNumber(av), bn = toNumber(bv);
                        if (isNaN(an)) an = -Infinity;
                        if (isNaN(bn)) bn = -Infinity;
                        return an - bn;
                    }
                    return String(av).localeCompare(String(bv), 'ar', { numeric: true, sensitivity: 'base' });
                });
                if (dir === 'desc') rows.reverse();
                rows.forEach(function (r) { tbody.appendChild(r); });
            }

            function activate(th, idx) {
                var dir = th.classList.contains('sorted-asc') ? 'desc' : 'asc';
                ths.forEach(function (other) {
                    other.classList.remove('sorted-asc', 'sorted-desc');
                    other.removeAttribute('aria-sort');
                });
                th.classList.add(dir === 'asc' ? 'sorted-asc' : 'sorted-desc');
                th.setAttribute('aria-sort', dir === 'asc' ? 'ascending' : 'descending');
                compare(idx, dir);
            }

            sortableIdx.forEach(function (idx) {
                var th = ths[idx];
                th.addEventListener('click', function () { activate(th, idx); });
                th.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activate(th, idx); }
                });
            });
        }

        function init() {
            // Enhance every listing table inside the page content, regardless
            // of its CSS class. Non-listing tables (multi-row headers, JS
            // tables, PDF/exports, the report tables handled separately) are
            // filtered out by isSortableTable().
            document.querySelectorAll('main.page-content table').forEach(enhance);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
        window.SortableTables = { init: init };
    })();
    </script>
</body>
</html>