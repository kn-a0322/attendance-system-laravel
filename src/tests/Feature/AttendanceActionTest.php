<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Attendance;

class AttendanceActionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
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

    public function test_cannnot_start_attendance_if_already_started()
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

        $this->actingAs($user);//ステータスが勤務外のユーザーでログイン

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

    /** 休憩ボタンが正しく機能する（出勤中→休憩入→処理後は休憩中表示） */
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

        // 打刻画面を再取得して検証する
        $after = $this->get('/attendance');
        $after->assertSee('休憩中', false);
    }

    /** 休憩は一日に何回でもできる（休憩戻後に再度休憩入が表示される） */
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

    /** 休憩戻ボタンが正しく機能する（休憩中→休憩戻→出勤中） */
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

    /** 休憩戻は一日に何回でもできる（2回目の休憩入のあと休憩戻が表示される） */
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

    /** 休憩時刻が勤怠一覧画面で確認できる */
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

    /** 退勤ボタンが正しく機能する */
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

    /** 退勤時刻が勤怠一覧画面で確認できる */
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
