<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_login_fails_if_email_is_empty()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(
            'メールアドレスを入力してください',
            session('errors')->get('email')[0]
        );
    }

    public function test_password_is_required()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);
        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(
            'パスワードを入力してください',
            session('errors')->get('password')[0]
        );
    }

    public function test_admin_login_fails_with_invalid_credentials()
    { 
        //正しいユーザーを用意
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        //アドレスは合っているが、パスワードが違う
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'incorrect-password',
        ]);
        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(
            'ログイン情報が登録されていません',
            session('errors')->get('email')[0]
        );

        $response2 = $this->post('/login', [
            'email' => 'error-email@example.com',
            'password' => 'password123',
        ]);
        $response2->assertSessionHasErrors(['email']);
        $this->assertEquals(
            'ログイン情報が登録されていません',
            session('errors')->get('email')[0]
        );
    }

    public function test_admin_login_page_is_displayed()
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertSee('管理者ログイン', false);
    }

    public function test_admin_redirects_to_admin_attendance_list_after_successful_login()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1,
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.attendance.list'));
        $this->assertAuthenticatedAs($admin);
    }
}
