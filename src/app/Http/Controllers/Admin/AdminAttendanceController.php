<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

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
        return view('attendance_detail', compact('attendance'));//bladeファイルは一般ユーザーと同様のもの
    }
}
