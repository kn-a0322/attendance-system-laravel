<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** 10-1: 名前がログインユーザーと一致する */
    public function test_detail_shows_logged_in_user_name()
    {
        $user = User::factory()->create(['name' => 'テスト花子']);
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        $response->assertOk();
        $response->assertSee('テスト花子', false);
    }

    /** 10-2: 日付が、その勤怠の日付と一致する */
    public function test_detail_shows_selected_attendance_date()
    {
        $user = User::factory()->create();
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        $response->assertOk();
        $response->assertSee('2026年', false);
        $response->assertSee('5月10日', false);
    }

    /** 10-3: 出勤・退勤の時刻が、記録（打刻）と一致する */
    public function test_detail_shows_recorded_clock_in_and_clock_out()
    {
        $user = User::factory()->create();
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'clock_in' => '08:55:00',
            'clock_out' => '18:05:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        $response->assertOk();
        $response->assertSee('08:55', false);
        $response->assertSee('18:05', false);
    }

    /** 10-4: 休憩の時刻が、記録と一致する */
    public function test_detail_shows_recorded_break_times()
    {
        $user = User::factory()->create();
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => '12:00:00',
            'rest_end' => '12:45:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        $response->assertOk();
        $response->assertSee('12:00', false);
        $response->assertSee('12:45', false);
    }
}
