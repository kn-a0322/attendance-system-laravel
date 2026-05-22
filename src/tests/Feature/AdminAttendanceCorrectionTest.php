<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\CorrectionRequestDetail;
use Carbon\Carbon;

class AdminAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /** 承認待ちタブに、全ユーザーの未承認申請が表示される */
    public function test_pending_correction_requests_are_shown_for_all_users()
    {
        $admin = $this->createAdmin();
        $userA = User::factory()->create(['name' => '申請者A']);
        $userB = User::factory()->create(['name' => '申請者B', 'email' => 'user-b@example.com']);

        $this->createPendingCorrection($userA, '2026-05-10', '申請理由A');
        $this->createPendingCorrection($userB, '2026-05-11', '申請理由B');

        $response = $this->actingAs($admin)->get(
            route('stamp_correction_request.list', ['status' => CorrectionRequest::STATUS_PENDING])
        );

        $response->assertOk();
        $response->assertSee('承認待ち', false);
        $response->assertSee('申請者A', false);
        $response->assertSee('申請者B', false);
        $response->assertSee('申請理由A', false);
        $response->assertSee('申請理由B', false);
    }

    /** 承認済みタブに、承認済みの申請が表示される */
    public function test_approved_correction_requests_are_shown()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => '承認済みユーザー']);

        $request = $this->createPendingCorrection($user, '2026-05-15', '承認済み確認用');
        $request->update([
            'status' => CorrectionRequest::STATUS_APPROVED,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(
            route('stamp_correction_request.list', ['status' => CorrectionRequest::STATUS_APPROVED])
        );

        $response->assertOk();
        $response->assertSee('承認済み', false);
        $response->assertSee('承認済みユーザー', false);
        $response->assertSee('承認済み確認用', false);
    }

    /** 修正申請の詳細画面に、申請内容が表示される */
    public function test_correction_request_detail_shows_application_content()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => '詳細表示ユーザー']);

        $correction = $this->createPendingCorrection(
            $user,
            '2026-05-12',
            '電車遅延のため修正',
            '08:50',
            '18:10'
        );

        $response = $this->actingAs($admin)->get(
            route('stamp_correction_request.approve', ['attendance_correct_request_id' => $correction->id])
        );

        $response->assertOk();
        $response->assertSee('勤怠詳細', false);
        $response->assertSee('詳細表示ユーザー', false);
        $response->assertSee('08:50', false);
        $response->assertSee('18:10', false);
        $response->assertSee('電車遅延のため修正', false);
        $response->assertSee('承認', false);
    }

    /** 「承認」で申請が承認され、勤怠情報が更新される */
    public function test_approve_button_updates_attendance_and_marks_request_approved()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-20',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $correction = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => CorrectionRequest::STATUS_PENDING,
        ]);

        CorrectionRequestDetail::create([
            'correction_request_id' => $correction->id,
            'clock_in' => Carbon::parse('2026-05-20 08:45:00'),
            'clock_out' => Carbon::parse('2026-05-20 18:15:00'),
            'remark' => '承認テスト用',
        ]);

        $this->actingAs($admin)->get(route('stamp_correction_request.approve', ['attendance_correct_request_id' => $correction->id]));

        $response = $this->patch(
            route('stamp_correction_request.approve.update', ['attendance_correct_request_id' => $correction->id])
        );

        $response->assertRedirect(route('stamp_correction_request.list', ['status' => CorrectionRequest::STATUS_APPROVED]));

        $this->assertDatabaseHas('correction_requests', [
            'id' => $correction->id,
            'status' => CorrectionRequest::STATUS_APPROVED,
            'approved_by' => $admin->id,
        ]);

        $attendance->refresh();
        $this->assertSame('08:45', Carbon::parse($attendance->clock_in)->format('H:i'));
        $this->assertSame('18:15', Carbon::parse($attendance->clock_out)->format('H:i'));
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 1,
            'email_verified_at' => now(),
        ]);
    }

    /** 承認待ちの修正申請を作成*/
    private function createPendingCorrection(
        User $user,
        string $date,
        string $remark,
        string $clockIn = '09:00',
        string $clockOut = '18:00'
    ): CorrectionRequest {
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);

        $correction = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => CorrectionRequest::STATUS_PENDING,
        ]);

        CorrectionRequestDetail::create([
            'correction_request_id' => $correction->id,
            'clock_in' => Carbon::parse("{$date} {$clockIn}:00"),
            'clock_out' => Carbon::parse("{$date} {$clockOut}:00"),
            'remark' => $remark,
        ]);

        return $correction;
    }
}
