<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectionRequest;
use App\Models\CorrectionRequestRest;
use App\Models\CorrectionRequestDetail;
use App\Models\User;


class AdminStampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 0);

        $requests = CorrectionRequest::with('user', 'detail', 'rests')
        ->where('status', $status)
        ->orderBy('created_at', 'desc')
        ->get();

        return view('admin_stamp_correction_request_list', compact('requests', 'status'));
    }

    public function show($id)
    {
        $request = CorrectionRequest::with('user', 'attendance','detail', 'rests')->findOrFail($id);
        return view('admin_stamp_correction_request_detail', compact('request'));
    }

    public function approve(Request $request, $id)
    {
        $correctionRequest = CorrectionRequest::with(['detail', 'rests'])->findOrFail($id);

        DB::transaction(function () use ($correctionRequest){
            //Attendancesテーブルを更新
            $attendance = $correctionRequest->attendance;
            $attendance->update([
                'clock_in' => $correctionRequest->detail->clock_in,
                'clock_out' => $correctionRequest->detail->clock_out,
                'date' => $correctionRequest->detail->date,
            ]);

            //Restsテーブルを更新
            $attendance->rests()->delete();
            foreach ($correctionRequest->rests as $rest) {
                $attendance->rests()->create([
                    'start_time' => $rest->start_time,
                    'end_time' => $rest->end_time,
                ]);
            }

            //CorrectionRequestsテーブルのステータスを承認済みに更新
            $correctionRequest->update([
                'status' => 1,
                'approved_by' => auth()->id,
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('admin.stamp_correction_request.list', ['status' => 1])->with('success', '申請を承認しました');
    }


}
