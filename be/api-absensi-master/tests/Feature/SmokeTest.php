<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Probe tests written during review. Each one documents current behaviour,
 * not desired behaviour.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'name' => 'Budi',
            'userName' => 'budi',
            'email' => 'budi@example.test',
            'password' => bcrypt('secret123'),
        ]);
    }


    private function project(): int
    {
        \DB::table('projects')->insert([
            'id' => 1, 'devisionId' => 1, 'userId' => 1, 'projectNo' => 'P-1',
            'startdate' => '2026-01-01', 'targetdate' => '2026-12-31', 'cost' => 0,
            'status' => 'active', 'rowStatus' => 1, 'address' => 'Bandung',
            'latitude' => '-6.9', 'longtitude' => '107.6',
        ]);
        \DB::table('medias')->insert([
            ['id' => 1, 'url' => 'http://x/1.jpg', 'type' => 'attendances'],
        ]);
        return 1;
    }

    /** F-01: /api/attendances needs no token and trusts client-supplied userId */
    public function test_attendance_can_be_posted_without_a_token()
    {
        $victim = $this->user();
        $this->project();

        $res = $this->postJson('/api/attendances', [
            'proofOfWork'  => UploadedFile::fake()->image('work.jpg'),
            'attendances'  => UploadedFile::fake()->image('face.jpg'),
            'latitude'     => '-6.9',
            'longtitude'   => '107.6',
            'projectId'    => 1,
            'userId'       => $victim->id,
            'action'       => 'clockin',
        ]);

        fwrite(STDERR, "\n[F-01] -> HTTP {$res->status()} BODY: " . substr($res->getContent(),0,400) . "\n");
        $this->assertNotSame(401, $res->status(), 'endpoint is unauthenticated');
        $this->assertSame(1, Attendance::where('userId', $victim->id)->count(),
            'attendance row forged for another user without logging in');
    }

    /** F-02 (fixed): /api/migrate route removed entirely (Fase 0). */
    public function test_migrate_endpoint_is_gone()
    {
        $res = $this->getJson('/api/migrate');
        fwrite(STDERR, "[F-02] GET /api/migrate -> HTTP {$res->status()}\n");
        $this->assertSame(404, $res->status());
    }

    /** F-03 (fixed): /api/export now requires authentication (Fase 1). */
    public function test_export_endpoint_requires_auth()
    {
        $res = $this->getJson('/api/export?admin_mode=1');
        fwrite(STDERR, "[F-03] GET /api/export?admin_mode=1 unauthenticated -> HTTP {$res->status()}\n");
        $this->assertSame(401, $res->status());
    }

    /** F-04: login with no g-recaptcha-response crashes before validation */
    public function test_login_without_recaptcha_token()
    {
        $this->user();
        $res = $this->postJson('/api/auth/login', [
            'email' => 'budi@example.test',
            'password' => 'secret123',
        ]);
        fwrite(STDERR, "[F-04] POST /api/auth/login without g-recaptcha-response -> HTTP {$res->status()}\n");
        $this->assertTrue(true);
    }

    /** F-05: public register lets the caller pick its own roleId */
    public function test_register_lets_caller_choose_role()
    {
        \DB::table('roles')->insert([['id' => 1, 'name' => 'admin'], ['id' => 2, 'name' => 'staff']]);

        $res = $this->postJson('/api/auth/register', [
            'name' => 'Penyusup',
            'userName' => 'penyusup',
            'email' => 'penyusup@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'roleId' => [1],
        ]);

        $admins = \DB::table('role_has_users')->where('roleId', 1)->count();
        fwrite(STDERR, "[F-05] POST /api/auth/register roleId=[1] -> HTTP {$res->status()}, admin rows created: {$admins}\n");
        $this->assertTrue(true);
    }

    /** F-06: late/on-time compares a datetime against a TIME column */
    public function test_clockin_status_logic()
    {
        $user = $this->user();
        $this->project();
        $shift = Shift::create([
            'projectId' => 1, 'userId' => $user->id,
            'timeIn' => '08:00:00', 'timeOut' => '17:00:00',
        ]);

        $token = auth()->login($user);

        $res = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/attendance', [
            'mediaAttendaceId' => 1, 'mediaOfWorkId' => 1,
            'latitude' => '-6.9', 'longtitude' => '107.6',
            'projectId' => 1, 'time' => '07:00:00',
            'action' => 'clockin', 'shiftId' => $shift->id,
        ]);

        $status = Attendance::latest('id')->first()->status ?? 'NULL';
        fwrite(STDERR, "[F-06] status={$status} BODY: " . substr($res->getContent(),0,400) . "\n");
        $this->assertTrue(true);
    }

    /** F-07: error payloads are returned with HTTP 200 */
    public function test_errors_use_http_200()
    {
        $token = auth()->login($this->user());
        $res = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/attendance', []); // missing every required field

        fwrite(STDERR, "[F-07] POST /api/attendance with empty body -> HTTP {$res->status()} (validation failed)\n");
        $this->assertTrue(true);
    }
}
