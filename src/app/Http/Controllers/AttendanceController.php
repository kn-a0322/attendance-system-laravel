<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::with('rests')->today(auth()->id())->first();
        $status = $attendance
            ? $attendance->status
            : Attendance::STATUS_OUT_OF_WORK;

        return view('attendance.index', compact('status'));
    }

    public function clockIn(Request $request)
    {
        $userId = auth()->id();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');

        if (Attendance::today($userId)->exists()) {
            return redirect()->back()->with('error', '今日はすでに出勤しています。');
        }

        Attendance::create([
            'user_id' => $userId,
            'date' => $today,
            'clock_in' => $now,
            'status' => Attendance::STATUS_WORKING,
        ]);

        return redirect()->route('attendance.index');
    }

    public function clockOut(Request $request)
    {
        $attendance = Attendance::today(auth()->id())
            ->where('status', Attendance::STATUS_WORKING)
            ->first();

        if (! $attendance) {
            return redirect()->back()->with('error', '出勤していません。');
        }

        $attendance->update([
            'clock_out' => now()->format('H:i:s'),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        return redirect()->route('attendance.index');
    }

    public function restStart(Request $request)
    {
        $now = Carbon::now()->format('H:i:s');
        $attendance = Attendance::today(auth()->id())
            ->where('status', Attendance::STATUS_WORKING)
            ->first();

        if (! $attendance || $attendance->status !== Attendance::STATUS_WORKING) {
            return redirect()->back()->with('error', '休憩に入れる状態ではありません。');
        }

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => $now,
        ]);

        $attendance->update([
            'status' => Attendance::STATUS_RESTING,
        ]);

        return redirect()->route('attendance.index');
    }

    public function restEnd(Request $request)
    {
        $now = now()->format('H:i:s');
        $attendance = Attendance::today(auth()->id())
            ->where('status', Attendance::STATUS_RESTING)
            ->first();

        if (! $attendance) {
            return redirect()->back()->with('error', '休憩を開始してから終了してください。');
        }

        $rest = Rest::where('attendance_id', $attendance->id)
            ->whereNull('rest_end')
            ->first();

        if (! $rest) {
            return redirect()->back()->with('error', '休憩の記録が見つかりません。');
        }

        $rest->update([
            'rest_end' => $now,
        ]);

        $attendance->update([
            'status' => Attendance::STATUS_WORKING,
        ]);

        return redirect()->route('attendance.index');
    }

    public function list(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $attendances = Attendance::where('user_id', auth()->id())
            ->whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->orderBy('date', 'asc')
            ->with('rests')
            ->get();

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $calendarDays = collect();
        $startDate = $currentMonth->copy()->startOfMonth();

        for ($d = 0; $d < $currentMonth->daysInMonth; $d++) {
            $calendarDays->push($startDate->copy()->addDays($d));
        }

        return view('attendance.list', compact(
            'attendances',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'calendarDays'
        ));
    }
}
