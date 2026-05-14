<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** 9-1: 登録した自分の勤怠が一覧にすべて表示される */
    public function test_all_own_attendance_records_are_displayed_on_list()
    {
        $user = User::factory()->create();
        $this->travelTo(Carbon::create(2026, 5, 15, 10, 0, 0));

        // 同じ月に2日分の勤怠を登録する
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-05',
            'clock_in' => '09:15:00',
            'clock_out' => '18:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-20',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        // 別ユーザーのデータは一覧に出てはいけない
        $other = User::factory()->create();
        Attendance::create([
            'user_id' => $other->id,
            'date' => '2026-05-10',
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => '2026-05']));

        $response->assertOk();
        $response->assertSee('05/05', false);
        $response->assertSee('09:15', false);
        $response->assertSee('05/20', false);
        $response->assertSee('10:00', false);
        // 他ユーザー分の 08:00 は自分の行に表示されない
        $response->assertDontSee('08:00', false);
    }

    /** 9-2: 勤怠一覧を開いたとき、今いる月（当月）が表示される */
    public function test_current_month_is_displayed_when_opening_attendance_list()
    {
        $user = User::factory()->create();
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertOk();
        $response->assertSee('2026/05', false);
    }

    /** 9-3: 「前月」を押すと、ひと月前の勤怠が表示される */
    public function test_previous_month_link_shows_previous_month_attendance()
    {
        $user = User::factory()->create();
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-12',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => '2026-04']));

        $response->assertOk();
        $response->assertSee('2026/04', false);
        $response->assertSee('04/12', false);
        $response->assertSee('09:00', false);
    }

    /** 9-4: 「翌月」を押すと、ひと月後の勤怠が表示される */
    public function test_next_month_link_shows_next_month_attendance()
    {
        $user = User::factory()->create();
        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-06-08',
            'clock_in' => '08:30:00',
            'clock_out' => '17:45:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => '2026-06']));

        $response->assertOk();
        $response->assertSee('2026/06', false);
        $response->assertSee('06/08', false);
        $response->assertSee('08:30', false);
    }

    /** 9-5: 「詳細」から、その日の勤怠詳細画面へ遷移できる */
    public function test_detail_link_opens_attendance_detail_for_that_day()
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

        $this->actingAs($user);
        $listResponse = $this->get(route('attendance.list', ['month' => '2026-05']));
        $listResponse->assertOk();
        $listResponse->assertSee('詳細', false);
        $listResponse->assertSee('/attendance/detail/'.$attendance->id, false);

        $detailResponse = $this->get(route('attendance.detail', $attendance->id));
        $detailResponse->assertOk();
        $detailResponse->assertSee('勤怠詳細', false);
        $detailResponse->assertSee('5月10日', false);
    }
}
