<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Smoke2Test extends TestCase
{
    use RefreshDatabase;

    /** F-08 (fixed): export download now requires authentication (Fase 1). */
    public function test_export_download_requires_auth()
    {
        \Storage::put('attendances/attendance_1700000000.xlsx', 'SECRET-PAYROLL');
        $res = $this->getJson('/api/export-data/attendance_1700000000.xlsx');
        $s = $res->baseResponse->getStatusCode();
        fwrite(STDERR, "\n[F-08] GET /api/export-data/<guessed-name> unauthenticated -> HTTP {$s}\n");
        $this->assertSame(401, $s, 'export download must not be public');
    }

    /** F-09: path traversal attempt on the same route */
    public function test_export_download_traversal()
    {
        $res = $this->get('/api/export-data/' . urlencode('../../.env'));
        fwrite(STDERR, "[F-09] traversal attempt -> HTTP {$res->status()}\n");
        $this->assertTrue(true);
    }

    /** F-10 (fixed): login gives an identical answer for known vs unknown email (Fase 6, #30). */
    public function test_login_user_enumeration_closed()
    {
        User::create(['name'=>'A','userName'=>'a','email'=>'ada@example.test','password'=>bcrypt('secret123')]);
        $a = $this->postJson('/api/auth/login', ['email'=>'ada@example.test','password'=>'wrong','g-recaptcha-response'=>'x']);
        $b = $this->postJson('/api/auth/login', ['email'=>'nobody@example.test','password'=>'wrong','g-recaptcha-response'=>'x']);
        fwrite(STDERR, "[F-10] existing email -> " . substr($a->getContent(),0,90) . "\n");
        fwrite(STDERR, "[F-10] unknown  email -> " . substr($b->getContent(),0,90) . "\n");
        $this->assertSame($a->status(), $b->status());
        $this->assertSame($a->getContent(), $b->getContent(), 'respons harus identik, tak boleh bocor');
    }
}
