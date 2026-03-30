# Analytics + In-Portral Messaging Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Implement two parallel feature tracks: (1) Student progress charts + supervisor performance reports, (2) In-portal notification center with web push + PWA for parent portal.

**Architecture:** Track 1 adds Chart.js analytics layered onto existing Blade pages via a new AnalyticsService + DTOs. Track 2 builds a polymorphic notification system with `beyondcode/laravel-web-push`, a service worker for the parent portal PWA, and wires up notification triggers across recitation/attendance/warning events.

**Tech Stack:** Laravel 10, Chart.js 4, beyondcode/laravel-web-push 8, Bootstrap 5 RTL, PHP 8.1+

---

## Track 1: Analytics

### Task 1: Create AnalyticsService and DTOs

**Files:**
- Create: `app/Services/AnalyticsService.php`
- Create: `app/DTO/StudentProgressData.php`
- Create: `app/DTO/SupervisorPerformanceData.php`

**Step 1: Write failing tests**

```bash
# Create test file
touch tests/Unit/Services/AnalyticsServiceTest.php
```

```php
<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AnalyticsService;
use App\Models\Student;
use App\Models\User;

class AnalyticsServiceTest extends TestCase
{
    public function test_student_progress_data_returns_velocity_array(): void
    {
        $service = new AnalyticsService();
        // Requires a seeded student with recitation sessions
        $this->assertIsArray($service->getStudentProgressVelocity(1, 12));
    }

    public function test_student_progress_data_returns_heatmap_array(): void
    {
        $service = new AnalyticsService();
        $this->assertIsArray($service->getPageDifficultyHeatmap(1));
        $this->assertCount(604, $service->getPageDifficultyHeatmap(1));
    }

    public function test_supervisor_performance_returns_expected_keys(): void
    {
        $service = new AnalyticsService();
        $data = $service->getSupervisorPerformance(1);
        $this->assertArrayHasKey('students_count', $data);
        $this->assertArrayHasKey('evaluations_this_month', $data);
        $this->assertArrayHasKey('avg_score_this_month', $data);
        $this->assertArrayHasKey('pending_warning_drafts', $data);
    }
}
```

**Step 2: Run tests — verify they fail (no AnalyticsService exists yet)**

```
php artisan test tests/Unit/Services/AnalyticsServiceTest.php
Expected: FAIL — Class AnalyticsService not found
```

**Step 3: Write StudentProgressData DTO**

```php
<?php
// app/DTO/StudentProgressData.php
namespace App\DTO;

class StudentProgressData
{
    public function __construct(
        public readonly array $velocityWeeks,      // ['2026-01' => 5, '2026-02' => 7, ...]
        public readonly array $pageDifficulty,       // [1 => 0.1, 2 => 0.3, ...] error rate 0-1
        public readonly int $currentLevelPagesTotal,
        public readonly int $currentLevelPagesDone,
        public readonly float $completionPercent,
        public readonly array $recentSessions,       // last 10 sessions with score, date, type
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
```

**Step 4: Write SupervisorPerformanceData DTO**

```php
<?php
// app/DTO/SupervisorPerformanceData.php
namespace App\DTO;

class SupervisorPerformanceData
{
    public function __construct(
        public readonly int $studentsCount,
        public readonly int $evaluationsThisMonth,
        public readonly float $avgScoreThisMonth,
        public readonly int $pendingWarningDrafts,
        public readonly ?float $avgResponseTimeDays,
        public readonly array $monthlyTrend, // ['2026-01' => 45, '2026-02' => 52, ...]
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
```

**Step 5: Write AnalyticsService**

