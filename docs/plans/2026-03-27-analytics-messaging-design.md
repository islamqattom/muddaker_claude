# Feature Enhancement Design: Analytics + In-Portral Messaging

**Date:** 2026-03-27
**Status:** Approved
**Tracks:** Two parallel tracks

---

## Context

The Madkar Al-Quranic system has a solid foundation in recitation tracking, attendance, and an automated warning system (recently added). The institution now wants two capability enhancements:

1. **Better visibility** — Student progress visualizations and supervisor performance tracking to move from reactive management to proactive intervention.
2. **Better communication** — In-portal messaging with push notifications so parents and supervisors stay informed without relying on third-party services (WhatsApp deferred).

WhatsApp integration deferred until WhatsApp Business API access is available.

---

## Track 1: Enhanced Monitoring & Analytics

### 1A: Student Progress Dashboard

**Goal:** Give supervisors and parents a clear visual view of each student's memorization journey.

**Pages affected:**
- `resources/views/supervisor/students/show.blade.php` — student detail page
- `resources/views/parent/children/show.blade.php` — parent child view

**New components:**
- **Memorization velocity chart** — Line chart showing pages memorized per week over last 12 weeks. Built with Chart.js. Data computed from `recitation_sessions` filtered by type = 'new_memorization' and date range.
- **Page difficulty heatmap** — Grid/matrix of Quran pages (1-604) color-coded by error rate. Green = low errors, Red = high errors. Derived from `recitation_pages` error data per page.
- **Current level progress bar** — Shows pages completed vs. total pages in current level. Reads from `students.current_part_page` and `levels.page_count`.
- **Recent session timeline** — Last 10 sessions as a compact timeline with score badges.

**Data flow:**
```
recitation_sessions + recitation_pages
  → AnalyticsService (new)
    → StudentProgressData (DTO)
      → Chart.js via Blade component
```

**New files:**
- `app/Services/AnalyticsService.php` — Computes all analytics data
- `app/DTO/StudentProgressData.php` — Struct for progress metrics
- `resources/views/components/student-progress-chart.blade.php` — Chart.js component
- `resources/views/components/page-difficulty-heatmap.blade.php` — Heatmap component
- `resources/views/components/recent-session-timeline.blade.php` — Session timeline

---

### 1B: Supervisor Performance Reports

**Goal:** Help admins understand how supervisors are performing — evaluation frequency, quality, student outcomes.

**Pages affected:**
- `resources/views/admin/dashboard.blade.php` — Admin dashboard gets new metrics row
- `resources/views/admin/supervisors/index.blade.php` — Supervisor list gets performance column

**New metrics:**
- **Students per supervisor** — Count of active students assigned
- **Evaluations this month** — Total recitation sessions logged in current month
- **Average session score** — Mean score across all sessions this month
- **Pending warning drafts** — Warning drafts awaiting action per supervisor
- **Response time** — Average days from warning draft creation to approval/dismissal

**Data flow:**
```
WarningDraft + RecitationSession + Student
  → AnalyticsService::supervisorMetrics(supervisorId)
    → SupervisorPerformanceData (DTO)
      → Admin dashboard cards
```

**Admin dashboard additions:**
- Summary row: Total students, total supervisors, avg evaluation score this week
- Supervisor performance table with sortable columns
- Sparkline charts for monthly evaluation trends

**New files:**
- `app/DTO/SupervisorPerformanceData.php`
- `resources/views/components/supervisor-performance-card.blade.php`

---

## Track 2: In-Portral Messaging + Push Notifications

### 2A: Notification System Architecture

**Goal:** Replace one-off notifications with a unified notification system that supports in-portal display + browser push notifications.

**Existing system:** Laravel's `notifications` table with `WarningDraftCreated` notification. Expand to a general `UserNotification` model.

**New components:**

**Database:**
- `user_notifications` table — id, type, notifiable_type, notifiable_id, data (JSON), read_at, created_at
- `push_subscriptions` table — id, user_id, endpoint, keys (p256dh), auth, created_at, updated_at

**Models:**
- `app/Models/UserNotification.php` — Polymorphic notification model
- `app/Models/PushSubscription.php` — Web push subscription storage

**Service:**
- `app/Services/PushNotificationService.php` — Sends push via Laravel Web Push

**Events:**
- `App\Events\UserNotificationCreated` — Fired when any notification is created

**Channels to support:**
1. **Database** — Always on, stored in `user_notifications`
2. **Broadcast** (optional later) — Laravel Echo + Pusher for real-time
3. **Web Push** — Via `laravel-web-push` package

---

### 2B: In-Portral Notification Center

