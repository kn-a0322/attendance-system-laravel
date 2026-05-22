<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_out_button_works_correctly()
    {
        $user = User::factory()->create();
        $now = Carbon::create(2026, 5, 10, 17, 0, 0);
        $this->travelTo($now);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'status' => Attendance::STATUS_WORKING,
        ]);

        $before = $this->actingAs($user)->get('/attendance');
        $before->assertSee('退勤', false);
        $before->assertSee('出勤中', false);

        $response = $this->post('/attendance/end');
        $response->assertRedirect('/attendance');

        $after = $this->get('/attendance');
        $after->assertSee('退勤済', false);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_FINISHED,
        ]);
    }

    public function test_clock_out_time_visible_on_attendance_list()
    {
        $user = User::factory()->create();
        $clockInTime = Carbon::create(2026, 5, 10, 9, 0, 0);
        $clockOutTime = Carbon::create(2026, 5, 10, 18, 0, 0);

        $this->travelTo($clockInTime);
        $this->actingAs($user)->post('/attendance/start');

        $this->travelTo($clockOutTime);
        $this->post('/attendance/end');

        $listResponse = $this->get(route('attendance.list', ['month' => '2026-05']));
        $listResponse->assertOk();
        $listResponse->assertSee('09:00', false);
        $listResponse->assertSee('18:00', false);
        $listResponse->assertSee('05/10', false);
    }
}
