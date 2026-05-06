<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CorrectionRequest;


class AdminStampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 0);

        $requests = CorrectionRequest::with('user', 'detail', 'rests')
        ->where('status', $status)
        ->orderBy('created_at', 'desc')
        ->get();

        return view('admin.stamp_correction_request.list', compact('requests', 'status'));
    }

    public function show($id)
    {
        $request = CorrectionRequest::with('user', 'attendance','detail', 'rests')->findOrFail($id);
        return view('admin.stamp_correction_request.detail', compact('request'));
    }

    public function approve(Request $request, $id)
    {
        $correctionRequest = CorrectionRequest::with(['detail', 'rests', 'attendance'])->findOrFail($id);

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
                'status' => 1,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('admin.stamp_correction_request.list', ['status' => 1])->with('success', '申請を承認しました');
    }


}
