<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminStampCorrectionRequestController;

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
//一般ユーザー用ログイン画面
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
//管理者用ログイン画面
Route::get('/admin/login', function () {
    return view('auth.admin_login');
})->name('admin.login');

//ログインしているかチェック
Route::middleware(['auth', 'verified'])->group(function () {

    //一般ユーザー,管理者共通
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
    Route::post('/attendance/rest-start', [AttendanceController::class, 'restStart'])->name('attendance.rest-start');
    Route::post('/attendance/rest-end', [AttendanceController::class, 'restEnd'])->name('attendance.rest-end');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.list');
    Route::get('/attendance/detail/{id}', [AttendanceDetailController::class, 'show'])->name('attendance.detail');
    Route::post('/attendance/detail/{id}', [AttendanceDetailController::class, 'storeCorrection'])->name('attendance.correction.store');

    

    //管理者のみアクセス可能
    Route::middleware('admin')->group(function () {
        Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
        Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
        Route::put('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
        Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list');
        Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'showStaff'])->name('admin.attendance.staff.show');
        Route::get('/admin/attendance/staff/export/{id}', [AdminAttendanceController::class, 'exportCsv'])->name('admin.attendance.staff.export');
        Route::get('/admin/stamp_correction_request/list', [AdminStampCorrectionRequestController::class, 'index'])->name('admin.stamp_correction_request.list');
        Route::get('/admin/stamp_correction_request/show/{id}', [AdminStampCorrectionRequestController::class, 'show'])->name('admin.stamp_correction_request.show');

    });
});