```php
<?php
// app/Services/AnalyticsService.php
namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\RecitationSession;
use App\Models\WarningDraft;
use App\Models\RecitationPage;
use App\DTO\StudentProgressData;
use App\DTO\SupervisorPerformanceData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getStudentProgressVelocity(int $studentId, int $weeksBack = 12): array
    {
        $student = Student::findOrFail($studentId);
        $startDate = Carbon::now()->subWeeks($weeksBack)->startOfWeek();

        $sessions = RecitationSession::where('student_id', $studentId)
            ->where('type', 'new_memorization')
            ->where('created_at', '>=', $startDate)
            ->with('recitationPages')
            ->get();

        $velocity = [];
        for ($i = 0; $i < $weeksBack; $i++) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            $monthKey = $weekStart->format('Y-W');

            $pagesThisWeek = $sessions
                ->where('created_at', '>=', $weekStart)
                ->where('created_at', '<=', $weekEnd)
                ->sum(function ($session) {
                    return $session->recitationPages->count();
                });

            $velocity[$monthKey] = $pagesThisWeek;
        }

        return $velocity;
    }

    public function getPageDifficultyHeatmap(int $studentId): array
    {
        $pages = RecitationPage::join('recitation_sessions', 'recitation_pages.recitation_session_id', '=', 'recitation_sessions.id')
            ->where('recitation_sessions.student_id', $studentId)
            ->select('recitation_pages.page_number')
            ->selectRaw('SUM(recitation_pages.errors) as total_errors, COUNT(*) as total_reviews')
            ->groupBy('recitation_pages.page_number')
            ->get()
            ->keyBy('page_number');

        $heatmap = [];
        for ($i = 1; $i <= 604; $i++) {
            $pageData = $pages->get($i);
            if ($pageData && $pageData->total_reviews > 0) {
                $heatmap[$i] = $pageData->total_errors / ($pageData->total_reviews * 2); // normalize 0-1
            } else {
                $heatmap[$i] = null; // no data
            }
        }

        return $heatmap;
    }

    public function getStudentProgressData(int $studentId): StudentProgressData
    {
        $student = Student::findOrFail($studentId);
        $level = $student->level;

        $velocity = $this->getStudentProgressVelocity($studentId);

        $heatmap = $this->getPageDifficultyHeatmap($studentId);

        $recentSessions = RecitationSession::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'type' => $s->type,
                'score' => $s->score,
                'date' => $s->created_at->toDateString(),
                'quran_part_id' => $s->quran_part_id,
            ])
            ->toArray();

        $pagesDone = $student->current_part_page ?? 0;
        $pagesTotal = $level ? $level->page_count : 604;
        $completionPercent = $pagesTotal > 0 ? round(($pagesDone / $pagesTotal) * 100, 1) : 0;

        return StudentProgressData::fromArray([
            'velocityWeeks' => $velocity,
            'pageDifficulty' => $heatmap,
            'currentLevelPagesTotal' => $pagesTotal,
            'currentLevelPagesDone' => $pagesDone,
            'completionPercent' => $completionPercent,
            'recentSessions' => $recentSessions,
        ]);
    }

    public function getSupervisorPerformance(int $supervisorId): SupervisorPerformanceData
    {
        $supervisor = User::findOrFail($supervisorId);
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        $studentsCount = Student::where('supervisor_id', $supervisorId)->count();

        $sessionsThisMonth = RecitationSession::where('supervisor_id', $supervisorId)
            ->where('created_at', '>=', $monthStart)
            ->get();

        $evaluationsThisMonth = $sessionsThisMonth->count();
        $avgScoreThisMonth = $sessionsThisMonth->avg('score') ?? 0;

        $pendingWarningDrafts = WarningDraft::where('supervisor_id', $supervisorId)
            ->whereNull('reviewed_at')
            ->count();

        $resolvedDrafts = WarningDraft::where('supervisor_id', $supervisorId)
            ->whereNotNull('reviewed_at')
            ->where('created_at', '>=', $monthStart->copy()->subMonths(3))
            ->get();

        $avgResponseTimeDays = $resolvedDrafts->count() > 0
            ? $resolvedDrafts->avg(fn($d) => Carbon::parse($d->reviewed_at)->diffInDays($d->created_at))
            : null;

        // Monthly trend — last 6 months
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = $now->copy()->subMonths($i)->startOfMonth();
            $mEnd = $mStart->copy()->endOfMonth();
            $monthKey = $mStart->format('Y-m');
            $count = RecitationSession::where('supervisor_id', $supervisorId)
                ->whereBetween('created_at', [$mStart, $mEnd])
                ->count();
            $monthlyTrend[$monthKey] = $count;
        }

        return SupervisorPerformanceData::fromArray([
            'studentsCount' => $studentsCount,
            'evaluationsThisMonth' => $evaluationsThisMonth,
            'avgScoreThisMonth' => round($avgScoreThisMonth, 1),
            'pendingWarningDrafts' => $pendingWarningDrafts,
            'avgResponseTimeDays' => $avgResponseTimeDays ? round($avgResponseTimeDays, 1) : null,
            'monthlyTrend' => $monthlyTrend,
        ]);
    }

    public function getAllSupervisorsPerformance(): array
    {
        $supervisors = User::role('supervisor')->get();
        return $supervisors->map(fn($s) => [
            'supervisor_id' => $s->id,
            'name' => $s->name,
            'data' => $this->getSupervisorPerformance($s->id),
        ])->toArray();
    }
}
```

**Step 6: Run tests — verify they pass**

```
php artisan test tests/Unit/Services/AnalyticsServiceTest.php
Expected: PASS (3 tests)
```

**Step 7: Commit**

