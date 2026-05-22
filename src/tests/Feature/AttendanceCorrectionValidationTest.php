<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesFinishedAttendance;
use Tests\TestCase;

class AttendanceCorrectionValidationTest extends TestCase
{
    use CreatesFinishedAttendance;
    use RefreshDatabase;

    public function test_validation_shows_error_when_clock_in_is_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = $this->makeFinishedAttendance($user, '2026-05-10');

        $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        $response = $this->post(route('attendance.correction.store', $attendance->id), [
            'date' => '2026-05-10',
            'clock_in' => '10:00',
            'clock_out' => '09:00',
            'rest_start' => [''],
            'rest_end' => [''],
            'remark' => '備考は入力必須です',
        ]);

        $response->assertSessionHasErrors('clock_out');
        $this->assertSame(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->get('clock_out')[0]
        );
    }

    public function test_validation_shows_error_when_break_start_is_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = $this->makeFinishedAttendance($user, '2026-05-10');

        $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        $response = $this->post(route('attendance.correction.store', $attendance->id), [
            'date' => '2026-05-10',
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

    public function test_validation_shows_error_when_break_end_is_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = $this->makeFinishedAttendance($user, '2026-05-10');

        $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        $response = $this->post(route('attendance.correction.store', $attendance->id), [
            'date' => '2026-05-10',
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

    public function test_validation_shows_error_when_remark_is_empty()
    {
        $user = User::factory()->create();
        $attendance = $this->makeFinishedAttendance($user, '2026-05-10');

        $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        $response = $this->post(route('attendance.correction.store', $attendance->id), [
            'date' => '2026-05-10',
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

    public function test_validation_shows_multiple_errors_at_once()
    {
        $user = User::factory()->create();
        $attendance = $this->makeFinishedAttendance($user, '2026-05-10');

        $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        $response = $this->post(route('attendance.correction.store', $attendance->id), [
            'date' => '2026-05-10',
            'clock_in' => '10:00',
            'clock_out' => '09:00',
            'rest_start' => ['19:00'],
            'rest_end' => ['19:30'],
            'remark' => '',
        ]);

        $response->assertSessionHasErrors(['remark', 'clock_out']);
        $this->assertSame(
            '備考を記入してください',
            session('errors')->get('remark')[0]
        );
        $this->assertSame(
            '出勤時間もしくは退勤時間が不適切な値です',
            session('errors')->get('clock_out')[0]
        );
    }
}
