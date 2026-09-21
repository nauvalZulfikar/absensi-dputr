<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Smoke3Test extends TestCase
{
    use RefreshDatabase;

    /** F-11 (fixed): role-less user is blocked from admin endpoints (Fase 2). */
    public function test_plain_user_cannot_use_admin_endpoints()
    {
        $u = User::create(['name'=>'Staff','userName'=>'staff','email'=>'staff@example.test','password'=>bcrypt('secret123')]);
        $token = auth()->login($u); // no roles attached at all
        $h = ['Authorization' => "Bearer $token"];

        $list = $this->withHeaders($h)->postJson('/api/user/all', []);
        $div  = $this->withHeaders($h)->postJson('/api/devision/store', ['name'=>'Divisi Sisipan','description'=>'x','status'=>'active']);

        fwrite(STDERR, "\n[F-11] POST /api/user/all  as role-less user -> HTTP {$list->status()}\n");
        fwrite(STDERR, "[F-11] POST /api/devision/store as role-less user -> HTTP {$div->status()}\n");
        $this->assertSame(403, $list->status());
        $this->assertSame(403, $div->status());
    }
}
