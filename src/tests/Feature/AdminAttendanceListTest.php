<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** その日の全ユーザーの勤怠が正しく表示される */
    public function test_all_users_attendance_for_the_day_is_displayed()
    {
        $admin = $this->createAdmin();
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $userA = User::factory()->create(['name' => '山田太郎']);
        $userB = User::factory()->create(['name' => '佐藤花子']);

        Attendance::create([
            'user_id' => $userA->id,
            'date' => '2026-05-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);
        Attendance::create([
            'user_id' => $userB->id,
            'date' => '2026-05-10',
            'clock_in' => '10:30:00',
            'clock_out' => '19:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        // 別の日の勤怠は表示されない
        Attendance::create([
            'user_id' => $userA->id,
            'date' => '2026-05-09',
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.list'));

        $response->assertOk();
        $response->assertSee('山田太郎', false);
        $response->assertSee('佐藤花子', false);
        $response->assertSee('09:00', false);
        $response->assertSee('10:30', false);
        $response->assertDontSee('08:00', false);
    }

    /** 勤怠一覧を開いたとき、当日の日付が表示される */
    public function test_current_date_is_displayed_when_opening_admin_attendance_list()
    {
        $admin = $this->createAdmin();
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $response = $this->actingAs($admin)->get(route('admin.attendance.list'));

        $response->assertOk();
        $response->assertSee('2026年05月10日', false);
        $response->assertSee('2026/05/10', false);
    }

    /** 「前日」で前日の勤怠が表示される */
    public function test_previous_day_link_shows_previous_day_attendance()
    {
        $admin = $this->createAdmin();
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $user = User::factory()->create(['name' => '前日確認ユーザー']);
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-09',
            'clock_in' => '08:15:00',
            'clock_out' => '17:45:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.attendance.list', ['date' => '2026-05-09'])
        );

        $response->assertOk();
        $response->assertSee('2026/05/09', false);
        $response->assertSee('前日確認ユーザー', false);
        $response->assertSee('08:15', false);
    }

    /** 「翌日」で翌日の勤怠が表示される（ */
    public function test_next_day_link_shows_next_day_attendance()
    {
        $admin = $this->createAdmin();
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $user = User::factory()->create(['name' => '翌日確認ユーザー']);
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-11',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.attendance.list', ['date' => '2026-05-11'])
        );

        $response->assertOk();
        $response->assertSee('2026/05/11', false);
        $response->assertSee('翌日確認ユーザー', false);
        $response->assertSee('09:30', false);
    }

    /** テスト用: 管理者ユーザーを1人作る */
    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 1,
            'email_verified_at' => now(),
        ]);
    }
}
