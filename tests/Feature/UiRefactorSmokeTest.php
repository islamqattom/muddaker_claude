<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\ParentProfile;
use App\Models\Level;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Tests\CreatesApplication;

class UiRefactorSmokeTest extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        Level::firstOrCreate(['id' => 1], ['name' => 'L1', 'level_number' => 1, 'new_memorization_pages' => 3, 'review_pages' => 6]);
        User::firstOrCreate(['username' => 'ui_admin'], ['name' => 'Admin', 'email' => 'ui_admin@t.local', 'password' => Hash::make('s'), 'role' => 'admin']);
        $sup = User::firstOrCreate(['username' => 'ui_sup'], ['name' => 'Sup', 'email' => 'ui_sup@t.local', 'password' => Hash::make('s'), 'role' => 'supervisor']);
        Supervisor::firstOrCreate(['user_id' => $sup->id], ['employee_number' => 'SUP-UI']);
        $stu = User::firstOrCreate(['username' => 'ui_stu'], ['name' => 'Stu', 'email' => 'ui_stu@t.local', 'password' => Hash::make('s'), 'role' => 'student']);
        Student::firstOrCreate(['user_id' => $stu->id], ['supervisor_id' => $sup->id, 'student_number' => 'STU-UI', 'level_id' => 1]);
        $par = User::firstOrCreate(['username' => 'ui_par'], ['name' => 'Par', 'email' => 'ui_par@t.local', 'password' => Hash::make('s'), 'role' => 'parent']);
        ParentProfile::firstOrCreate(['user_id' => $par->id], []);
    }

    public function test_ui_pages(): void
    {
        $paths = [
            ['/admin/dashboard', 'ui_admin'],
            ['/supervisor/dashboard', 'ui_sup'],
            ['/student/dashboard', 'ui_stu'],
            ['/parent/dashboard', 'ui_par'],
            ['/admin/students', 'ui_admin'],
            ['/admin/levels', 'ui_admin'],
            ['/admin/supervisors', 'ui_admin'],
            ['/student/attendance', 'ui_stu'],
        ];
        $fail = 0;
        foreach ($paths as [$p, $u]) {
            $user = User::where('username', $u)->first();
            $r = $this->actingAs($user)->get($p);
            $code = $r->getStatusCode();
            $html = $r->getContent();
            $hasCss = str_contains($html, 'css/dashboard.css') && str_contains($html, 'css/page.css');
            // sanity: Arabic rendered (no mojibake like 'Ø')
            $mojibake = str_contains($html, 'Ø') || str_contains($html, 'Ù');
            echo str_pad($p, 24) . " status=$code css=" . ($hasCss ? 'Y' : 'N') . " mojibake=" . ($mojibake ? 'Y' : 'N') . "\n";
            if ($code !== 200 || $mojibake) $fail++;
        }
        $this->assertSame(0, $fail, "Some UI pages failed");
    }
}
