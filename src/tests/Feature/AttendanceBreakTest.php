<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_rest_button_works_correctly()
    {
        $user = User::factory()->create();
        $now = Carbon::create(2026, 5, 10, 12, 0, 0);
        $this->travelTo($now);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'clock_in' => '10:00:00',
            'status' => Attendance::STATUS_WORKING,
        ]);

        $before = $this->actingAs($user)->get('/attendance');
        $before->assertOk();
        $before->assertSee('休憩入', false);
        $before->assertSee('出勤中', false);

        $response = $this->post('/attendance/rest-start');
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_RESTING,
        ]);

        $after = $this->get('/attendance');
        $after->assertSee('休憩中', false);
    }

    public function test_can_take_multiple_breaks_per_day()
    {
        $user = User::factory()->create();
        $now = Carbon::create(2026, 5, 10, 10, 0, 0);
        $this->travelTo($now);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user)->post('/attendance/rest-start');
        $this->travelTo($now->copy()->addMinutes(30));
        $this->post('/attendance/rest-end');

        $page = $this->get('/attendance');
        $page->assertSee('休憩入', false);
        $page->assertSee('出勤中', false);
    }

    public function test_break_end_button_works_correctly()
    {
        $user = User::factory()->create();
        $now = Carbon::create(2026, 5, 10, 11, 0, 0);
        $this->travelTo($now);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user)->post('/attendance/rest-start');

        $duringRest = $this->get('/attendance');
        $duringRest->assertSee('休憩戻', false);
        $duringRest->assertSee('休憩中', false);

        $this->travelTo($now->copy()->addHour());
        $this->post('/attendance/rest-end');

        $after = $this->get('/attendance');
        $after->assertSee('出勤中', false);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING,
        ]);
    }

    public function test_can_end_break_multiple_times_per_day()
    {
        $user = User::factory()->create();
        $now = Carbon::create(2026, 5, 10, 10, 0, 0);
        $this->travelTo($now);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);
        $this->post('/attendance/rest-start');
        $this->travelTo($now->copy()->addMinutes(15));
        $this->post('/attendance/rest-end');
        $this->post('/attendance/rest-start');

        $page = $this->get('/attendance');
        $page->assertSee('休憩戻', false);
        $page->assertSee('休憩中', false);
    }

    public function test_break_times_visible_on_attendance_list()
    {
        $user = User::factory()->create();
        $now = Carbon::create(2026, 5, 10, 10, 0, 0);
        $this->travelTo($now);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user)->post('/attendance/rest-start');
        $this->travelTo(Carbon::create(2026, 5, 10, 10, 30, 0));
        $this->post('/attendance/rest-end');

        $listResponse = $this->get(route('attendance.list', ['month' => '2026-05']));
        $listResponse->assertOk();
        $listResponse->assertSee('00:30', false);
        $listResponse->assertSee('05/10', false);
    }
}
