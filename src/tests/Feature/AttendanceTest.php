<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_current_date_is_displayed_correctly()
    {
        $user = User::factory()->create();

        //テスト内の時間を固定（Blade の now() と一致させる）
        $now = Carbon::create(2026, 5, 10, 10, 0, 0);
        $this->travelTo($now);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $expectedDateLabel = $now->format('Y年m月d日') . '(' . $weekdays[(int) $now->format('w')] . ')';
        $response->assertSee($expectedDateLabel, false);
        $response->assertSee($now->format('H:i'), false);
    }

    public function test_status_is_displayed_correctly_outside_working_hours()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('勤務外');
    }

    public function test_status_is_displayed_correctly_during_working_hours()
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

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');
    }

    public function test_status_is_displayed_correctly_during_rest()
    {
        $user = User::factory()->create();

        $now = Carbon::create(2026, 5, 10, 12, 30, 0);
        $this->travelTo($now);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'clock_in' => $now->copy()->subHours(2)->format('H:i:s'),
            'status' => Attendance::STATUS_RESTING,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩中');
    }

    public function test_status_is_displayed_correctly_after_clock_out()
    {
        $user = User::factory()->create();

        $now = Carbon::create(2026, 5, 10, 18, 0, 0);
        $this->travelTo($now);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '17:30:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤済');
    }
}
