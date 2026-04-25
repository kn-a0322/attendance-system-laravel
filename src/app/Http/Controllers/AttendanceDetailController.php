<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use App\Models\CorrectionRequestRest;
use App\Models\CorrectionRequestDetail;
use App\Models\Attendance;
use Illuminate\Http\Request;
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

        return view('attendance_detail', compact('attendance'));
    }

    public function storeCorrection(ApplicationRequest $request, $attendance_id)
    { 
        $date = $request->input('date');

        //correction_requestsテーブルにデータを保存
        $correctionRequest = CorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendance_id,
            'status' => 0,
        ]);

        //correction_request_detailsテーブルにデータを保存
        $in = Carbon::parse($date . ' ' . $request->clock_in);
        $out = Carbon::parse($date . ' ' . $request->clock_out);
       if($out->lt($in)) {
            $out->addDay();
        }

        CorrectionRequestDetail :: create([
            'correction_request_id' => $correctionRequest->id,
            'clock_in' => $in,
            'clock_out' => $out,
            'remark' => $request->remark,
        ]);

        //correction_request_restsテーブルにデータを保存
        $startTimes = $request->input('rest_start', []);
        $endTimes = $request->input('rest_end', []);

        foreach($startTimes as $index => $startTime) {
            $endTime = $endTimes[$index] ?? null;
            if(!empty($startTime) && !empty($endTime)) {
                $start = Carbon::parse($date . ' ' . $startTime);
                $end = Carbon::parse($date . ' ' . $endTime);
                if($start->lt($in)) {
                    $start->addDay();
                }
                if($end->lt($start)) {
                    $end->addDay();
                }

                $correctionRequest -> rests() -> create([
                    'rest_start' => $start,
                    'rest_end' => $end,
                ]);
            }
        }

        return redirect()
            ->route('attendance.detail', $attendance_id)
            ->with('success', '修正申請が完了しました');
    }
}