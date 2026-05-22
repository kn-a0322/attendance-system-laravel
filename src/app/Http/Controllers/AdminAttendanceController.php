<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use Symfony\Component\HttpFoundation\Response;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $currentDate = Carbon::parse($date);

        $attendances = Attendance::with(['user', 'rests'])
            ->whereDate('date', $date)
            ->get();

        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        return view('admin.attendance.list', compact('attendances', 'currentDate', 'prevDate', 'nextDate'));

    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);
        return view('admin.attendance.show', compact('attendance'));
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        if ($attendance->hasPendingCorrectionRequest()) {
            return back()->with('error', '承認待ちのため修正はできません。');
        }

        $attendance->update([
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'remark' => $request->remark,
        ]);

        $attendance->rests()->delete();
        $startTimes = $request->input('rest_start', []);
        $endTimes = $request->input('rest_end', []);

        foreach ($startTimes as $index => $startTime) {
            $endTime = $endTimes[$index] ?? null;

            if (! empty($startTime) && ! empty($endTime)) {
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

    public function showStaff(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $monthParam = $request->input('month', now()->format('Y-m'));
        $currentMonth = Carbon::parse($monthParam);

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $calendarDays = [];
        $daysInMonth = $currentMonth->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $calendarDays[] = $currentMonth->copy()->day($day);
        }

        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->get();

        return view('admin.attendance.staff', compact('user', 'attendances', 'currentMonth', 'prevMonth', 'nextMonth', 'calendarDays'));
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $month = $request->input('month', now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->orderBy('date', 'asc')
            ->get();

        $fileName = $user->name . '様_' . $currentMonth->format('Y年m月') . '勤怠一覧.csv';

        $callback = function () use ($attendances) {
            $stream = fopen('php://output', 'w');

            $header = ['日付', '出勤', '退勤', '休憩', '合計'];

            mb_convert_variables('SJIS-WIN', 'UTF-8', $header);
            fputcsv($stream, $header);

            foreach ($attendances as $attendance) {
                $data = [
                    $attendance->date->format('Y-m-d'),
                    $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                    $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                    $attendance->total_rest_time,
                    $attendance->work_time,
                ];

                mb_convert_variables('SJIS-WIN', 'UTF-8', $data);
                fputcsv($stream, $data);
            }

            fclose($stream);
        };

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream($callback, Response::HTTP_OK, $headers);
    }
}