```bash
git add app/Services/AnalyticsService.php app/DTO/StudentProgressData.php app/DTO/SupervisorPerformanceData.php tests/Unit/Services/AnalyticsServiceTest.php
git commit -m "feat(analytics): add AnalyticsService with StudentProgressData and SupervisorPerformanceData DTOs

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

### Task 2: Add Student Progress Charts to Student Detail Page

**Files:**
- Create: `resources/views/components/student-progress-chart.blade.php`
- Create: `resources/views/components/page-difficulty-heatmap.blade.php`
- Create: `resources/views/components/recent-session-timeline.blade.php`
- Modify: `resources/views/supervisor/students/show.blade.php`

**Step 1: Read existing student show page**

```
cat resources/views/supervisor/students/show.blade.php
```

**Step 2: Create student-progress-chart Blade component**

```blade
{{-- resources/views/components/student-progress-chart.blade.php --}}
@props(['studentId'])

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">@lang('Memorization Velocity')</h5>
    </div>
    <div class="card-body">
        <canvas id="velocityChart{{ $studentId }}" height="80"></canvas>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('velocityChart{{ $studentId }}');
    if (!ctx) return;

    const velocityData = @json($progressData['velocityWeeks'] ?? []);
    const labels = Object.keys(velocityData);
    const values = Object.values(velocityData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '@lang('Pages Memorized')',
                data: values,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: '@lang('Pages')' } },
                x: { title: { display: true, text: '@lang('Week')' } }
            }
        }
    });
});
</script>
@endpush
```

**Step 3: Create page-difficulty-heatmap Blade component**

```blade
{{-- resources/views/components/page-difficulty-heatmap.blade.php --}}
@props(['studentId', 'heatmapData'])

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">@lang('Page Difficulty Heatmap')</h5>
    </div>
    <div class="card-body">
        <div class="heatmap-grid" id="heatmap{{ $studentId }}" style="display: grid; grid-template-columns: repeat(20, 1fr); gap: 2px;">
            @for ($page = 1; $page <= 604; $page++)
                @php
                    $errorRate = $heatmapData[$page] ?? null;
                    $color = is_null($errorRate) ? '#e9ecef' : ($errorRate < 0.2 ? '#198754' : ($errorRate < 0.5 ? '#ffc107' : ($errorRate < 0.8 ? '#fd7e14' : '#dc3545')));
                @endphp
                <div class="heatmap-cell"
                     style="background: {{ $color }}; width: 100%; aspect-ratio: 1; cursor: pointer; border-radius: 2px;"
                     data-bs-toggle="tooltip"
                     data-bs-title="{{ __('Page') }} {{ $page }}: {{ is_null($errorRate) ? __('No data') : round($errorRate * 100) . '%' }}"
                     title="{{ __('Page') }} {{ $page }}">
                </div>
            @endfor
        </div>
        <div class="mt-2 d-flex gap-3">
            <span><span style="display:inline-block;width:12px;height:12px;background:#198754;border-radius:2px"></span> @lang('Easy')</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#ffc107;border-radius:2px"></span> @lang('Medium')</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#fd7e14;border-radius:2px"></span> @lang('Hard')</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#dc3545;border-radius:2px"></span> @lang('Needs Work')</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#e9ecef;border-radius:2px"></span> @lang('No Data')</span>
        </div>
    </div>
</div>
```

**Step 4: Create recent-session-timeline Blade component**

```blade
{{-- resources/views/components/recent-session-timeline.blade.php --}}
@props(['sessions'])

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">@lang('Recent Sessions')</h5>
    </div>
    <div class="card-body">
        <ul class="list-group list-group-flush">
            @forelse($sessions as $session)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-secondary me-2">{{ __($session['type']) }}</span>
                        <small class="text-muted">{{ $session['date'] }}</small>
                    </div>
                    <div>
                        @php
                            $score = $session['score'];
                            $badgeClass = $score >= 90 ? 'bg-success' : ($score >= 70 ? 'bg-warning' : 'bg-danger');
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $score }}%</span>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-muted">@lang('No sessions yet')</li>
            @endforelse
        </ul>
    </div>
</div>
```

**Step 5: Modify supervisor student show page to include charts**

Add to `resources/views/supervisor/students/show.blade.php` after the existing student info section:

```blade
{{-- Add after student info card --}}
<x-student-progress-chart :student-id="$student->id" />

<div class="row">
    <div class="col-md-8">
        <x-page-difficulty-heatmap :student-id="$student->id" :heatmap-data="$heatmapData" />
    </div>
    <div class="col-md-4">
        <x-recent-session-timeline :sessions="$recentSessions" />
    </div>
