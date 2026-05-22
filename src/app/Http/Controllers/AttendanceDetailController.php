<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use App\Models\CorrectionRequestDetail;
use App\Models\Attendance;
use App\Http\Requests\ApplicationRequest;
use Carbon\Carbon;

class AttendanceDetailController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with([
            'user',
            'rests',
            'correctionRequests.user',
            'correctionRequests.detail',
            'correctionRequests.rests',
        ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('attendance.detail', compact('attendance'));
    }

    public function storeCorrection(ApplicationRequest $request, $attendanceId)
    {
        $attendance = Attendance::where('user_id', auth()->id())->findOrFail($attendanceId);

        if ($attendance->hasPendingCorrectionRequest()) {
            return back()->with('error', '承認待ちのため修正はできません。');
        }

        $date = $request->input('date');

        $correctionRequest = CorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendanceId,
            'status' => CorrectionRequest::STATUS_PENDING,
        ]);

        $clockInAt = Carbon::parse($date . ' ' . $request->clock_in);
        $clockOutAt = Carbon::parse($date . ' ' . $request->clock_out);
        if ($clockOutAt->lt($clockInAt)) {
            $clockOutAt->addDay();
        }

        CorrectionRequestDetail::create([
            'correction_request_id' => $correctionRequest->id,
            'clock_in' => $clockInAt,
            'clock_out' => $clockOutAt,
            'remark' => $request->remark,
        ]);

        $startTimes = $request->input('rest_start', []);
        $endTimes = $request->input('rest_end', []);

        foreach ($startTimes as $index => $startTime) {
            $endTime = $endTimes[$index] ?? null;
            if (! empty($startTime) && ! empty($endTime)) {
                $restStartAt = Carbon::parse($date . ' ' . $startTime);
                $restEndAt = Carbon::parse($date . ' ' . $endTime);
                if ($restStartAt->lt($clockInAt)) {
                    $restStartAt->addDay();
                }
                if ($restEndAt->lt($restStartAt)) {
                    $restEndAt->addDay();
                }

                $correctionRequest->rests()->create([
                    'rest_start' => $restStartAt,
                    'rest_end' => $restEndAt,
                ]);
            }
        }

        return redirect()
            ->route('attendance.detail', $attendanceId)
            ->with('success', '修正申請が完了しました');
    }
}
