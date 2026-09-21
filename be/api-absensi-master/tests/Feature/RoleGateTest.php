<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Authorization layer (Fase 2). Unlike the Smoke/Matrix probes, these assert
 * DESIRED secure behaviour: every mutation/admin route must reject callers who
 * are unauthenticated (401) or lack an admin role (403).
 */
class RoleGateTest extends TestCase
{
    use RefreshDatabase;

    private function roles(): void
    {
        \DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'user'],
            ['id' => 3, 'name' => 'user_admin'],
        ]);
    }

    private function make(string $email, ?int $roleId): User
    {
        $u = User::create([
            'name' => $email, 'userName' => $email,
            'email' => $email, 'password' => bcrypt('secret123'),
        ]);
        if ($roleId !== null) {
            \DB::table('role_has_users')->insert(['userId' => $u->id, 'roleId' => $roleId]);
        }
        return $u;
    }

    private function bearer(User $u): array
    {
        return ['Authorization' => 'Bearer ' . auth()->login($u)];
    }

    /** Roleless authenticated user is rejected from admin routes with 403. */
    public function test_roleless_user_is_forbidden_on_admin_routes()
    {
        $this->roles();
        $staf = $this->make('staf@test', null);
        $h = $this->bearer($staf);

        foreach ([
            ['postJson', '/api/user/all', []],
            ['postJson', '/api/project/store', ['name' => 'x']],
            ['postJson', '/api/devision/store', ['name' => 'x']],
        ] as [$m, $url, $body]) {
            $res = $this->withHeaders($h)->{$m}($url, $body);
            fwrite(STDERR, "\n[gate] $url roleless -> {$res->status()}\n");
            $this->assertSame(403, $res->status(), "$url must be 403 for roleless user");
        }
    }

    /** Admin passes the gate (not 401/403). */
    public function test_admin_passes_the_gate()
    {
        $this->roles();
        $admin = $this->make('admin@test', 1);
        $res = $this->withHeaders($this->bearer($admin))->postJson('/api/user/all', []);
        fwrite(STDERR, "\n[gate] /api/user/all admin -> {$res->status()}\n");
        $this->assertNotSame(401, $res->status());
        $this->assertNotSame(403, $res->status());
    }

    /** No token -> clean 401 JSON, not a 500. */
    public function test_missing_token_returns_401()
    {
        $res = $this->postJson('/api/user/all', []);
        fwrite(STDERR, "\n[gate] /api/user/all no-token -> {$res->status()}\n");
        $this->assertSame(401, $res->status());
    }
}