</div>
```

**Step 6: Update controller to pass analytics data**

Modify `Supervisor/StudentController.php` `show()` method:

```php
// In show() method, after finding the student:
$analyticsService = app(AnalyticsService::class);
$progressData = $analyticsService->getStudentProgressData($student->id);

$heatmapData = $progressData->pageDifficulty;
$recentSessions = $progressData->recentSessions;

// Add to compact() or return view:
return view('supervisor.students.show', compact(
    'student', 'level', 'supervisor', 'parents',
    'heatmapData', 'recentSessions', 'progressData'
));
```

**Step 7: Add to parent child show page**

Modify `resources/views/parent/children/show.blade.php` with same components (read-only view — no evaluation controls).

**Step 8: Test**

- As supervisor, visit `/supervisor/students/{id}` — verify charts render
- Check browser console for Chart.js errors
- Verify heatmap tooltips work

**Step 9: Commit**

```bash
git add resources/views/components/student-progress-chart.blade.php resources/views/components/page-difficulty-heatmap.blade.php resources/views/components/recent-session-timeline.blade.php resources/views/supervisor/students/show.blade.php app/Http/Controllers/Supervisor/StudentController.php
git commit -m "feat(analytics): add student progress charts to supervisor student detail page

Adds velocity chart, page difficulty heatmap, and recent session timeline using Chart.js.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

### Task 3: Add Supervisor Performance to Admin Dashboard

**Files:**
- Modify: `resources/views/admin/dashboard.blade.php`
- Modify: `app/Http/Controllers/Admin/DashboardController.php`
- Create: `resources/views/components/supervisor-performance-card.blade.php`

**Step 1: Read existing dashboard controller**

```
cat app/Http/Controllers/Admin/DashboardController.php
```

**Step 2: Create supervisor-performance-card Blade component**

```blade
{{-- resources/views/components/supervisor-performance-card.blade.php --}}
@props(['supervisor', 'perf'])

<div class="col-md-4 mb-3">
    <div class="card border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ $supervisor['name'] }}</h6>
            @if($perf->pendingWarningDrafts > 0)
                <span class="badge bg-warning">{{ $perf->pendingWarningDrafts }} @lang('pending')</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">@lang('Students')</small>
                    <strong>{{ $perf->studentsCount }}</strong>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">@lang('Evaluations/Month')</small>
                    <strong>{{ $perf->evaluationsThisMonth }}</strong>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">@lang('Avg Score')</small>
                    <strong>{{ $perf->avgScoreThisMonth ?? '-' }}%</strong>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">@lang('Response Time')</small>
                    <strong>{{ $perf->avgResponseTimeDays !== null ? $perf->avgResponseTimeDays . 'd' : '-' }}</strong>
                </div>
            </div>
            {{-- Mini sparkline using Chart.js --}}
            <canvas id="sparkline{{ $supervisor['supervisor_id'] }}" height="40"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('sparkline{{ $supervisor['supervisor_id'] }}');
    if (!ctx) return;
    const trend = @json(array_values($perf->monthlyTrend));
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(@json($perf->monthlyTrend)),
            datasets: [{
                data: trend,
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 2,
                tension: 0.3,
                pointRadius: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { display: false }
            }
        }
    });
});
</script>
@endpush
```

**Step 3: Modify DashboardController**

```php
// In index() method:
$analyticsService = app(AnalyticsService::class);
$supervisorsPerformance = $analyticsService->getAllSupervisorsPerformance();

return view('admin.dashboard', compact(
    'totalStudents', 'totalSupervisors', 'recentWarnings',
    'supervisorsPerformance'
));
```

**Step 4: Modify admin dashboard Blade**

Add after existing stats row in `resources/views/admin/dashboard.blade.php`:

```blade
<div class="row mt-4">
    <div class="col-12">
        <h5>@lang('Supervisor Performance')</h5>
    </div>
    @forelse($supervisorsPerformance as $sp)
        <x-supervisor-performance-card :supervisor="$sp" :perf="$sp['data']" />
    @empty
        <div class="col-12">
            <p class="text-muted">@lang('No supervisors found')</p>
        </div>
    @endforelse
</div>
```

**Step 5: Test**

- Visit `/admin/dashboard` as admin
- Verify supervisor cards appear with correct data
- Verify sparklines render

**Step 6: Commit**

