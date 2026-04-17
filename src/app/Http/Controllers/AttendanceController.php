<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        /*今日の自分のレコードを取得*/
        $attendance = Attendance::today(auth()->id())->first();

        /*今日のレコードが存在しない=「出勤を押していない」＝勤務外０*/
        if (!$attendance) {
            $status = Attendance::STATUS_OUT_OF_WORK;
        } else {
            /*今日のレコードが存在する場合はstatus1~3を取得*/
            $status = $attendance->status;
        }  

        return view('attendance', compact('status'));
    }

    public function start(Request $request)
    {
        $userId = auth()->id();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');

        /*1日に１回しか押下できないバリデーション（scopeToday と同条件）*/
        if (Attendance::today($userId)->exists()) {
            return redirect()->back()->with('error', '今日はすでに出勤しています。');
        }

        /*出勤を押下したらレコードを作成*/
        Attendance::create([
            'user_id' => $userId,
            'date' => $today,
            'clock_in' => $now,
            'status' => Attendance::STATUS_WORKING, //出勤中：１
        ]);

        return redirect()->route('attendance.index')->with('success', '出勤しました。');
    }

    public function end(Request $request)
    {
        /*出勤中(status1)のレコードを取得*/
        $attendance = Attendance::today(auth()->id())
                                ->where('status', Attendance::STATUS_WORKING)
                                ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '出勤していません。');
        }

        $attendance->update([
            'clock_out' => now()->format('H:i:s'),
            'status' => Attendance::STATUS_FINISHED, //退勤：３
        ]);

        return redirect()->route('attendance.index');
    }


    public function restStart(Request $request)
    {
        $userId = auth()->id();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');

        /*出勤中(status1)のレコードを取得*/
        $attendance = Attendance::today(auth()->id())
                                ->where('status', Attendance::STATUS_WORKING)
                                ->first();
        
        /*出勤中(status1)でない場合は休憩に入れない*/
        if (!$attendance || $attendance->status !== Attendance::STATUS_WORKING) {
            return redirect()->back()->with('error', '休憩に入れる状態ではありません。');
        }

        /*休憩開始時間を作成*/
        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => $now,
        ]);

        /*休憩中(status2)に更新*/
        $attendance->update([
            'status' => Attendance::STATUS_RESTING, //休憩中：２
        ]);

        return redirect()->route('attendance.index')->with('success', '休憩を開始しました。');
    }


    public function restEnd(Request $request)
    {
        $userId = auth()->id();
        $today = now()->format('Y-m-d');
        $now = now()->format('H:i:s');

        /*まずステータスが休憩中のレコードを取得*/
        $attendance = Attendance::today(auth()->id())
                    ->where('status', Attendance::STATUS_RESTING)
                    ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '休憩を開始してから終了してください。');
        }

        /*ステータスが休憩中のレコードの中から「rest_end」が空のレコードを取得*/
        $rest = Rest::where('attendance_id', $attendance->id)
                    ->whereNull('rest_end')
                    ->first();

        if (!$rest) {
            return redirect()->back()->with('error', '休憩の記録が見つかりません。');
        }

        $rest->update([
            'rest_end' => $now,
        ]);

        /*出勤中(status1)に更新*/
        $attendance->update([
            'status' => Attendance::STATUS_WORKING, //出勤中：１
        ]);

        return redirect()->route('attendance.index')->with('success', '休憩を終了しました。');
    }

    public function list(Request $request)
    {
        /*表示する「月」を取得*/
        $month = $request->query('month', now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        /*表示する「月」の全データ、リレーションで休憩データを取得*/
        $attendances = Attendance::where('user_id', auth()->id())
        ->whereMonth('date', $currentMonth->month)
        ->whereYear('date', $currentMonth->year)
        ->orderBy('date', 'asc')/*日付順に並び替え*/
        ->with('rests')
        ->get();

        /*前月・翌月のリンクを作成*/
        $prevMonth = $currentMonth->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->addMonth()->format('Y-m');

        return view('attendance_list', compact('attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

}
