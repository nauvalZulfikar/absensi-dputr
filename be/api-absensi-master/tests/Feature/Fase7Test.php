<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 7 — horizontal ownership scoping (#32).
 * user_admin (Pengawas) hanya boleh menulis project di divisi yang ditugaskan
 * padanya; full admin lintas divisi. Slice: project store/update/destroy.
 */
class Fase7Test extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        \DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'user'],
            ['id' => 3, 'name' => 'user_admin'],
        ]);
        \DB::table('devisions')->insert([
            ['id' => 1, 'name' => 'Divisi Satu'],
            ['id' => 2, 'name' => 'Divisi Dua'],
        ]);
    }

    private function makeUser(string $email, string $roleName, ?int $divisionId = null): User
    {
        $u = User::create([
            'name' => $email, 'userName' => $email, 'email' => $email,
            'password' => bcrypt('secret123'),
        ]);
        $roleId = \DB::table('roles')->where('name', $roleName)->value('id');
        \DB::table('role_has_users')->insert(['userId' => $u->id, 'roleId' => $roleId]);
        if ($divisionId !== null) {
            \DB::table('user_have_division')->insert([
                'user_id' => $u->id, 'devision_id' => $divisionId, 'type' => 'assign',
            ]);
        }
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

    /** user_admin divisi 1 boleh ubah project di divisi 1. */
    public function test_user_admin_can_update_project_in_own_division()
    {
        $this->seedRoles();
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);
        $this->makeProject(10, 1);

        $token = auth()->login($pengawas);
        $res = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/project/update/10', ['name' => 'Diubah Sah']);

        $this->assertSame(200, $res->status());
        $this->assertSame('Diubah Sah', Project::find(10)->name);
    }

    /** user_admin divisi 1 TAK boleh ubah project di divisi 2 (403, tak berubah). */
    public function test_user_admin_cannot_update_project_in_foreign_division()
    {
        $this->seedRoles();
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);
        $this->makeProject(20, 2); // divisi lain

        $token = auth()->login($pengawas);
        $res = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/project/update/20', ['name' => 'Dibajak']);

        $this->assertSame(403, $res->status());
        $this->assertSame('Proj 20', Project::find(20)->name, 'project divisi lain tak boleh berubah');
    }

    /** user_admin TAK boleh hapus project divisi lain (403, masih ada). */
    public function test_user_admin_cannot_destroy_project_in_foreign_division()
    {
        $this->seedRoles();
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);
        $this->makeProject(30, 2);

        $token = auth()->login($pengawas);
        $res = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/project/destroy/30');

        $this->assertSame(403, $res->status());
        $this->assertNotNull(Project::find(30), 'project divisi lain tak boleh terhapus');
    }

    /** user_admin TAK boleh bikin project di divisi yang bukan miliknya. */
    public function test_user_admin_cannot_store_project_in_foreign_division()
    {
        $this->seedRoles();
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);

        $token = auth()->login($pengawas);
        $res = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/project/store', ['name' => 'Selundupan', 'devisionId' => 2]);

        $this->assertSame(403, $res->status());
        $this->assertSame(0, Project::where('devisionId', 2)->count());
    }

    /** Full admin lintas divisi: boleh ubah project divisi mana pun. */
    public function test_full_admin_can_update_any_division()
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin@test', 'admin'); // tanpa divisi
        $this->makeProject(40, 2);

        $token = auth()->login($admin);
        $res = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/project/update/40', ['name' => 'Admin Sah']);

        $this->assertSame(200, $res->status());
        $this->assertSame('Admin Sah', Project::find(40)->name);
    }

    // ---- divisi itu sendiri (#32 extend) ----

    /** user_admin boleh ubah divisinya sendiri, tapi bukan divisi lain. */
    public function test_user_admin_can_update_own_division_but_not_foreign()
    {
        $this->seedRoles();
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);
        $token = auth()->login($pengawas);

        $own = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/devision/update/1', ['name' => 'Divisi Sendiri']);
        $this->assertSame(200, $own->status());
        $this->assertSame('Divisi Sendiri', \DB::table('devisions')->where('id', 1)->value('name'));

        $foreign = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/devision/update/2', ['name' => 'Dibajak']);
        $this->assertSame(403, $foreign->status());
        $this->assertSame('Divisi Dua', \DB::table('devisions')->where('id', 2)->value('name'));
    }

    /** user_admin TAK boleh hapus divisi lain. */
    public function test_user_admin_cannot_destroy_foreign_division()
    {
        $this->seedRoles();
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);

        $token = auth()->login($pengawas);
        $res = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/devision/destroy/2');

        $this->assertSame(403, $res->status());
        $this->assertNotNull(\DB::table('devisions')->where('id', 2)->first());
    }

    // ---- assign user ke divisi (#32 extend) ----

    /** user_admin boleh masukin user ke divisinya, tapi bukan ke divisi lain. */
    public function test_user_admin_can_assign_into_own_division_only()
    {
        $this->seedRoles();
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);
        $staff = $this->makeUser('staff@test', 'user');
        $token = auth()->login($pengawas);

        $own = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/user-division', ['user_id' => $staff->id, 'division_id' => 1]);
        $this->assertSame(200, $own->status());
        $this->assertNotNull(\DB::table('user_have_division')
            ->where(['user_id' => $staff->id, 'devision_id' => 1, 'type' => 'assign'])->first());

        $foreign = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/user-division', ['user_id' => $staff->id, 'division_id' => 2]);
        $this->assertSame(403, $foreign->status());
        $this->assertNull(\DB::table('user_have_division')
            ->where(['user_id' => $staff->id, 'devision_id' => 2])->first(), 'tak boleh nyusup ke divisi lain');
    }
}
