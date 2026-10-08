<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\ParentProfile;
use App\Models\Level;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ReportRoutesSmokeTest extends \Tests\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    private function seedUsers(): void
    {
        // Force canonical ids: this class does not use transactions and
        // AUTO_INCREMENT is not reset by RefreshDatabase rollbacks.
        Model::unguard();

        Level::firstOrCreate(['id' => 1], [
            'name' => 'مستوى 1', 'level_number' => 1,
            'new_memorization_pages' => 3, 'review_pages' => 6, 'description' => 'تجريبي',
        ]);

        User::updateOrCreate(
            ['email' => 'admin@test.local'],
            ['id' => 1, 'name' => 'مدير النظام', 'username' => 'admin', 'password' => Hash::make('secret'), 'role' => 'admin']
        );
        User::updateOrCreate(
            ['email' => 'sup@test.local'],
            ['id' => 2, 'name' => 'مشرف تجريبي', 'username' => 'supervisor', 'password' => Hash::make('secret'), 'role' => 'supervisor']
        );
        User::updateOrCreate(
            ['email' => 'stu@test.local'],
            ['id' => 3, 'name' => 'طالب تجريبي', 'username' => 'student', 'password' => Hash::make('secret'), 'role' => 'student']
        );
        User::updateOrCreate(
            ['email' => 'par@test.local'],
            ['id' => 4, 'name' => 'ولي أمر تجريبي', 'username' => 'parent', 'password' => Hash::make('secret'), 'role' => 'parent']
        );

        Supervisor::updateOrCreate(
            ['user_id' => 2],
            ['employee_number' => 'SUP-001']
        );
        Student::updateOrCreate(
            ['user_id' => 3],
            ['supervisor_id' => 2, 'student_number' => 'STU-001', 'level_id' => 1]
        );
        $stu = Student::where('user_id', 3)->first();
        ParentProfile::updateOrCreate(
            ['user_id' => 4],
            ['national_id' => '9999999999', 'phone' => '0500000000', 'relationship' => 'father']
        );
        $pp = ParentProfile::where('user_id', 4)->first();
        if ($pp && $stu && !\DB::table('parent_student')->where('parent_id', $pp->id)->where('student_id', $stu->id)->exists()) {
            \DB::table('parent_student')->insert(['parent_id' => $pp->id, 'student_id' => $stu->id]);
        }
        User::reguard();
        fwrite(STDERR, 'seeded: ' . User::count() . PHP_EOL);
    }

    private function hit($method, $url, $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            $this->markTestSkipped("no user $userId");
            return;
        }
        $response = $this->actingAs($user)->call($method, $url);
        $status = $response->getStatusCode();
        $body = $response->getContent() ?: '';
        $snippet = preg_replace('/\s+/', ' ', strip_tags($body));
        $snippet = mb_substr($snippet, 0, 200);
        $msg = sprintf('%s %s as %s -> %d  %s', $method, $url, $user->role, $status, $snippet);
        fwrite(STDERR, $msg . PHP_EOL);
        $this->assertLessThan(500, $status, "Route returned server error: $url");
    }

    public function test_admin_routes()
    {
        $student = Student::where('user_id', 3)->first();
        $urls = [
            '/admin/reports',
            '/admin/reports/recitation',
            '/admin/reports/recitation?period=today',
            '/admin/reports/recitation?type=review&status=failed',
            '/admin/reports/recitation?status=passed&period=custom&from_date=2026-01-01&to_date=2026-02-01',
            '/admin/reports/attendance',
            '/admin/reports/attendance?status=absent',
            '/admin/reports/attendance?status=present&period=custom&from_date=2026-01-01&to_date=2026-02-01',
            '/admin/reports/quarter',
            '/admin/reports/quarter?year=2026&quarter=3',
            '/admin/reports/quarter?rec_page=2&att_page=2',
            '/admin/reports/half',
            '/admin/reports/half?year=2026&half=2',
            '/admin/reports/year',
            '/admin/reports/year?year=2026&type=new_memorization',
            '/admin/reports/quarter?export=excel',
            '/admin/reports/quarter?export=print',
            '/admin/reports/quarter?export=pdf',
            '/admin/reports/recitation?export=excel',
            '/admin/reports/recitation?export=print',
            '/admin/reports/recitation?export=pdf',
            '/admin/yawm-al-himma',
            '/admin/reports/himma',
            '/admin/reports/himma?period=today',
            '/admin/reports/himma?export=excel',
            '/admin/reports/himma?export=pdf',
            '/admin/reports/himma/day/2026-09-01',
            '/admin/reports/himma/day/2026-09-01?export=pdf',
            '/admin/reports/himma/day/2026-09-01?export=excel',
            '/admin/reports/attendance?export=excel',
            '/admin/reports/attendance?export=print',
            '/admin/reports/attendance?export=pdf',
        ];
        foreach ($urls as $u) {
            $this->hit('GET', $u, 1);
        }
        if ($student) {
            $this->hit('GET', '/admin/reports/student/' . $student->id, 1);
            $this->hit('GET', '/admin/reports/student/' . $student->id . '?export=pdf', 1);
            $this->hit('GET', '/admin/reports/himma/student/' . $student->id, 1);
            $this->hit('GET', '/admin/reports/himma/student/' . $student->id . '?export=pdf', 1);
            $this->hit('GET', '/admin/reports/himma/student/' . $student->id . '?export=excel', 1);
        }
    }

    public function test_supervisor_routes()
    {
        $student = Student::where('user_id', 3)->first();
        $urls = [
            '/supervisor/reports',
            '/supervisor/reports/recitation',
            '/supervisor/reports/recitation?type=mafsil&status=passed',
            '/supervisor/reports/attendance',
            '/supervisor/reports/attendance?status=late',
            '/supervisor/reports/quarter',
            '/supervisor/reports/quarter?rec_page=2&att_page=2',
            '/supervisor/reports/half',
            '/supervisor/reports/half?year=2026&half=1',
            '/supervisor/reports/year',
            '/supervisor/reports/year?year=2026',
            '/supervisor/reports/quarter?export=excel',
            '/supervisor/reports/quarter?export=print',
            '/supervisor/reports/recitation?export=excel',
            '/supervisor/yawm-al-himma',
            '/supervisor/reports/himma',
            '/supervisor/reports/himma?export=excel',
            '/supervisor/reports/himma/day/2026-09-01',
        ];
        foreach ($urls as $u) {
            $this->hit('GET', $u, 2);
        }
        if ($student) {
            $this->hit('GET', '/supervisor/reports/student/' . $student->id, 2);
            $this->hit('GET', '/supervisor/reports/student/' . $student->id . '?export=pdf', 2);
            $this->hit('GET', '/supervisor/reports/himma/student/' . $student->id, 2);
        }
    }

    public function test_student_routes()
    {
        $this->hit('GET', '/student/reports', 3);
    }

    public function test_parent_routes()
    {
        $this->hit('GET', '/parent/reports', 4);
    }
}
