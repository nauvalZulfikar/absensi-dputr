<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/** Per-feature probes. Documents CURRENT behaviour, not desired behaviour. */
class MatrixTest extends TestCase
{
    use RefreshDatabase;

    private function log(string $id, string $msg): void
    {
        fwrite(STDERR, "\n[$id] $msg\n");
    }

    private function user(string $name = 'Budi', string $email = 'budi@example.test'): User
    {
        return User::create([
            'name' => $name, 'userName' => strtolower($name),
            'email' => $email, 'password' => bcrypt('secret123'),
        ]);
    }

    private function auth(User $u = null): array
    {
        $u = $u ?: $this->user();
        return ['Authorization' => 'Bearer ' . auth()->login($u)];
    }

    private function fixtures(): void
    {
        \DB::table('roles')->insert([['id' => 1, 'name' => 'admin'], ['id' => 2, 'name' => 'staff']]);
        \DB::table('devisions')->insert([['id' => 1, 'name' => 'Divisi A', 'slug' => 'divisi-a', 'status' => 'draft']]);
        \DB::table('projects')->insert([[
            'id' => 1, 'devisionId' => 1, 'userId' => 1, 'projectNo' => 'P-1', 'name' => 'Proyek Satu',
            'slug' => 'proyek-satu', 'startdate' => '2026-01-01', 'targetdate' => '2026-12-31', 'cost' => 0,
            'status' => 'active', 'rowStatus' => 1, 'address' => 'Bandung', 'latitude' => '-6.9', 'longtitude' => '107.6',
        ]]);
        \DB::table('medias')->insert([['id' => 1, 'url' => 'http://x/1.jpg', 'type' => 'attendances']]);
    }

    // ---------- DEVISION ----------
    public function test_M01_devision_store_without_assign_list()
    {
        $this->fixtures();
        $res = $this->withHeaders($this->auth())->postJson('/api/devision/store', ['name' => 'Divisi B']);
        $this->log('M-01', "POST devision/store tanpa usersIdsAssignTo -> HTTP {$res->status()} " . substr($res->getContent(), 0, 200)
            . " | rows=" . \DB::table('devisions')->count());
        $this->assertTrue(true);
    }

    public function test_M02_devision_update_without_name_kills_slug()
    {
        $this->fixtures();
        $res = $this->withHeaders($this->auth())->putJson('/api/devision/update/1', ['description' => 'ganti deskripsi doang']);
        $row = \DB::table('devisions')->find(1);
        $this->log('M-02', "PUT devision/update/1 hanya description -> HTTP {$res->status()} | name='{$row->name}' slug='{$row->slug}'");
        $this->assertTrue(true);
    }

    public function test_M03_any_user_can_delete_any_devision()
    {
        $this->fixtures();
        $outsider = $this->user('Orang Lain', 'lain@example.test');
        $res = $this->withHeaders($this->auth($outsider))->postJson('/api/devision/destroy/1');
        $this->log('M-03', "POST devision/destroy/1 oleh user tanpa role -> HTTP {$res->status()} | sisa divisi="
            . \DB::table('devisions')->count());
        $this->assertTrue(true);
    }

    // ---------- PROJECT ----------
    public function test_M04_project_store_drops_longitude()
    {
        $this->fixtures();
        $res = $this->withHeaders($this->auth())->postJson('/api/project/store', [
            'devisionId' => 1, 'name' => 'Proyek Baru', 'projectNo' => 'P-2',
            'startdate' => '2026-02-01', 'targetdate' => '2026-06-01', 'cost' => 1000,
            'address' => 'Soreang', 'latitude' => '-7.01', 'longitude' => '107.52',
        ]);
        $row = \DB::table('projects')->orderByDesc('id')->first();
        $this->log('M-04', "POST project/store latitude=-7.01 longitude=107.52 -> HTTP {$res->status()} | tersimpan lat={$row->latitude} lng=" . var_export($row->longtitude, true));
        $this->assertTrue(true);
    }

    public function test_M05_project_update_without_name_kills_slug()
    {
        $this->fixtures();
        $res = $this->withHeaders($this->auth())->putJson('/api/project/update/1', ['cost' => 5000]);
        $row = \DB::table('projects')->find(1);
        $this->log('M-05', "PUT project/update/1 hanya cost -> HTTP {$res->status()} | name='{$row->name}' slug='{$row->slug}'");
        $this->assertTrue(true);
    }

