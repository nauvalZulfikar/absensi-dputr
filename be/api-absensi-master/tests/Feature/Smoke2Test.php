<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Smoke2Test extends TestCase
{
    use RefreshDatabase;

    /** F-08: exported xlsx is downloadable with no token, filename is guessable */
    public function test_export_download_is_public()
    {
        \Storage::put('attendances/attendance_1700000000.xlsx', 'SECRET-PAYROLL');
        $res = $this->get('/api/export-data/attendance_1700000000.xlsx');
        $s = $res->baseResponse->getStatusCode(); fwrite(STDERR, "\n[F-08] GET /api/export-data/<guessed-name> unauthenticated -> HTTP {$s}, served bytes\n");
        $this->assertSame(200, $s);
    }

    /** F-09: path traversal attempt on the same route */
    public function test_export_download_traversal()
    {
        $res = $this->get('/api/export-data/' . urlencode('../../.env'));
        fwrite(STDERR, "[F-09] traversal attempt -> HTTP {$res->status()}\n");
        $this->assertTrue(true);
    }

    /** F-10: login tells you whether an email exists */
    public function test_login_user_enumeration()
    {
        User::create(['name'=>'A','userName'=>'a','email'=>'ada@example.test','password'=>bcrypt('secret123')]);
        $a = $this->postJson('/api/auth/login', ['email'=>'ada@example.test','password'=>'wrong','g-recaptcha-response'=>'x']);
        $b = $this->postJson('/api/auth/login', ['email'=>'nobody@example.test','password'=>'wrong','g-recaptcha-response'=>'x']);
        fwrite(STDERR, "[F-10] existing email -> " . substr($a->getContent(),0,90) . "\n");
        fwrite(STDERR, "[F-10] unknown  email -> " . substr($b->getContent(),0,90) . "\n");
        $this->assertTrue(true);
    }
}
