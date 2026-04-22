<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceDetailController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])
            ->where('user_id', auth()->id())    
            ->findOrFail($id);

        return view('attendance_detail', compact('attendance'));
    }

    public function update(Request $request, $id)
    {

    }
}
