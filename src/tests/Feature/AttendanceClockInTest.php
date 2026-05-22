<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_clock_in_button_works_correctly()
    {
        $user = User::factory()->create();
        $now = Carbon::create(2026, 5, 10, 10, 0, 0);
        $this->travelTo($now);

        $response = $this->actingAs($user)->post('/attendance/start');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'clock_in' => $now->format('H:i:s'),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $response->assertRedirect('/attendance');
    }

    public function test_cannot_start_attendance_if_already_started()
    {
        $user = User::factory()->create();
        $now = Carbon::create(2026, 5, 10, 10, 0, 0);
        $this->travelTo($now);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'clock_in' => $now->format('H:i:s'),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user)->get('/attendance');

        $response = $this->post('/attendance/start');

        $response->assertRedirect('/attendance');
        $response->assertSessionHas('error', '今日はすでに出勤しています。');
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_attendance_timestamp_is_recorded_correctly_on_attendance_list()
    {
        $user = User::factory()->create();
        $now = Carbon::create(2026, 5, 10, 10, 0, 0);
        $this->travelTo($now);

        $this->actingAs($user);

        $response = $this->post('/attendance/start');
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'clock_in' => '10:00:00',
            'status' => Attendance::STATUS_WORKING,
        ]);

        $listResponse = $this->get('/attendance/list');

        $listResponse->assertStatus(200);
        $listResponse->assertSee('10:00');
        $listResponse->assertSee('出勤');
        $listResponse->assertSee('05/10');
    }
}