    public function test_M06_any_user_can_delete_any_project()
    {
        $this->fixtures();
        $outsider = $this->user('Orang Lain', 'lain@example.test');
        $res = $this->withHeaders($this->auth($outsider))->postJson('/api/project/destroy/1');
        $this->log('M-06', "POST project/destroy/1 oleh user asing -> HTTP {$res->status()} | sisa proyek=" . \DB::table('projects')->count());
        $this->assertTrue(true);
    }

    public function test_M07_detail_project_with_bad_id()
    {
        $this->fixtures();
        $res = $this->withHeaders($this->auth())->getJson('/api/project/detail-project?projectId=999999');
        $this->log('M-07', "GET project/detail-project?projectId=999999 -> HTTP {$res->status()} " . substr($res->getContent(), 0, 160));
        $this->assertTrue(true);
    }

    // ---------- PROGRES ----------
    public function test_M08_progres_store_on_someone_elses_project()
    {
        $this->fixtures();
        $outsider = $this->user('Orang Lain', 'lain@example.test');
        $res = $this->withHeaders($this->auth($outsider))->postJson('/api/progres/store', [
            'projectId' => 1, 'fisik' => 99, 'pencairan' => 99, 'date' => '2026-09-20',
        ]);
        $this->log('M-08', "POST progres/store ke proyek orang lain -> HTTP {$res->status()} | rows=" . \DB::table('progres')->count());
        $this->assertTrue(true);
    }

    public function test_M09_progres_destroy_nonexistent_reports_success()
    {
        $this->fixtures();
        $res = $this->withHeaders($this->auth())->postJson('/api/progres/destroy/424242');
        $this->log('M-09', "POST progres/destroy/424242 (id ga ada) -> HTTP {$res->status()} " . substr($res->getContent(), 0, 160));
        $this->assertTrue(true);
    }

    // ---------- SHIFT ----------
    public function test_M10_deleting_shift_deletes_the_project()
    {
        $this->fixtures();
        $shift = Shift::create(['projectId' => 1, 'userId' => 1, 'timeIn' => '08:00:00', 'timeOut' => '17:00:00']);
        $before = \DB::table('projects')->count();
        $res = $this->withHeaders($this->auth())->deleteJson("/api/shift/destroy/{$shift->id}");
        $after = \DB::table('projects')->count();
        $this->log('M-10', "DELETE shift/destroy/{$shift->id} -> HTTP {$res->status()} | proyek sebelum={$before} sesudah={$after}"
            . " | shift kehapus? " . var_export(\DB::table('shifts')->count() === 0, true)
            . " | body=" . substr($res->getContent(), 0, 260));
        $this->assertTrue(true);
    }

    public function test_M11_show2_is_hardcoded()
    {
        $this->fixtures();
        $res = $this->withHeaders($this->auth())->postJson('/api/shift/show2', ['projectIds' => [1], 'userIds' => [1]]);
        $this->log('M-11', "POST shift/show2 -> HTTP {$res->status()} " . substr($res->getContent(), 0, 200));
        $this->assertTrue(true);
    }

    public function test_M12_shift_store_ignores_status_and_extra_projects()
    {
        $this->fixtures();
        $res = $this->withHeaders($this->auth())->postJson('/api/shift/store', [
            'timeIn' => '08:00:00', 'timeOut' => '17:00:00', 'type' => 'pagi',
            'startdate' => '2026-01-01', 'targetdate' => '2026-12-31',
            'project_id' => 1, 'project_ids' => [1], 'status' => 'active',
        ]);
        $row = \DB::table('shifts')->orderByDesc('id')->first();
        $this->log('M-12', "POST shift/store status=active -> HTTP {$res->status()} | tersimpan status=" . var_export($row->status ?? null, true)
            . " projectId=" . var_export($row->projectId ?? null, true)
            . " relasi shift_have_projects=" . \DB::table('shift_have_projects')->count());
        $this->assertTrue(true);
    }

    // ---------- USER ----------
    public function test_M13_user_store_skips_validation()
    {
        $this->fixtures();
        $res = $this->withHeaders($this->auth())->postJson('/api/user', []); // no email, no name, nothing
        $this->log('M-13', "POST user/ dengan body KOSONG -> HTTP {$res->status()} " . substr($res->getContent(), 0, 200)
            . " | jumlah user=" . \DB::table('users')->count());
        $this->assertTrue(true);
    }

