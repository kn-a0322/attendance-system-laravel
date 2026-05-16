<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** 詳細画面に、選択した勤怠の内容が表示される */
    public function test_detail_screen_shows_selected_attendance_data()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => '詳細表示テスト']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'clock_in' => '08:55:00',
            'clock_out' => '18:05:00',
            'remark' => '管理者詳細確認用の備考',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => '12:00:00',
            'rest_end' => '12:30:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.show', $attendance->id));

        $response->assertOk();
        $response->assertSee('勤怠詳細', false);
        $response->assertSee('詳細表示テスト', false);
        $response->assertSee('2026年', false);
        $response->assertSee('5月10日', false);
        $response->assertSee('08:55', false);
        $response->assertSee('18:05', false);
        $response->assertSee('12:00', false);
        $response->assertSee('12:30', false);
        $response->assertSee('管理者詳細確認用の備考', false);
    }

    /** 退勤より後ろに出勤が来るとエラー */
    public function test_validation_shows_error_when_clock_in_is_after_clock_out()
    {
        $admin = $this->createAdmin();
        $attendance = $this->makeFinishedAttendance();

        $this->actingAs($admin)->get(route('admin.attendance.show', $attendance->id));

        $response = $this->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '10:00',
            'clock_out' => '09:00',
            'rest_start' => [''],
            'rest_end' => [''],
            'remark' => '備考を入力しています。',
        ]);

        $response->assertSessionHasErrors('clock_out');
        $this->assertSame(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->get('clock_out')[0]
        );
    }

    /** 退勤より後ろに休憩開始が来るとエラー */
    public function test_validation_shows_error_when_break_start_is_after_clock_out()
    {
        $admin = $this->createAdmin();
        $attendance = $this->makeFinishedAttendance();

        $this->actingAs($admin)->get(route('admin.attendance.show', $attendance->id));

        $response = $this->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest_start' => ['19:00'],
            'rest_end' => ['19:30'],
            'remark' => '備考を入力しています。',
        ]);

        $response->assertSessionHasErrors('rest_start.0');
        $this->assertSame(
            '休憩時間が不適切な値です',
            session('errors')->get('rest_start.0')[0]
        );
    }

    /** 退勤より後ろに休憩終了が来るとエラー */
    public function test_validation_shows_error_when_break_end_is_after_clock_out()
    {
        $admin = $this->createAdmin();
        $attendance = $this->makeFinishedAttendance();

        $this->actingAs($admin)->get(route('admin.attendance.show', $attendance->id));

        $response = $this->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest_start' => ['12:00'],
            'rest_end' => ['19:00'],
            'remark' => '備考を入力しています。',
        ]);

        $response->assertSessionHasErrors('rest_end.0');
        $this->assertSame(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->get('rest_end.0')[0]
        );
    }

    /** 備考が空だとエラー */
    public function test_validation_shows_error_when_remark_is_empty()
    {
        $admin = $this->createAdmin();
        $attendance = $this->makeFinishedAttendance();

        $this->actingAs($admin)->get(route('admin.attendance.show', $attendance->id));

        $response = $this->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest_start' => [''],
            'rest_end' => [''],
            'remark' => '',
        ]);

        $response->assertSessionHasErrors('remark');
        $this->assertSame(
            '備考を記入してください',
            session('errors')->get('remark')[0]
        );
    }

    /** テスト用: 管理者ユーザーを1人作る */
    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 1,
            'email_verified_at' => now(),
        ]);
    }

    /** テスト用: 退勤済みの勤怠を1件作る */
    private function makeFinishedAttendance(): Attendance
    {
        $user = User::factory()->create();

        return Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'remark' => '備考',
            'status' => Attendance::STATUS_FINISHED,
        ]);
    }
}