```bash
git add resources/views/components/supervisor-performance-card.blade.php app/Http/Controllers/Admin/DashboardController.php resources/views/admin/dashboard.blade.php
git commit -m "feat(analytics): add supervisor performance cards to admin dashboard

Shows students count, evaluations/month, avg score, pending warnings, and monthly trend sparkline.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Track 2: In-Portral Messaging + Push Notifications

### Task 4: Install and Configure Laravel Web Push

**Files:**
- Modify: `composer.json` (add beyondcode/laravel-web-push)
- Create: `config/web-push.php`
- Create: `database/migrations/XXXX_create_push_subscriptions_table.php`
- Modify: `app/Models/User.php`

**Step 1: Install package**

```bash
cd /c/xampp/htdocs/quran-system
composer require beyondcode/laravel-web-push:^8.0
```

**Step 2: Publish config**

```bash
php artisan vendor:publish --tag=web-push-config
```

**Step 3: Create push subscriptions migration**

```bash
php artisan make:migration create_push_subscriptions_table
```

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('endpoint')->unique();
            $table->string('key_p256dh')->nullable();
            $table->string('key_auth')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
```

**Step 4: Generate VAPID keys**

```bash
php artisan web-push:generate-vapid-keys
```

Output: Saves VAPID keys to `.env`:
```
WEB_PUSH_VAPID_SUBJECT=mailto:notifications@quran-system.com
WEB_PUSH_VAPID_PUBLIC_KEY=<64-char-key>
WEB_PUSH_VAPID_PRIVATE_KEY=<87-char-key>
```

**Step 5: Update User model**

```php
// In app/Models/User.php, add:
use Beyondcode\LaravelWebPush\CanSendWebPushNotifications;

class User extends Authenticatable
{
    use CanSendWebPushNotifications;
    // ... existing traits and methods
}
```

**Step 6: Add routes for push subscription management**

In `routes/web.php` or a new `routes/push.php`:

```php
// Push subscription management
Route::post('push/subscribe', [PushController::class, 'subscribe'])->middleware('auth');
Route::delete('push/unsubscribe', [PushController::class, 'unsubscribe'])->middleware('auth');
```

**Step 7: Create PushController**

```bash
php artisan make:controller PushController
```

```php
<?php
// app/Http/Controllers/PushController.php
namespace App\Http\Controllers;

use Beyondcode\LaravelWebPush\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushController extends Controller
{
    public function subscribe(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
            'key_p256dh' => 'nullable|string',
            'key_auth' => 'nullable|string',
        ]);

        try {
            $request->user()->subscribePush(
                $request->input('endpoint'),
                $request->input('key_p256dh'),
                $request->input('key_auth')
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Push subscription failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function unsubscribe(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->user()->unsubscribePush($request->input('endpoint'));
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
```

**Step 8: Commit**

```bash
git add composer.json composer.lock database/migrations/XXXX_create_push_subscriptions_table.php app/Models/User.php app/Http/Controllers/PushController.php routes/web.php config/web-push.php
git commit -m "feat(notifications): install and configure beyondcode/laravel-web-push

Adds push subscription management, VAPID keys generated, and PushController.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

### Task 5: Build UserNotification Model and Migration

**Files:**
- Create: `database/migrations/XXXX_create_user_notifications_table.php`
- Create: `app/Models/UserNotification.php`
- Modify: `app/Models/User.php` (add relationship)

**Step 1: Create migration**

```bash
php artisan make:migration create_user_notifications_table
```

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // e.g., 'warning_draft', 'recitation_session', 'attendance'
            $table->string('title');
            $table->text('body');
            $table->string('link')->nullable(); // URL to redirect to
            $table->json('data')->nullable(); // Additional payload
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
```

**Step 2: Create UserNotification model**

```php
<?php
// app/Models/UserNotification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'link',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
```

**Step 3: Update User model**

```php
// Add to User model:
public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(UserNotification::class);
}

public function unreadNotifications(): \Illuminate\Database\Relations\HasMany
{
    return $this->hasMany(UserNotification::class)->whereNull('read_at');
}
```

**Step 4: Create NotificationService**

```php
<?php
// app/Services/NotificationService.php
namespace App\Services;

use App\Models\UserNotification;
use App\Models\User;
use Beyondcode\LaravelWebPush\WebPush;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        private readonly WebPush $webPush,
    ) {}

    public function sendToUser(User $user, string $type, string $title, string $body, ?string $link = null, array $data = []): UserNotification
    {
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'data' => $data,
        ]);

        $this->sendWebPush($user, $title, $body, $link);

        return $notification;
    }

    public function sendToUsers(array $userIds, string $type, string $title, string $body, ?string $link = null, array $data = []): void
    {
        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'link' => $link,
                'data' => $data,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        UserNotification::insert($notifications);

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $this->sendWebPush($user, $title, $body, $link);
            }
        }
    }

    private function sendWebPush(User $user, string $title, string $body, ?string $link): void
    {
        try {
            $user->pushSubscription?->delete(); // clear null subscriptions

            if ($user->pushSubscription) {
                $this->webPush->send([
                    'endpoint' => $user->pushSubscription->endpoint,
                    'key' => $user->pushSubscription->key_p256dh,
                    'auth' => $user->pushSubscription->key_auth,
                ], json_encode([
                    'title' => $title,
                    'body' => $body,
                    'link' => $link,
                ]));
            }
        } catch (\Exception $e) {
            Log::warning('Web push failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
```

