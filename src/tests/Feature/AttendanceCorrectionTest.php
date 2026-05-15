<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\User;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 同日で退勤時刻が出勤時刻より前（出退勤の順序が逆）のときエラーになる
     * ※仕様表は「出勤時間が不適切な値です」だが、実装は clock_out に次の文言が付く
     */
    public function test_validation_shows_error_when_clock_in_is_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = $this->makeFinishedAttendance($user, '2026-05-10');

        $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        // 同日で「退勤 < 出勤」かつ夜勤パターンでない → 不適切（例: 10:00 より前に 9:00 退勤）
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

    /** 退勤時刻より後ろに休憩開始が来るとエラー */
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

    /** 退勤時刻より後ろに休憩終了が来るとエラー */
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

    /** 備考が空だとエラー */
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

    /** 修正申請後、管理者の承認待ち一覧と本人の申請一覧に出る */
    public function test_correction_request_appears_on_admin_and_user_lists()
    {
        $user = User::factory()->create(['name' => '申請テスト一郎']);
        $admin = User::factory()->create([
            'name' => '管理者',
            'role' => 1,
            'email' => 'admin-correction@example.com',
        ]);

        $attendance = $this->makeFinishedAttendance($user, '2026-05-12');
        $remark = '打刻ミスのため修正します（管理者一覧確認用）';

        $this->actingAs($user)->get(route('attendance.detail', $attendance->id));
        $this->post(route('attendance.correction.store', $attendance->id), [
            'date' => '2026-05-12',
            'clock_in' => '08:55',
            'clock_out' => '18:05',
            'rest_start' => [''],
            'rest_end' => [''],
            'remark' => $remark,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 0,
        ]);

        $adminList = $this->actingAs($admin)->get(
            route('admin.stamp_correction_request.list', ['status' => 0])
        );
        $adminList->assertOk();
        $adminList->assertSee('申請テスト一郎', false);
        $adminList->assertSee($remark, false);

        $userList = $this->actingAs($user)->get(
            route('stamp_correction_request.list', ['tab' => 'pending'])
        );
        $userList->assertOk();
        $userList->assertSee($remark, false);
        $userList->assertSee('承認待ち', false);
    }

    /** 承認待ちの申請が複数あるとき、本人の申請一覧にすべて表示される */
    public function test_user_sees_all_own_pending_requests_on_application_list()
    {
        $user = User::factory()->create();
        $remarkA = '5月10日分の修正理由A';
        $remarkB = '5月11日分の修正理由B';

        $attendanceA = $this->makeFinishedAttendance($user, '2026-05-10');
        $this->actingAs($user)->get(route('attendance.detail', $attendanceA->id));
        $this->post(route('attendance.correction.store', $attendanceA->id), [
            'date' => '2026-05-10',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest_start' => [''],
            'rest_end' => [''],
            'remark' => $remarkA,
        ])->assertSessionHasNoErrors();

        $attendanceB = $this->makeFinishedAttendance($user, '2026-05-11');
        $this->actingAs($user)->get(route('attendance.detail', $attendanceB->id));
        $this->post(route('attendance.correction.store', $attendanceB->id), [
            'date' => '2026-05-11',
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'rest_start' => [''],
            'rest_end' => [''],
            'remark' => $remarkB,
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($user)->get(
            route('stamp_correction_request.list', ['tab' => 'pending'])
        );
        $response->assertOk();
        $response->assertSee($remarkA, false);
        $response->assertSee($remarkB, false);
    }


    /** 管理者が承認した申請は、本人の申請一覧「承認済み」に表示される */
    public function test_approved_correction_requests_are_shown_under_approved_tab()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 1,
            'email' => 'admin-approve@example.com',
        ]);
        $remark = '承認後に承認済みタブへ出るか確認';

        $attendance = $this->makeFinishedAttendance($user, '2026-05-20');
        $this->actingAs($user)->get(route('attendance.detail', $attendance->id));
        $this->post(route('attendance.correction.store', $attendance->id), [
            'date' => '2026-05-20',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest_start' => [''],
            'rest_end' => [''],
            'remark' => $remark,
        ])->assertSessionHasNoErrors();

        $request = CorrectionRequest::where('user_id', $user->id)->firstOrFail();
        $this->actingAs($admin)->patch(
            route('admin.stamp_correction_request.approve', $request->id)
        )->assertRedirect(route('admin.stamp_correction_request.list', ['status' => 1]));

        $approvedPage = $this->actingAs($user)->get(
            route('stamp_correction_request.list', ['tab' => 'approved'])
        );
        $approvedPage->assertOk();
        $approvedPage->assertSee('承認済み', false);
        $approvedPage->assertSee($remark, false);
    }

    /** 申請一覧の「詳細」を押すと、その勤怠の詳細画面へ遷移できる */
    public function test_detail_link_on_application_list_opens_attendance_detail()
    {
        $user = User::factory()->create();
        $attendance = $this->makeFinishedAttendance($user, '2026-05-15');

        $this->actingAs($user)->get(route('attendance.detail', $attendance->id));
        $this->post(route('attendance.correction.store', $attendance->id), [
            'date' => '2026-05-15',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest_start' => [''],
            'rest_end' => [''],
            'remark' => '詳細リンク確認用の備考です。',
        ])->assertSessionHasNoErrors();

        $list = $this->actingAs($user)->get(
            route('stamp_correction_request.list', ['tab' => 'pending'])
        );
        $list->assertOk();
        $list->assertSee('/attendance/detail/'.$attendance->id, false);

        $detail = $this->get(route('attendance.detail', $attendance->id));
        $detail->assertOk();
        $detail->assertSee('勤怠詳細', false);
    }

    /** テスト用: 退勤済みの勤怠を1件作る */
    private function makeFinishedAttendance(User $user, string $date): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);
    }
}
