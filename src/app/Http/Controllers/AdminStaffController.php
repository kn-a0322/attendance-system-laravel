<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::where('role', 0)->get();

        return view('admin_staff_list', compact('users'));
    }
}