**Step 5: Run migrations**

```bash
php artisan migrate
```

**Step 6: Commit**

```bash
git add database/migrations/XXXX_create_user_notifications_table.php app/Models/UserNotification.php app/Services/NotificationService.php app/Models/User.php
git commit -m "feat(notifications): add UserNotification model and NotificationService

Supports database storage + web push via beyondcode/laravel-web-push.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

### Task 6: Build Notification UI (Dropdown + Full Page)

**Files:**
- Create: `resources/views/layouts/partials/notification-dropdown.blade.php`
- Create: `resources/views/notifications/index.blade.php`
- Create: `resources/views/components/notification-bell.blade.php`
- Modify: `resources/views/layouts/app.blade.php` (add bell to navbar)
- Create: `app/Http/Controllers/NotificationController.php`
- Modify: `routes/web.php`

**Step 1: Create NotificationController**

```php
<?php
// app/Http/Controllers/NotificationController.php
namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->notifications()->orderBy('created_at', 'desc');

        if ($request->filled('filter')) {
            $query->where('type', $request->filter);
        }

        $notifications = $query->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($notification->link) {
            return redirect($notification->link);
        }

        return redirect()->back();
    }

    public function markAllAsRead(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        return redirect()->back();
    }

    public function unreadCount(Request $request): \Illuminate\Http\JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();
        return response()->json(['count' => $count]);
    }
}
```

**Step 2: Add notification routes**

In `routes/web.php` (after auth routes):

```php
Route::middleware('auth')->group(function () {
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
});
```

**Step 3: Create notification bell Blade component**

```blade
{{-- resources/views/components/notification-bell.blade.php --}}
@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp

<li class="nav-item dropdown">
    <a class="nav-link position-relative" data-bs-toggle="dropdown" aria-expanded="false" href="#" role="button">
        <i class="bi bi-bell-fill"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                  style="font-size: 0.6rem;"
                  id="notificationBadge">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </a>
    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 320px; max-width: 400px;">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span>@lang('Notifications')</span>
            @if($unreadCount > 0)
                <a href="{{ route('notifications.markAllAsRead') }}" class="text-decoration-none small">
                    @lang('Mark all as read')
                </a>
            @endif
        </li>
        <li><hr class="dropdown-divider"></li>
        @forelse(auth()->user()->unreadNotifications()->limit(5)->get() as $notification)
            <li>
                <a class="dropdown-item d-flex flex-column py-2"
                   href="{{ route('notifications.markAsRead', $notification->id) }}"
                   style="white-space: normal;">
                    <small class="fw-bold">{{ $notification->title }}</small>
                    <small class="text-muted">{{ Str::limit($notification->body, 80) }}</small>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                </a>
            </li>
        @empty
            <li>
                <span class="dropdown-item text-muted">@lang('No new notifications')</span>
            </li>
        @endforelse
        <li><hr class="dropdown-divider"></li>
        <li class="text-center">
            <a href="{{ route('notifications.index') }}" class="dropdown-item small">
                @lang('View All Notifications')
            </a>
        </li>
    </ul>
