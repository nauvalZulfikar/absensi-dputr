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

    // ---- progres (project-keyed, #32 extend) ----

    /** user_admin boleh input progres di project divisinya, tapi bukan project divisi lain. */
    public function test_user_admin_progres_store_scoped_to_own_project()
    {
        $this->seedRoles();
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);
        $this->makeProject(50, 1); // own
        $this->makeProject(51, 2); // foreign
        $token = auth()->login($pengawas);

        $own = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/progres/store', ['projectId' => 50, 'fisik' => 10, 'pencairan' => 5, 'date' => '2026-02-01']);
        $this->assertSame(200, $own->status());

        $foreign = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/progres/store', ['projectId' => 51, 'fisik' => 10, 'pencairan' => 5, 'date' => '2026-02-01']);
        $this->assertSame(403, $foreign->status());
    }

    /** user_admin TAK boleh ubah/hapus progres milik project divisi lain. */
    public function test_user_admin_cannot_touch_foreign_progres()
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin@test', 'admin');
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);
        $this->makeProject(60, 2); // divisi lain

        // admin bikin progres di project divisi 2
        $adminTok = auth()->login($admin);
        $created = $this->withHeader('Authorization', "Bearer $adminTok")
            ->postJson('/api/progres/store', ['projectId' => 60, 'fisik' => 10, 'pencairan' => 5, 'date' => '2026-02-01']);
        $pid = $created->json('data.id');
        $this->assertNotNull($pid);

        // pengawas divisi 1 coba sentuh
        $pengTok = auth()->login($pengawas);
        $upd = $this->withHeader('Authorization', "Bearer $pengTok")
            ->putJson("/api/progres/update/$pid", ['fisik' => 99]);
        $this->assertSame(403, $upd->status());

        $del = $this->withHeader('Authorization', "Bearer $pengTok")
            ->postJson("/api/progres/destroy/$pid");
        $this->assertSame(403, $del->status());
    }

    // ---- user-project assign (project-keyed, #32 extend) ----

    /** user_admin boleh assign user ke project divisinya, tapi bukan project divisi lain. */
    public function test_user_admin_project_assign_scoped()
    {
        $this->seedRoles();
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);
        $staff = $this->makeUser('staff@test', 'user');
        $this->makeProject(70, 1); // own
        $this->makeProject(71, 2); // foreign
        $token = auth()->login($pengawas);

        $own = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/user-project', ['user_id' => $staff->id, 'project_id' => 70]);
        $this->assertSame(200, $own->status());

        $foreign = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/user-project', ['user_id' => $staff->id, 'project_id' => 71]);
        $this->assertSame(403, $foreign->status());
        $this->assertSame(0, \DB::table('user_have_project')->where('project_id', 71)->count());
    }

    // ---- shift (project via shift_have_projects pivot, #32 extend) ----

    /** user_admin boleh bikin shift di project divisinya, tapi bukan project divisi lain. */
    public function test_shift_store_scoped_to_own_project()
    {
        $this->seedRoles();
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);
        $this->makeProject(80, 1); // own
        $this->makeProject(81, 2); // foreign
        $token = auth()->login($pengawas);

        $own = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/shift/store', ['project_id' => 80, 'timeIn' => '08:00:00', 'timeOut' => '17:00:00', 'type' => 'reguler']);
        $this->assertSame(200, $own->status());

        $foreign = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/shift/store', ['project_id' => 81, 'timeIn' => '08:00:00', 'timeOut' => '17:00:00', 'type' => 'reguler']);
        $this->assertSame(403, $foreign->status());
        $this->assertSame(0, \DB::table('shift_have_projects')->where('project_id', 81)->count());
    }

    /** user_admin TAK boleh ubah/hapus shift milik project divisi lain. */
    public function test_user_admin_cannot_touch_foreign_shift()
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin@test', 'admin');
        $pengawas = $this->makeUser('peng1@test', 'user_admin', 1);
        $this->makeProject(90, 2); // divisi lain

        // admin bikin shift di project divisi 2
        $adminTok = auth()->login($admin);
        $created = $this->withHeader('Authorization', "Bearer $adminTok")
            ->postJson('/api/shift/store', ['project_id' => 90, 'timeIn' => '08:00:00', 'timeOut' => '17:00:00', 'type' => 'reguler']);
        $sid = $created->json('data.id');
        $this->assertNotNull($sid);

        // pengawas divisi 1 coba sentuh
        $pengTok = auth()->login($pengawas);
        $upd = $this->withHeader('Authorization', "Bearer $pengTok")
            ->putJson("/api/shift/update/$sid", ['timeIn' => '09:00:00']);
        $this->assertSame(403, $upd->status());

        $del = $this->withHeader('Authorization', "Bearer $pengTok")
            ->deleteJson("/api/shift/destroy/$sid");
        $this->assertSame(403, $del->status());

        // dan tak boleh nempelin user ke shift itu
        $add = $this->withHeader('Authorization', "Bearer $pengTok")
            ->postJson('/api/shift/add-user', ['shift_id' => $sid, 'user_ids' => [$pengawas->id], 'project_ids' => [90]]);
        $this->assertSame(403, $add->status());
    }
}
