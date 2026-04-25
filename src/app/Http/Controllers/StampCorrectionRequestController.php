<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    //
    public function index(Request $request)
    {
        $user = Auth::user();

        //0:承認待ちを取得
        $pendingRequests = CorrectionRequest::where('user_id', $user->id)
        ->where('status', 0)
        ->with(['user', 'attendance', 'detail'])
        ->get();

        //1:承認済みを取得
        $approvedRequests = CorrectionRequest::where('user_id', $user->id)
        ->where('status', 1)
        ->with(['user', 'attendance', 'detail'])
        ->get();
        
        return view('stamp_correction_request_list', compact('pendingRequests', 'approvedRequests'));
    }
}