    public function test_M14_update_profile_changes_password_and_reactivates()
    {
        $this->fixtures();
        $u = $this->user('Nonaktif', 'off@example.test');
        \DB::table('users')->where('id', $u->id)->update(['status' => 'inactive']);
        $res = $this->withHeaders($this->auth($u))->postJson('/api/user/profile', ['password' => 'passwordbaru']);
        $fresh = \DB::table('users')->find($u->id);
        $this->log('M-14', "POST user/profile password=passwordbaru (tanpa password lama) -> HTTP {$res->status()}"
            . " | status '{$fresh->status}' | password lama masih valid? " . var_export(\Hash::check('secret123', $fresh->password), true)
            . " | password baru valid? " . var_export(\Hash::check('passwordbaru', $fresh->password), true));
        $this->assertTrue(true);
    }

    // ---------- ASSIGNMENT ----------
    public function test_M15_any_user_can_assign_anyone_to_any_project()
    {
        $this->fixtures();
        $victim = $this->user('Korban', 'korban@example.test');
        $outsider = $this->user('Orang Lain', 'lain@example.test');
        $res = $this->withHeaders($this->auth($outsider))->postJson('/api/user-project', [
            'user_id' => $victim->id, 'project_id' => 1,
        ]);
        $this->log('M-15', "POST user-project (assign user lain) -> HTTP {$res->status()} | rows=" . \DB::table('user_have_project')->count());
        $this->assertTrue(true);
    }

    public function test_M16_bulk_assign_swallows_errors()
    {
        $this->fixtures();
        $res = $this->withHeaders($this->auth())->postJson('/api/user-project/inserts', [
            'user_ids' => [999999], 'project_id' => 1, // user id ga ada -> FK error
        ]);
        $this->log('M-16', "POST user-project/inserts user_ids=[999999] -> HTTP {$res->status()} BODY='" . $res->getContent()
            . "' | rows=" . \DB::table('user_have_project')->count());
        $this->assertTrue(true);
    }

    // ---------- MEDIA ----------
    public function test_M17_media_upload_filename_collision()
    {
        $this->fixtures();
        $h = $this->auth();
        $a = $this->withHeaders($h)->post('/api/media', ['media' => UploadedFile::fake()->image('a.jpg'), 'type' => 'attendances']);
        $b = $this->withHeaders($h)->post('/api/media', ['media' => UploadedFile::fake()->image('b.jpg'), 'type' => 'attendances']);
        $urls = \DB::table('medias')->whereIn('id', [2, 3])->pluck('url')->all();
        $this->log('M-17', "2x POST /api/media dalam detik yang sama -> " . json_encode($urls)
            . " | sama? " . var_export(count($urls) === 2 && $urls[0] === $urls[1], true));
        @array_map('unlink', glob(public_path('media/*.jpg')));
        $this->assertTrue(true);
    }

    public function test_M19_project_update_ignores_longitude_key()
    {
        $this->fixtures();
        $res = $this->withHeaders($this->auth())->putJson('/api/project/update/1', ['name' => 'Proyek Satu', 'longitude' => '999.9']);
        $row = \DB::table('projects')->find(1);
        $this->log('M-19', "PUT project/update/1 longitude=999.9 (key yang dipakai waktu CREATE) -> HTTP {$res->status()} | tersimpan lng={$row->longtitude}");
        $this->assertTrue(true);
    }

    // ---------- BASELINE (yang harusnya jalan) ----------
    public function test_M18_roles_profile_and_attendance_reads()
    {
        $this->fixtures();
        $h = $this->auth();
        foreach ([['GET', '/api/roles'], ['GET', '/api/profile/me'], ['GET', '/api/profile'],
                  ['GET', '/api/attendance'], ['GET', '/api/attendance/summary'], ['GET', '/api/attendance/log'],
                  ['GET', '/api/user/summary'], ['GET', '/api/files'], ['GET', '/api/project/global'],
                  ['POST', '/api/user/all'], ['POST', '/api/user/selected'], ['POST', '/api/devision'],
                  ['POST', '/api/project'], ['POST', '/api/progres'], ['POST', '/api/shift']] as [$m, $u]) {
            $res = $m === 'GET' ? $this->withHeaders($h)->getJson($u) : $this->withHeaders($h)->postJson($u, []);
            $body = substr(preg_replace('/\s+/', ' ', $res->getContent()), 0, 90);
            $this->log('M-18', str_pad("$m $u", 30) . " -> HTTP {$res->status()} :: {$body}");
        }
        $this->assertTrue(true);
    }
}
