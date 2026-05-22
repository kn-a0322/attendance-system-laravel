<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $status = $request->query('status', CorrectionRequest::STATUS_PENDING);

            $requests = CorrectionRequest::with('user', 'attendance', 'detail', 'rests')
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admin.stamp_correction_request.list', compact('requests', 'status'));
        }

        $pendingRequests = CorrectionRequest::where('user_id', $user->id)
            ->where('status', CorrectionRequest::STATUS_PENDING)
            ->with(['user', 'attendance', 'detail'])
            ->get();

        $approvedRequests = CorrectionRequest::where('user_id', $user->id)
            ->where('status', CorrectionRequest::STATUS_APPROVED)
            ->with(['user', 'attendance', 'detail'])
            ->get();

        return view('stamp_correction_request.list', compact('pendingRequests', 'approvedRequests'));
    }
}
