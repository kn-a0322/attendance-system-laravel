<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StampCorrectionRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


//まずログインしているかチェック
Route::middleware(['auth', 'verified'])->group(function () {

    //一般ユーザー,管理者共通
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.list');
    

    //管理者のみアクセス可能
    Route::middleware('admin')->group(function () {
        Route::get('/admin/attendance/list', [AttendanceController::class, 'index'])->name('admin.attendance.list');
        Route::get('/admin/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');
        Route::get('/admin/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('admin.stamp_correction_request.list');

    });
});