<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;
use App\Models\User;

class EmailVerifiedTest extends TestCase
{
    use RefreshDatabase;

    /** 会員登録後、登録したメールアドレス宛に認証メールが送信される */
    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $email = 'verify-user@example.com';

        // 1. 会員登録
        $this->post('/register', [
            'name' => '認証テストユーザー',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        // 登録時に認証メールが送られる
        Notification::assertSentTo($user, VerifyEmail::class);

        // 2. 認証メールを再送
        $this->actingAs($user)->post(route('verification.send'));

        // 登録時と再送の合計2通が送信されているか
        Notification::assertSentToTimes($user, VerifyEmail::class, 2);
    }

    /** 認証誘導画面の「認証はこちらから」がメール確認サイト（Mailpit）へリンクしている */
    public function test_verify_email_page_links_to_mail_verification_site()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertOk();
        $response->assertSee('登録していただいたメールアドレスに認証メールを送付しました。', false);
        $response->assertSee('認証はこちらから', false);
        $response->assertSee('http://localhost:8025', false);
    }

    /** メール認証を完了すると、勤怠登録画面（/attendance）へ遷移する */
    public function test_completing_email_verification_redirects_to_attendance_page()
    {
        $user = User::factory()->unverified()->create([
            'email' => 'verified-flow@example.com',
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),//有効期限60分
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        // 認証完了後は勤怠画面へ。Laravel は ?verified=1 を付けてリダイレクトする
        $response->assertRedirect('/attendance?verified=1');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }
}
