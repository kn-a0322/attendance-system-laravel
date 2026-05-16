<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminUserListTest extends TestCase
{
    use RefreshDatabase;

    /** 一般ユーザーの名前・メールアドレスがスタッフ一覧に表示される */
    public function test_staff_list_shows_all_general_users_name_and_email()
    {
        $admin = $this->createAdmin();

        $userA = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'role' => 0,
        ]);
        $userB = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'role' => 0,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.staff.list'));

        $response->assertOk();
        $response->assertSee('スタッフ一覧', false);
        $response->assertSee('山田太郎', false);
        $response->assertSee('yamada@example.com', false);
        $response->assertSee('佐藤花子', false);
        $response->assertSee('sato@example.com', false);
        $response->assertDontSee($admin->email, false);
    }

    /** 選択したユーザーの勤怠一覧に、その月の勤怠が表示される */
    public function test_staff_attendance_list_shows_user_attendance_correctly()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => '勤怠確認ユーザー', 'role' => 0]);
        $this->travelTo(Carbon::create(2026, 5, 15, 12, 0, 0));

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'clock_in' => '09:15:00',
            'clock_out' => '18:30:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.attendance.staff.show', ['id' => $user->id, 'month' => '2026-05'])
        );

        $response->assertOk();
        $response->assertSee('勤怠確認ユーザー', false);
        $response->assertSee('2026/05', false);
        $response->assertSee('05/10', false);
        $response->assertSee('09:15', false);
        $response->assertSee('18:30', false);
    }

    /** 「前月」で前月の勤怠が表示される */
    public function test_previous_month_shows_previous_month_attendance()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['role' => 0]);
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-12',
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.attendance.staff.show', ['id' => $user->id, 'month' => '2026-04'])
        );

        $response->assertOk();
        $response->assertSee('2026/04', false);
        $response->assertSee('04/12', false);
        $response->assertSee('08:00', false);
    }

    /** 「翌月」で翌月の勤怠が表示される */
    public function test_next_month_shows_next_month_attendance()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['role' => 0]);
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-06-08',
            'clock_in' => '09:30:00',
            'clock_out' => '18:45:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.attendance.staff.show', ['id' => $user->id, 'month' => '2026-06'])
        );

        $response->assertOk();
        $response->assertSee('2026/06', false);
        $response->assertSee('06/08', false);
        $response->assertSee('09:30', false);
    }

    /** 「詳細」から、その日の管理者用勤怠詳細画面へ遷移できる */
    public function test_detail_link_opens_admin_attendance_detail_for_that_day()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['role' => 0]);
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $list = $this->actingAs($admin)->get(
            route('admin.attendance.staff.show', ['id' => $user->id, 'month' => '2026-05'])
        );
        $list->assertOk();
        $list->assertSee('詳細', false);
        $list->assertSee('/admin/attendance/'.$attendance->id, false);

        $detail = $this->get(route('admin.attendance.show', $attendance->id));
        $detail->assertOk();
        $detail->assertSee('勤怠詳細', false);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 1,
            'email_verified_at' => now(),
        ]);
    }
}