**Goal:** A bell-icon notification dropdown + a full `/notifications` page.

**New pages:**
- `resources/views/layouts/partials/notification-dropdown.blade.php` — Dropdown component in navbar
- `resources/views/notifications/index.blade.php` — Full notification history page
- `resources/views/notifications/show.blade.php` — Single notification detail

**Features:**
- Bell icon in top navbar with unread count badge
- Dropdown shows last 5 unread
- Full page shows all notifications with filter (all/unread/warnings/sessions)
- Click to mark as read (inline or redirect to related entity)
- "Mark all as read" action
- Pagination (20 per page)

**Integration points — what triggers notifications:**
| Event | Recipients | Message |
|-------|-----------|---------|
| WarningDraft created | Supervisor | "New warning draft for {student_name} — review needed" |
| Warning approved | Parent | "Warning issued for {student_name}: {warning_type}" |
| New recitation session | Parent | "{student_name} completed {type} — Score: {score}" |
| Attendance marked (absent/late) | Parent | "{student_name} was {status} on {date}" |
| Mafsil milestone | Parent + Supervisor | "Congratulations! {student_name} completed {parts} parts" |

---

### 2C: PWA Configuration

**Goal:** Parent portal works as a Progressive Web App with push notifications.

**New files:**
- `public/manifest.json` — PWA manifest for parent portal
- `public/sw.js` — Service worker for push + offline caching
- `public/js/pwa-init.js` — Registers service worker, requests push permission

**PWA features:**
- "Add to Home Screen" prompt on mobile
- Background push notifications even when browser closed
- Offline-capable shell (cached assets)

**Note:** PWA for parent portal only. Admin/supervisor portals can be added later.

---

### 2D: Package Dependencies

**New Composer packages:**
- `beyondcode/laravel-web-push:^8.0` — Web push notifications

**New NPM packages:**
- `chart.js@^4.4` — For progress charts
- `chartjs-adapter-date-fns` — Date handling for time series

**Existing packages used:**
- `spatie/laravel-permission` — Already in use for roles
- Bootstrap 5 RTL — Already in use

---

## Implementation Order

### Track 1 (Analytics):
1. Create `AnalyticsService` + DTOs
2. Add student progress chart to student detail page
3. Add page difficulty heatmap component
4. Add recent session timeline
5. Add supervisor performance metrics to admin dashboard

### Track 2 (Messaging):
1. Install `beyondcode/laravel-web-push`
2. Create `PushSubscription` model + migration
3. Create `UserNotification` model + migration
4. Create `PushNotificationService`
5. Build notification dropdown Blade component
6. Build full `/notifications` page
7. Create notification event class
8. Wire up notification triggers (WarningDraft, RecitationSession, Attendance)
9. Add PWA manifest + service worker for parent portal
10. Install Chart.js + add analytics charts

---

## Critical Files to Modify

| File | Change |
|------|--------|
| `app/Providers/AppServiceProvider.php` | Register PushNotificationService |
| `app/Models/User.php` | Add HasPushSubscriptions trait, notification relationship |
| `app/Console/Kernel.php` | Add `web-push:generate-vapid-keys` command reference |
| `resources/views/layouts/app.blade.php` | Add notification dropdown to navbar |
| `resources/views/supervisor/students/show.blade.php` | Add progress charts |
| `resources/views/parent/children/show.blade.php` | Add progress charts for parent |
| `resources/views/admin/dashboard.blade.php` | Add supervisor performance table |
| `config/app.php` | Add web-push config |
| `routes/web.php` | Add `/notifications` route |

---

## Reusable Utilities Found in Codebase

- `app/Services/WarningRuleService.php` — Good reference for how services are structured
- `app/Models/RecitationSession.php` — Use existing query scopes for date filtering
- `app/Models/Student.php` — Existing relationships to build upon
- `resources/views/components/` — Existing Blade component pattern to follow

---

## Verification Plan

1. **Analytics:**
   - As supervisor, open a student detail page — verify charts render with real data
   - As admin, open dashboard — verify supervisor performance table shows correct numbers
   - Check Chart.js renders without console errors

2. **Notifications:**
   - Trigger a warning draft — verify notification appears in dropdown
   - Visit `/notifications` — verify all notifications listed with correct data
   - Click notification — verify it marks as read and links to correct entity
   - Test "Mark all as read" action

3. **PWA:**
   - Visit parent portal on mobile Chrome — verify "Add to Home Screen" prompt
   - Grant push permission — verify push subscription stored in DB
   - Send test push via `php artisan web-push:send` — verify notification received

4. **No regressions:**
   - Run existing Playwright tests to ensure recitation, attendance, warnings still work
