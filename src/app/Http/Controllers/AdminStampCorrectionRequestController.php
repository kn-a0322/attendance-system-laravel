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
        $request = CorrectionRequest::with('user', 'detail', 'rests')->findOrFail($id);
        return view('admin_stamp_correction_request_detail', compact('request'));
    }
}