</li>
```

**Step 4: Read existing app layout to find navbar**

```
cat resources/views/layouts/app.blade.php
```

**Step 5: Add bell to navbar in app.blade.php**

Find the `<ul class="navbar-nav">` or navigation section and insert:

```blade
<x-notification-bell />
```

**Step 6: Create full notifications index page**

```blade
{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">@lang('Notifications')</h4>
                    @if($notifications->whereNull('read_at')->count() > 0)
                        <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                @lang('Mark All as Read')
                            </button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    {{-- Filter tabs --}}
                    <ul class="nav nav-pills mb-3">
                        <li class="nav-item">
                            <a class="nav-link {{ !request('filter') ? 'active' : '' }}"
                               href="{{ route('notifications.index') }}">@lang('All')</a>
                        </li>
                        @foreach(['warning_draft', 'warning', 'recitation_session', 'attendance', 'milestone'] as $type)
                            <li class="nav-item">
                                <a class="nav-link {{ request('filter') === $type ? 'active' : '' }}"
                                   href="{{ route('notifications.index', ['filter' => $type]) }}">
                                    @lang(ucfirst(str_replace('_', ' ', $type)))
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Notification list --}}
                    <div class="list-group">
                        @forelse($notifications as $notification)
                            <a href="{{ route('notifications.markAsRead', $notification->id) }}"
                               class="list-group-item list-group-item-action d-flex gap-3 py-3
                                      {{ is_null($notification->read_at) ? 'bg-light' : '' }}">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 {{ is_null($notification->read_at) ? 'fw-bold' : '' }}">
                                        {{ $notification->title }}
                                    </h6>
                                    <p class="mb-1 text-muted">{{ $notification->body }}</p>
                                    <small class="text-muted">{{ $notification->created_at->toDateTimeString() }}</small>
                                </div>
                                @if(is_null($notification->read_at))
                                    <span class="badge bg-primary rounded-pill">@lang('New')</span>
                                @endif
                            </a>
                        @empty
                            <p class="text-muted text-center py-4">@lang('No notifications')</p>
                        @endforelse
                    </div>

                    {{ $notifications->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

**Step 7: Add JS to update badge via AJAX**

In `resources/views/layouts/app.blade.php` before `</body>`:

```blade
@auth
<script>
    // Poll for unread count every 60 seconds
    function updateNotificationBadge() {
        fetch('/notifications/unread-count')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notificationBadge');
                if (data.count > 0) {
                    if (badge) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                    } else {
                        // Create badge if it doesn't exist
                        const bell = document.querySelector('.nav-link:has(.bi-bell-fill)');
                        if (bell) {
                            const newBadge = document.createElement('span');
                            newBadge.id = 'notificationBadge';
                            newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                            newBadge.style.fontSize = '0.6rem';
                            newBadge.textContent = data.count > 99 ? '99+' : data.count;
                            bell.style.position = 'relative';
                            bell.appendChild(newBadge);
                        }
                    }
                } else if (badge) {
                    badge.remove();
                }
            })
            .catch(() => {});
    }
    setInterval(updateNotificationBadge, 60000);
</script>
@endauth
```

**Step 8: Test**

- Login as supervisor
- Visit `/notifications` — verify empty state shows
- Trigger a warning draft creation — verify notification appears

**Step 9: Commit**

```bash
git add app/Http/Controllers/NotificationController.php resources/views/layouts/partials/notification-dropdown.blade.php resources/views/components/notification-bell.blade.php resources/views/notifications/index.blade.php resources/views/layouts/app.blade.php routes/web.php
git commit -m "feat(notifications): add notification bell, dropdown, and full notifications page

Includes filter tabs, mark as read, mark all as read, and AJAX badge polling.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

### Task 7: Wire Up Notification Triggers

**Files:**
- Modify: `app/Http/Controllers/Supervisor/WarningDraftController.php`
- Modify: `app/Http/Controllers/Supervisor/RecitationController.php`
- Modify: `app/Http/Controllers/Supervisor/AttendanceController.php`

**Step 1: Read WarningDraftController**

```
cat app/Http/Controllers/Supervisor/WarningDraftController.php
```

**Step 2: Add NotificationService injection to WarningDraftController store()**

In `store()` method of `WarningDraftController.php`, after draft is created:

```php
// In store() method, after WarningDraft::create():
$notificationService = app(\App\Services\NotificationService::class);
$student = $draft->student;

$notificationService->sendToUser(
    $draft->supervisor,
    'warning_draft',
    __('New Warning Draft'),
    __("Warning draft for :name requires review", ['name' => $student->name]),
    route('supervisor.warning-drafts.show', $draft->id)
);
```

**Step 3: Add recitation session notification in RecitationController**

In `store()` or `saveSession()` — after session is saved:

```php
$notificationService = app(\App\Services\NotificationService::class);
$student = $recitationSession->student;
$parentUserIds = $student->parents()->pluck('users.id')->toArray();

if (!empty($parentUserIds)) {
    $typeLabel = __($recitationSession->type);
    $notificationService->sendToUsers(
        $parentUserIds,
        'recitation_session',
        __("Session Completed"),
        __(":name completed :type with score :score%", ['name' => $student->name, 'type' => $typeLabel, 'score' => $recitationSession->score]),
        route('parent.children.show', $student->id)
    );
}
```

**Step 4: Add attendance notification in AttendanceController**

In `store()` or `bulkStore()` — when absent/late is marked:

```php
$notificationService = app(\App\Services\NotificationService::class);
$student = $attendance->student;
$parentUserIds = $student->parents()->pluck('users.id')->toArray();

if (!empty($parentUserIds) && in_array($attendance->status, ['absent', 'late'])) {
    $notificationService->sendToUsers(
        $parentUserIds,
        'attendance',
        __("Attendance Alert"),
        __(":name was :status on :date", ['name' => $student->name, 'status' => __($attendance->status), 'date' => $attendance->date->format('Y-m-d')]),
        route('parent.children.show', $student->id)
    );
}
```

**Step 5: Commit**

```bash
git add app/Http/Controllers/Supervisor/WarningDraftController.php app/Http/Controllers/Supervisor/RecitationController.php app/Http/Controllers/Supervisor/AttendanceController.php
git commit -m "feat(notifications): wire up notification triggers for warnings, sessions, and attendance

Sends to appropriate supervisors and parents on event creation.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

### Task 8: PWA for Parent Portal

**Files:**
- Create: `public/manifest.json`
- Create: `public/sw.js`
- Create: `public/js/pwa-init.js`
- Modify: `resources/views/parent/layouts/app.blade.php`

**Step 1: Create manifest.json**

```json
{
  "name": "Madkar Parent Portal",
  "short_name": "Madkar",
  "description": "Parent portal for Quran memorization tracking",
  "start_url": "/parent/dashboard",
  "display": "standalone",
  "background_color": "#0d6efd",
  "theme_color": "#0d6efd",
  "orientation": "portrait",
  "icons": [
    {
      "src": "/images/icon-192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/images/icon-512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}
```

**Step 2: Create service worker**

```javascript
// public/sw.js
const CACHE_NAME = 'madkar-pwa-v1';
const urlsToCache = [
    '/',
    '/parent/dashboard',
    '/manifest.json',
];

// Install event
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(urlsToCache))
    );
    self.skipWaiting();
});

// Fetch event
self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => response || fetch(event.request))
    );
});

// Push event
self.addEventListener('push', (event) => {
    if (!event.data) return;

    const data = event.data.json();
    const options = {
        body: data.body || '',
        icon: '/images/icon-192.png',
        badge: '/images/icon-192.png',
        vibrate: [100, 50, 100],
        data: {
            url: data.link || '/parent/dashboard',
        },
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'Madkar', options)
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );
});
```

**Step 3: Create pwa-init.js**

```javascript
// public/js/pwa-init.js
async function initPWA() {
    if ('serviceWorker' in navigator) {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            console.log('SW registered:', registration.scope);
        } catch (error) {
            console.error('SW registration failed:', error);
        }
    }

    // Request push permission
    if ('Notification' in window && Notification.permission === 'default') {
        // Show custom prompt instead of browser default
        const btn = document.createElement('button');
        btn.className = 'btn btn-primary btn-sm position-fixed bottom-0 end-0 m-3';
        btn.style.zIndex = '9999';
        btn.textContent = 'Enable Push Notifications';
        btn.onclick = async () => {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                subscribeToPush();
                btn.remove();
            }
        };
        document.body.appendChild(btn);
    }
}

async function subscribeToPush() {
    if (!('serviceWorker' in navigator)) return;

    const registration = await navigator.serviceWorker.ready;
    const vapidPublicKey = document.querySelector('meta[name="vapid-key"]')?.content;
    if (!vapidPublicKey) return;

    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
    });

    await fetch('/push/subscribe', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
        body: JSON.stringify(subscription),
    });
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

document.addEventListener('DOMContentLoaded', initPWA);
```

**Step 4: Read and modify parent layout**

```
cat resources/views/parent/layouts/app.blade.php
```

Add to `<head>`:

```blade
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0d6efd">
<meta name="vapid-key" content="{{ env('WEB_PUSH_VAPID_PUBLIC_KEY') }}">
```

Add before `</body>`:

```blade
<script src="{{ asset('js/pwa-init.js') }}" defer></script>
```

**Step 5: Commit**

```bash
git add public/manifest.json public/sw.js public/js/pwa-init.js resources/views/parent/layouts/app.blade.php
git commit -m "feat(pwa): add manifest and service worker for parent portal push notifications

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Final Verification

### Run all migrations
```bash
php artisan migrate
```

### Run Playwright tests
```bash
npx playwright test
```

### Manual checks
- [ ] Supervisor: `/supervisor/students/{id}` shows velocity chart, heatmap, session timeline
- [ ] Parent: `/parent/children/{id}` shows same charts (read-only)
- [ ] Admin: `/admin/dashboard` shows supervisor performance cards
- [ ] Login as parent → bell icon visible in navbar
- [ ] Create a recitation session as supervisor → parent gets notification
- [ ] Mark student absent as supervisor → parent gets notification
- [ ] Visit `/notifications` → all notifications listed with filter tabs
- [ ] Click "Mark all as read" → all notifications marked
- [ ] Visit parent portal on mobile Chrome → "Add to Home Screen" prompt appears
- [ ] Grant push permission → subscription stored in `push_subscriptions` table
