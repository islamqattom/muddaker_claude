@extends('layouts.app')
@section('title', __('إعدادات الإشعارات'))
@section('page-title', __('إعدادات الإشعارات'))

@php
    $role = auth()->user()->role;
    $roleSidebar = $role === 'admin' ? 'admin.partials.sidebar' : ($role === 'supervisor' ? 'supervisor.partials.sidebar' : ($role === 'student' ? 'student.partials.sidebar' : 'parent.partials.sidebar'));
@endphp

@section('sidebar-menu')
    @include($roleSidebar)
@endsection

@section('content')
<div class="page-header mb-4">
    <div class="header-content">
        <div class="header-icon" style="background: linear-gradient(135deg, #182B48, #2A4570);">
            <i class="fas fa-bell"></i>
        </div>
        <div class="header-text">
            <h4>إعدادات الإشعارات</h4>
            <p>تحكم في كيفية وصول الإشعارات إليك</p>
        </div>
    </div>
</div>

{{-- Status card --}}
<div class="card mb-4" id="pushStatusCard" style="border: none; box-shadow: 0 4px 20px rgba(24, 43, 72, 0.08); border-radius: 16px;">
    <div class="card-body" style="padding: 24px;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 class="mb-1" style="color: #182B48; font-weight: 700;">
                    <i class="fas fa-mobile-alt ms-2"></i>
                    حالة الإشعارات
                </h5>
                <p class="mb-0" id="pushStatusMessage" style="color: #7f8c8d;">
                    @if($hasActivePush)
                        <span class="badge" style="background: linear-gradient(135deg, #27ae60, #2ecc71); padding: 6px 14px; border-radius: 20px;">
                            <i class="fas fa-check-circle"></i> مفعّلة
                        </span>
                        <span class="ms-2">يتم إرسال الإشعارات إلى {{ $devices->where('is_active', true)->count() }} جهاز</span>
                    @else
                        <span class="badge" style="background: linear-gradient(135deg, #e67e22, #d35400); padding: 6px 14px; border-radius: 20px;">
                            <i class="fas fa-exclamation-circle"></i> غير مفعّلة
                        </span>
                        <span class="ms-2">اضغط "تفعيل" للسماح للموقع بإرسال الإشعارات</span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn" id="enablePushBtn" style="background: linear-gradient(135deg, #27ae60, #2ecc71); color: #fff; border: none; padding: 10px 20px; border-radius: 10px;">
                    <i class="fas fa-bell"></i>
                    <span>تفعيل</span>
                </button>
                <button type="button" class="btn" id="disablePushBtn" style="background: linear-gradient(135deg, #95a5a6, #7f8c8d); color: #fff; border: none; padding: 10px 20px; border-radius: 10px;">
                    <i class="fas fa-bell-slash"></i>
                    <span>تعطيل</span>
                </button>
                <button type="button" class="btn" id="testPushBtn" style="background: linear-gradient(135deg, #182B48, #2A4570); color: #fff; border: none; padding: 10px 20px; border-radius: 10px;">
                    <i class="fas fa-paper-plane"></i>
                    <span>إرسال إشعار اختباري</span>
                </button>
            </div>
        </div>
        <div id="pushStatusAlert" class="mt-3" style="display: none;"></div>
    </div>
</div>

{{-- Subscribed devices --}}
@if($devices->count() > 0)
<div class="card mb-4" style="border: none; box-shadow: 0 4px 20px rgba(24, 43, 72, 0.08); border-radius: 16px;">
    <div class="card-body" style="padding: 24px;">
        <h5 class="mb-3" style="color: #182B48; font-weight: 700;">
            <i class="fas fa-devices ms-2"></i>
            الأجهزة المسجّلة
        </h5>
        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <thead>
                    <tr style="border-bottom: 2px solid #f0f4f8;">
                        <th style="color: #7f8c8d; font-weight: 600;">الجهاز</th>
                        <th style="color: #7f8c8d; font-weight: 600;">الحالة</th>
                        <th style="color: #7f8c8d; font-weight: 600;">آخر استخدام</th>
                        <th style="color: #7f8c8d; font-weight: 600;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devices as $device)
                    <tr style="border-bottom: 1px solid #f0f4f8;">
                        <td>
                            <i class="fas {{ $device->is_active ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' }}"></i>
                            <span class="ms-2">{{ $device->deviceLabel() }}</span>
                        </td>
                        <td>
                            @if($device->is_active && !$device->isExpired())
                                <span class="badge" style="background: #27ae60;">نشط</span>
                            @else
                                <span class="badge" style="background: #95a5a6;">غير نشط</span>
                            @endif
                        </td>
                        <td style="color: #7f8c8d;">{{ $device->last_used_at?->diffForHumans() ?? 'لم يستخدم بعد' }}</td>
                        <td>
                            <button type="button" class="btn btn-sm remove-device-btn" data-id="{{ $device->id }}" style="color: #c0392b;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Per-type preferences --}}
<div class="card" style="border: none; box-shadow: 0 4px 20px rgba(24, 43, 72, 0.08); border-radius: 16px;">
    <div class="card-body" style="padding: 24px;">
        <h5 class="mb-3" style="color: #182B48; font-weight: 700;">
            <i class="fas fa-sliders-h ms-2"></i>
            أنواع الإشعارات
        </h5>
        <p class="text-muted mb-4">اختر الإشعارات التي تريد استلامها. يمكنك إيقاف نوع معين دون تعطيل الإشعارات كلياً.</p>
        <form id="preferencesForm">
            @csrf
            @foreach($types as $typeKey => $typeInfo)
            @php
                $pref = $preferences->get($typeKey);
                $inApp = $pref ? (bool) $pref->enabled : true;
                $push = $pref ? (bool) $pref->push_enabled : true;
            @endphp
            <div class="pref-row d-flex align-items-center justify-content-between flex-wrap gap-2 py-3" style="border-bottom: 1px solid #f0f4f8;" data-type="{{ $typeKey }}">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, rgba(24, 43, 72, 0.08), rgba(42, 69, 112, 0.12)); display: flex; align-items: center; justify-content: center;">
                        <i class="fas {{ $typeInfo['icon'] }}" style="color: #182B48;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #182B48;">{{ $typeInfo['label'] }}</div>
                    </div>
                </div>
                <div class="d-flex gap-4 align-items-center">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input pref-in-app" type="checkbox" role="switch" id="inApp_{{ $typeKey }}" {{ $inApp ? 'checked' : '' }}>
                        <label class="form-check-label ms-2" for="inApp_{{ $typeKey }}" style="color: #7f8c8d;">داخل التطبيق</label>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input pref-push" type="checkbox" role="switch" id="push_{{ $typeKey }}" {{ $push ? 'checked' : '' }}>
                        <label class="form-check-label ms-2" for="push_{{ $typeKey }}" style="color: #7f8c8d;">Push</label>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn" style="background: linear-gradient(135deg, #182B48, #2A4570); color: #fff; border: none; padding: 10px 24px; border-radius: 10px;">
                    <i class="fas fa-save"></i>
                    حفظ التفضيلات
                </button>
                <span id="preferencesSavedHint" class="align-self-center" style="color: #27ae60; display: none;">
                    <i class="fas fa-check-circle"></i> تم الحفظ
                </span>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    const $ = (s) => document.querySelector(s);
    const $$ = (s) => document.querySelectorAll(s);

    function showAlert(message, type = 'info') {
        const el = $('#pushStatusAlert');
        el.className = 'alert alert-' + (type === 'error' ? 'danger' : type) + ' mt-3';
        el.style.borderRadius = '10px';
        el.textContent = message;
        el.style.display = 'block';
        setTimeout(() => { el.style.display = 'none'; }, 5000);
    }

    function refreshStatusBar() {
        // Reload the page to refresh the device list / status — simplest way
        // to keep the UI in sync after subscribe/unsubscribe.
        setTimeout(() => window.location.reload(), 800);
    }

    // ============= Enable / Disable =============

    $('#enablePushBtn')?.addEventListener('click', async function() {
        const result = await PushNotifications.enable();
        if (result === 'granted') {
            showAlert('تم تفعيل الإشعارات بنجاح ✅', 'success');
            refreshStatusBar();
        } else if (result === 'denied') {
            showAlert('تم رفض إذن الإشعارات. يمكنك تفعيلها من إعدادات المتصفح.', 'warning');
        } else if (result === 'ios-not-pwa') {
            showAlert('للحصول على الإشعارات على iPhone، أضف الموقع إلى الشاشة الرئيسية أولاً.', 'warning');
        } else if (result === 'unsupported') {
            showAlert('المتصفح الحالي لا يدعم الإشعارات.', 'error');
        } else {
            showAlert('حدث خطأ أثناء تفعيل الإشعارات.', 'error');
        }
    });

    $('#disablePushBtn')?.addEventListener('click', async function() {
        if (!confirm('هل تريد تعطيل الإشعارات على هذا الجهاز؟')) return;
        const ok = await PushNotifications.disable();
        if (ok) {
            showAlert('تم تعطيل الإشعارات على هذا الجهاز.', 'success');
            refreshStatusBar();
        } else {
            showAlert('فشل تعطيل الإشعارات.', 'error');
        }
    });

    // ============= Test =============

    $('#testPushBtn')?.addEventListener('click', async function() {
        const btn = this;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...';

        try {
            const response = await fetch('{{ route("notifications.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            const data = await response.json();
            if (data.success) {
                showAlert('تم إرسال الإشعار الاختباري. تحقق من قائمة الإشعارات على جهازك.', 'success');
            } else {
                showAlert(data.error || 'فشل إرسال الإشعار الاختباري.', 'error');
            }
        } catch (e) {
            showAlert('حدث خطأ في الاتصال.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });

    // ============= Remove device =============

    $$('.remove-device-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('هل تريد إزالة هذا الجهاز من قائمة الأجهزة المسجّلة؟')) return;
            const id = this.dataset.id;
            try {
                const response = await fetch('{{ route("push.unsubscribe") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ subscription_id: id }),
                });
                const data = await response.json();
                if (data.success) {
                    this.closest('tr').remove();
                    showAlert('تم إزالة الجهاز.', 'success');
                } else {
                    showAlert('فشل إزالة الجهاز.', 'error');
                }
            } catch (e) {
                showAlert('حدث خطأ في الاتصال.', 'error');
            }
        });
    });

    // ============= Preferences form =============

    $('#preferencesForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const rows = $$('.pref-row');
        const payload = { preferences: [] };
        rows.forEach(row => {
            const type = row.dataset.type;
            const inApp = row.querySelector('.pref-in-app').checked;
            const push = row.querySelector('.pref-push').checked;
            payload.preferences.push({ type, enabled: inApp, push_enabled: push });
        });

        const btn = this.querySelector('button[type="submit"]');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';

        try {
            const response = await fetch('{{ route("notifications.preferences.update") }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (data.success) {
                const hint = $('#preferencesSavedHint');
                hint.style.display = 'inline';
                setTimeout(() => { hint.style.display = 'none'; }, 2500);
            } else {
                showAlert('فشل حفظ التفضيلات.', 'error');
            }
        } catch (e) {
            showAlert('حدث خطأ في الاتصال.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });
})();
</script>
@endpush
