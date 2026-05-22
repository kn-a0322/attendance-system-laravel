<?php

namespace Tests\Feature;

use App\Models\CorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesFinishedAttendance;
use Tests\TestCase;

class AttendanceCorrectionListTest extends TestCase
{
    use CreatesFinishedAttendance;
    use RefreshDatabase;

    public function test_correction_request_appears_on_admin_and_user_lists()
    {
        $user = User::factory()->create(['name' => '申請テスト一郎']);
        $admin = User::factory()->create([
            'name' => '管理者',
            'role' => User::ROLE_ADMIN,
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
            'status' => CorrectionRequest::STATUS_PENDING,
        ]);

        $adminList = $this->actingAs($admin)->get(
            route('stamp_correction_request.list', ['status' => CorrectionRequest::STATUS_PENDING])
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

    public function test_approved_correction_requests_are_shown_under_approved_tab()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
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
            route('stamp_correction_request.approve.update', ['attendance_correct_request_id' => $request->id])
        )->assertRedirect(route('stamp_correction_request.list', ['status' => CorrectionRequest::STATUS_APPROVED]));

        $approvedPage = $this->actingAs($user)->get(
            route('stamp_correction_request.list', ['tab' => 'approved'])
        );
        $approvedPage->assertOk();
        $approvedPage->assertSee('承認済み', false);
        $approvedPage->assertSee($remark, false);
    }

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
}
