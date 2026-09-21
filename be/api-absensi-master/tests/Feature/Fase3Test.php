<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Fase 3 correctness fixes: late-calc, longitude key, profile {id}+name. */
class Fase3Test extends TestCase
{
    use RefreshDatabase;

    private function roles(): void
    {
        \DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'user'],
        ]);
    }

    private function make(string $email, ?int $roleId): User
    {
        $u = User::create(['name' => $email, 'userName' => $email, 'email' => $email, 'password' => bcrypt('secret123')]);
        if ($roleId !== null) {
            \DB::table('role_has_users')->insert(['userId' => $u->id, 'roleId' => $roleId]);
        }
        return $u;
    }

    private function bearer(User $u): array
    {
        return ['Authorization' => 'Bearer ' . auth()->login($u)];
    }

    private function fixtures(): int
    {
        \DB::table('devisions')->insert(['id' => 1, 'name' => 'A', 'slug' => 'a', 'status' => 'draft']);
        \DB::table('projects')->insert([
            'id' => 1, 'devisionId' => 1, 'userId' => 1, 'projectNo' => 'P-1', 'name' => 'Satu', 'slug' => 'satu',
            'startdate' => '2026-01-01', 'targetdate' => '2026-12-31', 'cost' => 0, 'status' => 'active',
            'rowStatus' => 1, 'address' => 'Bandung', 'latitude' => '-6.9', 'longtitude' => '107.6',
        ]);
        \DB::table('medias')->insert(['id' => 1, 'url' => 'http://x/1.jpg', 'type' => 'attendances']);
        return Shift::create(['projectId' => 1, 'userId' => 1, 'timeIn' => '08:00:00', 'timeOut' => '17:00:00'])->id;
    }

    private function clockin(User $u, int $shiftId, string $time): string
    {
        $this->withHeaders($this->bearer($u))->postJson('/api/attendance', [
            'mediaAttendaceId' => 1, 'mediaOfWorkId' => 1, 'latitude' => '-6.9', 'longtitude' => '107.6',
            'projectId' => 1, 'time' => $time, 'action' => 'clockin', 'shiftId' => $shiftId,
        ]);
        return Attendance::latest('id')->first()->status ?? 'NULL';
    }

    public function test_clockin_before_shift_is_on_time()
    {
        $this->roles();
        $shift = $this->fixtures();
        $u = $this->make('u@test', 2);
        $this->assertSame('on-time', $this->clockin($u, $shift, '07:00:00'));
    }

    public function test_clockin_after_shift_is_late()
    {
        $this->roles();
        $shift = $this->fixtures();
        $u = $this->make('u@test', 2);
        $this->assertSame('late', $this->clockin($u, $shift, '09:00:00'));
    }

    public function test_project_store_persists_longitude_both_spellings()
    {
        $this->roles();
        \DB::table('devisions')->insert(['id' => 1, 'name' => 'A', 'slug' => 'a', 'status' => 'draft']);
        $admin = $this->make('admin@test', 1);

        $this->withHeaders($this->bearer($admin))->postJson('/api/project/store', [
            'devisionId' => 1, 'name' => 'Correct', 'projectNo' => 'P-A', 'startdate' => '2026-01-01',
            'targetdate' => '2026-06-01', 'cost' => 1, 'address' => 'x', 'latitude' => '-7', 'longitude' => '107.52',
        ]);
        $this->assertSame('107.52', \DB::table('projects')->where('projectNo', 'P-A')->value('longtitude'));

        $this->withHeaders($this->bearer($admin))->postJson('/api/project/store', [
            'devisionId' => 1, 'name' => 'Typo', 'projectNo' => 'P-B', 'startdate' => '2026-01-01',
            'targetdate' => '2026-06-01', 'cost' => 1, 'address' => 'x', 'latitude' => '-7', 'longtitude' => '108.1',
        ]);
        $this->assertSame('108.1', \DB::table('projects')->where('projectNo', 'P-B')->value('longtitude'));
    }

    public function test_profile_update_sets_name_and_stays_on_self_for_non_admin()
    {
        $this->roles();
        $admin = $this->make('admin@test', 1);
        $staf  = $this->make('staf@test', null);
        \DB::table('profiles')->insert([
            ['userId' => $admin->id, 'name' => 'Admin Asli'],
            ['userId' => $staf->id, 'name' => 'Staf Asli'],
        ]);

        // staf targetkan {id}=admin, tapi harus kepaksa ke profil sendiri
        $this->withHeaders($this->bearer($staf))->postJson("/api/profile/update/{$admin->id}", ['name' => 'DIRETAS']);

        $this->assertSame('Admin Asli', \DB::table('profiles')->where('userId', $admin->id)->value('name'));
        $this->assertSame('DIRETAS', \DB::table('profiles')->where('userId', $staf->id)->value('name'));
    }
}
