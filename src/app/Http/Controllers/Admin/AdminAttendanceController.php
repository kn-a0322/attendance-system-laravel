<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use App\Http\Requests\AdminAttendanceUpdateRequest;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $currentDate = \Carbon\Carbon::parse($date);
        
        //その日の全ユーザーの勤怠を取得
        $attendances = Attendance::with(['user', 'rests'])
        ->whereDate('date', $date)->get();

        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        return view('admin_attendance_list', compact('attendances', 'currentDate', 'prevDate', 'nextDate'));

    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);
        return view('admin_attendance_detail', compact('attendance'));
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        if($attendance->status == 0) {
            return back()->with('error', '承認待ちのため修正はできません。');
    }

    //*勤怠情報の更新*/
    $attendance->update([
        'clock_in' => $request->clock_in,
        'clock_out' => $request->clock_out,
        'remark' => $request->remark,
    ]);

    //休憩時間の更新
    $attendance->rests()->delete();//一度既存の休憩データを削除
    $startTimes = $request->input('rest_start', []);
    $endTimes = $request->input('rest_end', []);

    foreach($startTimes as $index => $startTime) {
        $endTime = $endTimes[$index] ?? null;

        if(!empty($startTime) && !empty($endTime)) {
            $attendance->rests()->create([
                'rest_start' => $startTime,
                'rest_end' => $endTime,
            ]);
        }
    }

    return redirect()
        ->route('admin.attendance.list', ['date' => $attendance->date->format('Y-m-d')])
        ->with('success', '勤怠情報を修正しました');
    }
}
