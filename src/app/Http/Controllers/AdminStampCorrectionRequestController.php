<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStampCorrectionRequestController extends Controller
{
    public function show($attendanceCorrectRequestId)
    {
        $request = CorrectionRequest::with('user', 'attendance', 'detail', 'rests')
            ->findOrFail($attendanceCorrectRequestId);

        return view('admin.stamp_correction_request.detail', compact('request'));
    }

    public function approve(Request $request, $attendanceCorrectRequestId)
    {
        $correctionRequest = CorrectionRequest::with(['detail', 'rests', 'attendance'])
            ->findOrFail($attendanceCorrectRequestId);

        if ($correctionRequest->status === CorrectionRequest::STATUS_APPROVED) {
            return back()->with('error', 'この申請はすでに承認済みです。');
        }

        DB::transaction(function () use ($correctionRequest) {
            $attendance = $correctionRequest->attendance;
            $detail = $correctionRequest->detail;

            $attendance->update([
                'clock_in' => $detail->clock_in,
                'clock_out' => $detail->clock_out,
            ]);

            $attendance->rests()->delete();
            foreach ($correctionRequest->rests as $rest) {
                $attendance->rests()->create([
                    'rest_start' => $rest->rest_start,
                    'rest_end' => $rest->rest_end,
                ]);
            }

            $correctionRequest->update([
                'status' => CorrectionRequest::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()
            ->route('stamp_correction_request.list', ['status' => CorrectionRequest::STATUS_APPROVED])
            ->with('success', '申請を承認しました');
    }
}
