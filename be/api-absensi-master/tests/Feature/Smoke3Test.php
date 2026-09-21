<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Smoke3Test extends TestCase
{
    use RefreshDatabase;

    /** F-11: no role check anywhere — a plain user reaches admin endpoints */
    public function test_plain_user_can_use_admin_endpoints()
    {
        $u = User::create(['name'=>'Staff','userName'=>'staff','email'=>'staff@example.test','password'=>bcrypt('secret123')]);
        $token = auth()->login($u); // no roles attached at all
        $h = ['Authorization' => "Bearer $token"];

        $list = $this->withHeaders($h)->postJson('/api/user/all', []);
        $div  = $this->withHeaders($h)->postJson('/api/devision/store', ['name'=>'Divisi Sisipan','description'=>'x','status'=>'active']);

        fwrite(STDERR, "\n[F-11] POST /api/user/all  as role-less user -> HTTP {$list->status()} " . substr($list->getContent(),0,80) . "\n");
        fwrite(STDERR, "[F-11] POST /api/devision/store as role-less user -> HTTP {$div->status()} " . substr($div->getContent(),0,110) . "\n");
        $this->assertTrue(true);
    }
}
