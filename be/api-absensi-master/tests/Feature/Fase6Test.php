<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Fase 6 login hardening: recaptcha 500 (#29), user enumeration (#30). */
class Fase6Test extends TestCase
{
    use RefreshDatabase;

    private function user(string $email = 'budi@test'): User
    {
        return User::create([
            'name' => 'Budi', 'userName' => 'budi', 'email' => $email,
            'password' => bcrypt('secret123'),
        ]);
    }

    /** #29: login tanpa g-recaptcha-response tak lagi 500 (dulu TypeError param string). */
    public function test_login_without_recaptcha_token_does_not_crash()
    {
        $this->user();

        $res = $this->postJson('/api/auth/login', [
            'email' => 'budi@test', 'password' => 'secret123',
        ]);

        $this->assertNotSame(500, $res->status(), 'login tak boleh 500 karena token recaptcha kosong');
        $this->assertSame(200, $res->status());
        $this->assertNotNull($res->json('data.access_token.token'), 'harus mengembalikan token');
    }

    /** #30: email tak dikenal & password salah → respons IDENTIK (tak bisa enumerasi). */
    public function test_login_error_is_uniform_across_bad_email_and_bad_password()
    {
        $this->user('ada@test');

        $badPassword = $this->postJson('/api/auth/login', [
            'email' => 'ada@test', 'password' => 'salah', 'g-recaptcha-response' => 'x',
        ]);
        $unknownEmail = $this->postJson('/api/auth/login', [
            'email' => 'hantu@test', 'password' => 'salah', 'g-recaptcha-response' => 'x',
        ]);

        $this->assertSame($badPassword->status(), $unknownEmail->status(), 'status harus sama');
        $this->assertSame($badPassword->getContent(), $unknownEmail->getContent(), 'body harus identik');
        $this->assertStringNotContainsStringIgnoringCase('not registered', $unknownEmail->getContent());
    }

    /** #29: flag RECAPTCHA_ENFORCE=true menolak login tanpa token. */
    public function test_enforce_flag_rejects_login_without_token()
    {
        $this->user();
        $_ENV['RECAPTCHA_ENFORCE'] = 'true';
        putenv('RECAPTCHA_ENFORCE=true');

        try {
            $res = $this->postJson('/api/auth/login', [
                'email' => 'budi@test', 'password' => 'secret123',
            ]);
            $this->assertSame(422, $res->status(), 'tanpa token saat enforce=on harus 422');
        } finally {
            unset($_ENV['RECAPTCHA_ENFORCE']);
            putenv('RECAPTCHA_ENFORCE');
        }
    }
}
