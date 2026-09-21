<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Fase 5 auth hardening: legacy /attendances forgery (#27), register roleId escalation (#28). */
class Fase5Test extends TestCase
{
    use RefreshDatabase;

    private function roles(): void
    {
        \DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'user'],
        ]);
    }

    private function rolesOf(User $u): array
    {
        return \DB::table('role_has_users')
            ->join('roles', 'roles.id', '=', 'role_has_users.roleId')
            ->where('role_has_users.userId', $u->id)
            ->pluck('roles.name')->all();
    }

    /** #27: legacy POST /api/attendances menolak request tanpa login → tak bisa palsu kehadiran. */
    public function test_legacy_attendances_rejects_unauthenticated_and_creates_nothing()
    {
        $this->roles();

        $res = $this->postJson('/api/attendances', [
            'userId' => 999, // korban yang mau dipalsukan
            'projectId' => 1,
            'latitude' => '-6.9',
            'longtitude' => '107.6',
            'action' => 'clockin',
        ]);

        $this->assertGreaterThanOrEqual(400, $res->status(), 'harus ditolak, bukan 2xx');
        $this->assertSame(0, Attendance::count(), 'tak boleh ada baris kehadiran tercipta');
    }

    /** #28: registrasi publik mengabaikan roleId dari body — tak bisa self-escalate ke admin. */
    public function test_register_ignores_client_role_and_forces_plain_user()
    {
        $this->roles();

        $this->postJson('/api/auth/register', [
            'name' => 'Penyerang',
            'userName' => 'attacker',
            'email' => 'attacker@test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'roleId' => [1], // minta admin
        ])->assertStatus(200);

        $u = User::where('email', 'attacker@test')->firstOrFail();

        $this->assertSame(['user'], $this->rolesOf($u), 'hanya boleh peran user, bukan admin');
    }

    /** #28: tanpa roleId pun tetap dapat peran 'user'. */
    public function test_register_without_role_still_assigns_user()
    {
        $this->roles();

        $this->postJson('/api/auth/register', [
            'name' => 'Biasa',
            'userName' => 'biasa',
            'email' => 'biasa@test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertStatus(200);

        $u = User::where('email', 'biasa@test')->firstOrFail();

        $this->assertSame(['user'], $this->rolesOf($u), 'hanya boleh peran user, bukan admin');
    }
}
