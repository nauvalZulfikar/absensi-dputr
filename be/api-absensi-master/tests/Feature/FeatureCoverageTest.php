<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File as FakeFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Coverage happy-path SEMUA fitur (endpoint) sebagai full admin.
 * Tiap fitur = satu test. Gagal kalau HTTP != 200 ATAU amplop meta.status=false
 * (error yang disembunyikan di balik HTTP 200). Tujuan: ketemu fitur yang rusak.
 */
class FeatureCoverageTest extends TestCase
{
    use RefreshDatabase;

    private string $token;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');
        Storage::fake('public');

        \DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'user'],
            ['id' => 3, 'name' => 'user_admin'],
        ]);
        \DB::table('devisions')->insert([
            ['id' => 1, 'name' => 'Divisi Satu'],
            ['id' => 2, 'name' => 'Divisi Dua'],
        ]);

        $this->admin = $this->makeUser('admin@test', 'admin');
        $this->token = auth()->login($this->admin);

        $this->makeProject(1, 1);
        \DB::table('profiles')->insert(['userId' => $this->admin->id, 'name' => 'Admin']);
        \DB::table('medias')->insert([
            ['id' => 1, 'url' => 'http://x/1.jpg', 'type' => 'attendances'],
            ['id' => 2, 'url' => 'http://x/2.jpg', 'type' => 'proofOfWork'],
        ]);
    }

    // ---------- helpers ----------

    private function makeUser(string $email, string $roleName): User
    {
        $u = User::create([
            'name' => $email, 'userName' => $email, 'email' => $email,
            'password' => bcrypt('secret123'),
        ]);
        $roleId = \DB::table('roles')->where('name', $roleName)->value('id');
        \DB::table('role_has_users')->insert(['userId' => $u->id, 'roleId' => $roleId]);
        return $u;
    }

    private function makeProject(int $id, int $devisionId): void
    {
        \DB::table('projects')->insert([
            'id' => $id, 'devisionId' => $devisionId, 'userId' => 1, 'projectNo' => "P-$id",
            'name' => "Proj $id", 'slug' => "proj-$id",
            'startdate' => '2026-01-01', 'targetdate' => '2026-12-31', 'cost' => 0,
            'status' => 'active', 'rowStatus' => 1, 'address' => 'Bandung',
            'latitude' => '-6.9', 'longtitude' => '107.6',
        ]);
    }

    private function api(): TestCase|self
    {
        return $this->withHeader('Authorization', "Bearer {$this->token}");
    }

    /** Sukses = HTTP 200 dan (kalau ada amplop) meta.status !== false. */
    private function assertFeatureOk(TestResponse $res, string $label): void
    {
        $this->assertSame(200, $res->status(), "$label: HTTP harus 200, dapat {$res->status()} — {$res->getContent()}");
        $metaStatus = $res->json('meta.status');
        if ($metaStatus !== null) {
            $this->assertNotFalse(
                $metaStatus,
                "$label: amplop error (meta.status=false) — {$res->getContent()}"
            );
        }
    }

    private function seedShift(int $projectId): int
    {
        $res = $this->api()->postJson('/api/shift/store', [
            'project_id' => $projectId, 'timeIn' => '08:00:00', 'timeOut' => '17:00:00',
            'type' => 'reguler', 'startdate' => '2026-01-01', 'targetdate' => '2026-12-31',
        ]);
        return (int) $res->json('data.id');
    }

    private function seedAttendance(int $projectId, int $shiftId, int $userId): int
    {
        $res = $this->api()->postJson('/api/attendance', [
            'mediaAttendaceId' => 1, 'mediaOfWorkId' => 2, 'projectId' => $projectId,
            'latitude' => '-6.9', 'longtitude' => '107.6', 'time' => '08:00:00',
            'action' => 'clockin', 'shiftId' => $shiftId,
        ]);
        return (int) $res->json('data.id');
    }

    // ==================== AUTH ====================

    public function test_auth_register()
    {
        $res = $this->postJson('/api/auth/register', [
            'name' => 'Baru', 'userName' => 'baru', 'email' => 'baru@test',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ]);
        $this->assertFeatureOk($res, 'auth/register');
    }

    public function test_auth_login()
    {
        $res = $this->postJson('/api/auth/login', ['email' => 'admin@test', 'password' => 'secret123']);
        $this->assertFeatureOk($res, 'auth/login');
        $this->assertNotNull($res->json('access_token') ?? $res->json('data.access_token') ?? $res->json('token'), 'login harus balik token');
    }

    public function test_auth_refresh()
    {
        $res = $this->api()->postJson('/api/auth/refresh');
        $this->assertFeatureOk($res, 'auth/refresh');
    }

    public function test_auth_change_password()
    {
        $res = $this->api()->postJson('/api/auth/change-password', [
            'currentPassword' => 'secret123', 'newPassword' => 'secret456',
        ]);
        $this->assertFeatureOk($res, 'auth/change-password');
    }

    public function test_auth_logout()
    {
        $res = $this->api()->postJson('/api/auth/logout');
        $this->assertFeatureOk($res, 'auth/logout');
    }

    // ==================== PROFILE ====================

    public function test_profile_userprofile()
    {
        $res = $this->api()->getJson('/api/profile');
        $this->assertFeatureOk($res, 'profile (userProfile)');
    }

    public function test_profile_me()
    {
        $res = $this->api()->getJson('/api/profile/me');
        $this->assertFeatureOk($res, 'profile/me');
    }

    public function test_profile_edit()
    {
        $res = $this->api()->postJson('/api/profile/edit', ['name' => 'Nama Baru', 'address' => 'Bandung']);
        $this->assertFeatureOk($res, 'profile/edit');
    }

    // ==================== USER ====================

    public function test_user_list()
    {
        $res = $this->api()->postJson('/api/user/all', []);
        $this->assertFeatureOk($res, 'user/all');
    }

    public function test_user_store()
    {
        $res = $this->api()->postJson('/api/user', [
            'name' => 'User Baru', 'username' => 'userbaru', 'email' => 'userbaru@test',
            'role_ids' => [2], 'profile_nik' => '123',
        ]);
        $this->assertFeatureOk($res, 'user (store)');
    }

    public function test_user_selected()
    {
        $res = $this->api()->postJson('/api/user/selected', []);
        $this->assertFeatureOk($res, 'user/selected');
    }

    public function test_user_summary()
    {
        $res = $this->api()->getJson('/api/user/summary');
        $this->assertFeatureOk($res, 'user/summary');
    }

    public function test_user_profile_update()
    {
        $res = $this->api()->postJson('/api/user/profile', ['name' => 'Ganti']);
        $this->assertFeatureOk($res, 'user/profile');
    }

    // ==================== DEVISION ====================

    public function test_devision_list()
    {
        $res = $this->api()->postJson('/api/devision', []);
        $this->assertFeatureOk($res, 'devision (list)');
    }

    public function test_devision_show()
    {
        $res = $this->api()->getJson('/api/devision/show/1');
        $this->assertFeatureOk($res, 'devision/show');
    }

    public function test_devision_store()
    {
        $res = $this->api()->postJson('/api/devision/store', ['name' => 'Divisi Baru', 'description' => 'x']);
        $this->assertFeatureOk($res, 'devision/store');
    }

    public function test_devision_update()
    {
        $res = $this->api()->putJson('/api/devision/update/1', ['name' => 'Divisi Diubah']);
        $this->assertFeatureOk($res, 'devision/update');
    }

    public function test_devision_destroy()
    {
        $res = $this->api()->postJson('/api/devision/destroy/2');
        $this->assertFeatureOk($res, 'devision/destroy');
    }

    // ==================== PROJECT ====================

    public function test_project_list()
    {
        $res = $this->api()->postJson('/api/project', []);
        $this->assertFeatureOk($res, 'project (list)');
    }

    public function test_project_show()
    {
        $res = $this->api()->getJson('/api/project/show/1');
        $this->assertFeatureOk($res, 'project/show');
    }

    public function test_project_global()
    {
        $res = $this->api()->getJson('/api/project/global');
        $this->assertFeatureOk($res, 'project/global');
    }

    public function test_project_detail()
    {
        $res = $this->api()->getJson('/api/project/detail-project?projectId=1');
        $this->assertFeatureOk($res, 'project/detail-project');
    }

    public function test_project_store()
    {
        $res = $this->api()->postJson('/api/project/store', [
            'name' => 'Project Baru', 'devisionId' => 1, 'projectNo' => 'P-99',
            'startdate' => '2026-01-01', 'targetdate' => '2026-12-31', 'cost' => 1000,
            'address' => 'Bandung', 'latitude' => '-6.9', 'longitude' => '107.6',
        ]);
        $this->assertFeatureOk($res, 'project/store');
    }

    public function test_project_update()
    {
        $res = $this->api()->putJson('/api/project/update/1', ['name' => 'Project Diubah']);
        $this->assertFeatureOk($res, 'project/update');
    }

    public function test_project_destroy()
    {
        $this->makeProject(2, 1);
        $res = $this->api()->postJson('/api/project/destroy/2');
        $this->assertFeatureOk($res, 'project/destroy');
    }

    // ==================== PROGRES ====================

    public function test_progres_list()
    {
        $res = $this->api()->postJson('/api/progres', []);
        $this->assertFeatureOk($res, 'progres (list)');
    }

    public function test_progres_store()
    {
        $res = $this->api()->postJson('/api/progres/store', [
            'projectId' => 1, 'fisik' => 10, 'pencairan' => 5, 'date' => '2026-02-01',
        ]);
        $this->assertFeatureOk($res, 'progres/store');
    }

    public function test_progres_show()
    {
        $id = $this->api()->postJson('/api/progres/store', ['projectId' => 1, 'fisik' => 1, 'pencairan' => 1, 'date' => '2026-02-01'])->json('data.id');
        $res = $this->api()->getJson("/api/progres/show/$id");
        $this->assertFeatureOk($res, 'progres/show');
    }

    public function test_progres_update()
    {
        $id = $this->api()->postJson('/api/progres/store', ['projectId' => 1, 'fisik' => 1, 'pencairan' => 1, 'date' => '2026-02-01'])->json('data.id');
        $res = $this->api()->putJson("/api/progres/update/$id", ['fisik' => 50]);
        $this->assertFeatureOk($res, 'progres/update');
    }

    public function test_progres_destroy()
    {
        $id = $this->api()->postJson('/api/progres/store', ['projectId' => 1, 'fisik' => 1, 'pencairan' => 1, 'date' => '2026-02-01'])->json('data.id');
        $res = $this->api()->postJson("/api/progres/destroy/$id");
        $this->assertFeatureOk($res, 'progres/destroy');
    }

    // ==================== SHIFT ====================

    public function test_shift_list()
    {
        $res = $this->api()->postJson('/api/shift', []);
        $this->assertFeatureOk($res, 'shift (list)');
    }

    public function test_shift_store()
    {
        $res = $this->api()->postJson('/api/shift/store', [
            'project_id' => 1, 'timeIn' => '08:00:00', 'timeOut' => '17:00:00', 'type' => 'reguler',
            'startdate' => '2026-01-01', 'targetdate' => '2026-12-31',
        ]);
        $this->assertFeatureOk($res, 'shift/store');
    }

    public function test_shift_show()
    {
        $sid = $this->seedShift(1);
        $res = $this->api()->getJson("/api/shift/show/$sid");
        $this->assertFeatureOk($res, 'shift/show');
    }

    public function test_shift_show2()
    {
        $res = $this->api()->postJson('/api/shift/show2', ['projectIds' => [1], 'userIds' => [$this->admin->id]]);
        $this->assertFeatureOk($res, 'shift/show2');
    }

    public function test_shift_update()
    {
        $sid = $this->seedShift(1);
        $res = $this->api()->putJson("/api/shift/update/$sid", ['timeIn' => '09:00:00']);
        $this->assertFeatureOk($res, 'shift/update');
    }

    public function test_shift_add_user()
    {
        $sid = $this->seedShift(1);
        // user harus sudah di-assign ke project agar notifikasi join menemukan datanya
        \DB::table('user_have_project')->insert(['user_id' => $this->admin->id, 'project_id' => 1, 'type' => 'assign']);
        $res = $this->api()->postJson('/api/shift/add-user', [
            'shift_id' => $sid, 'user_ids' => [$this->admin->id], 'project_ids' => [1],
        ]);
        $this->assertFeatureOk($res, 'shift/add-user');
    }

    public function test_shift_delete_user()
    {
        $sid = $this->seedShift(1);
        $this->api()->postJson('/api/shift/add-user', ['shift_id' => $sid, 'user_ids' => [$this->admin->id], 'project_ids' => [1]]);
        $relId = \DB::table('shift_have_users')->where('shift_id', $sid)->value('id');
        $res = $this->api()->postJson('/api/shift/delete-user', ['relation_id' => $relId]);
        $this->assertFeatureOk($res, 'shift/delete-user');
    }

    public function test_shift_destroy()
    {
        $sid = $this->seedShift(1);
        $res = $this->api()->deleteJson("/api/shift/destroy/$sid");
        $this->assertFeatureOk($res, 'shift/destroy');
    }

    // ==================== ATTENDANCE ====================

    public function test_attendance_store()
    {
        $sid = $this->seedShift(1);
        $res = $this->api()->postJson('/api/attendance', [
            'mediaAttendaceId' => 1, 'mediaOfWorkId' => 2, 'projectId' => 1,
            'latitude' => '-6.9', 'longtitude' => '107.6', 'time' => '08:00:00',
            'action' => 'clockin', 'shiftId' => $sid,
        ]);
        $this->assertFeatureOk($res, 'attendance (store)');
    }

    public function test_attendance_index()
    {
        $res = $this->api()->getJson('/api/attendance');
        $this->assertFeatureOk($res, 'attendance (index)');
    }

    public function test_attendance_summary()
    {
        $res = $this->api()->getJson('/api/attendance/summary');
        $this->assertFeatureOk($res, 'attendance/summary');
    }

    public function test_attendance_log()
    {
        $res = $this->api()->getJson('/api/attendance/log');
        $this->assertFeatureOk($res, 'attendance/log');
    }

    public function test_attendance_legacy_with_images()
    {
        $res = $this->api()->post('/api/attendances', [
            'projectId' => 1, 'latitude' => '-6.9', 'longtitude' => '107.6', 'action' => 'clockin',
            'proofOfWork' => UploadedFile::fake()->image('proof.jpg'),
            'attendances' => UploadedFile::fake()->image('att.jpg'),
        ]);
        $this->assertFeatureOk($res, 'auth/attendances (legacy)');
    }

    // ==================== MEDIA ====================

    public function test_media_store()
    {
        $res = $this->api()->post('/api/media', [
            'media' => UploadedFile::fake()->image('m.jpg'), 'type' => 'attendances',
        ]);
        $this->assertFeatureOk($res, 'media (store)');
    }

    // ==================== ROLES ====================

    public function test_roles_index()
    {
        $res = $this->api()->getJson('/api/roles');
        $this->assertFeatureOk($res, 'roles');
    }

    // ==================== EXPORT / FILES ====================

    public function test_export()
    {
        $res = $this->api()->getJson('/api/export');
        $this->assertFeatureOk($res, 'export');
    }

    public function test_files_index()
    {
        $res = $this->api()->getJson('/api/files');
        $this->assertFeatureOk($res, 'files (index)');
    }

    public function test_files_update()
    {
        \DB::table('files')->insert(['id' => 1, 'file_name' => 'a.xlsx', 'type' => 'attendance']);
        $res = $this->api()->patchJson('/api/files/1', ['file_name' => 'b.xlsx']);
        $this->assertFeatureOk($res, 'files/{id} (update)');
    }

    // ==================== ASSIGNMENTS ====================

    public function test_user_project_assign()
    {
        $staff = $this->makeUser('staff@test', 'user');
        $res = $this->api()->postJson('/api/user-project', ['user_id' => $staff->id, 'project_id' => 1]);
        $this->assertFeatureOk($res, 'user-project (assign)');
    }

    public function test_user_project_assigns()
    {
        $staff = $this->makeUser('staff2@test', 'user');
        $res = $this->api()->postJson('/api/user-project/inserts', ['user_ids' => [$staff->id], 'project_id' => 1]);
        $this->assertFeatureOk($res, 'user-project/inserts');
    }

    public function test_user_project_delete()
    {
        $staff = $this->makeUser('staff3@test', 'user');
        $this->api()->postJson('/api/user-project', ['user_id' => $staff->id, 'project_id' => 1]);
        $relId = \DB::table('user_have_project')->where('user_id', $staff->id)->value('id');
        $res = $this->api()->deleteJson("/api/user-project/$relId");
        $this->assertFeatureOk($res, 'user-project (delete)');
    }

    public function test_user_division_assign()
    {
        $staff = $this->makeUser('staff4@test', 'user');
        $res = $this->api()->postJson('/api/user-division', ['user_id' => $staff->id, 'division_id' => 1]);
        $this->assertFeatureOk($res, 'user-division (assign)');
    }

    public function test_user_division_delete()
    {
        $staff = $this->makeUser('staff5@test', 'user');
        $this->api()->postJson('/api/user-division', ['user_id' => $staff->id, 'division_id' => 1]);
        $relId = \DB::table('user_have_division')->where('user_id', $staff->id)->value('id');
        $res = $this->api()->deleteJson("/api/user-division/$relId");
        $this->assertFeatureOk($res, 'user-division (delete)');
    }
}
