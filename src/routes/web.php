<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminListController;
use App\Http\Controllers\AdminApprovalController;

use Illuminate\Support\Facades\Route;

// 会員登録画面
Route::post('/register', [AuthController::class, 'register']);

// ログイン
Route::post('/login', [AuthController::class, 'login']);

// ログアウト
Route::post('/logout', [AuthController::class, 'logout']);

// 一般ユーザーの色んなルート記述予定
Route::middleware(['auth'])->group(function () {
    //　出勤登録画面
    Route::get('/attendance',[AttendanceController::class, 'attendance']);
    Route::post('/attendance/at_work',[AttendanceController::class, 'start']);
    Route::post('/attendance/at_rest',[AttendanceController::class, 'restStart']);
    Route::post('/attendance/leave_rest',[AttendanceController::class, 'restFinish']);
    Route::post('/attendance/leave_work',[AttendanceController::class, 'finish']);

    //　勤怠一覧画面
    Route::get('/attendance/list',[ListController::class, 'list'])->name('list');

    //　勤怠詳細画面
    Route::get('/attendance/detail/{id?}', [ListController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/detail/{id?}/fix', [ListController::class, 'fix'])->name('attendance.fix');
    
    //　申請一覧画面
    Route::get('/attendance/request',[ListController::class, 'request'])->name('attendance.request');
    Route::get('/attendance/approved',[ListController::class, 'approved'])->name('attendance.approved');;
});

// 管理者ログイン
Route::get('/admin/login', [AdminAuthController::class, 'showAdmin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'adminLogin']);

// ログアウト
Route::post('/admin/logout', [AdminAuthController::class, 'adminLogout']);

// 管理者の色んなルート記述予定
Route::middleware(['auth:admin'])->group(function () {
    // 勤怠一覧
    Route::get('/admin/attendance',[AdminListController::class, 'adminList'])->name('admin.attendance');

    // 勤怠詳細
    Route::get('/admin/attendance/{id?}',[AdminListController::class, 'adminDetail'])->name('admin.detail');
    Route::post('/admin/attendance/{id?}/fix',[AdminListController::class, 'adminFix']);

    // スタッフ一覧画面
    Route::get('/admin/users',[AdminListController::class, 'staff']);

    // スタッフ別勤怠一覧画面
    Route::get('/admin/users/{user_id}',[AdminListController::class, 'checkStaff'])->name('admin.user.detail');

    // 申請一覧画面
    Route::get('/admin/requests',[AdminApprovalController::class, 'adminRequest'])->name('admin.requests');
    Route::get('/admin/requests/approved',[AdminApprovalController::class, 'adminApproved'])->name('admin.approved');

    //　修正申請承認画面
    Route::get('/admin/requests/{id}',[AdminApprovalController::class, 'showApproval'])->name('show.approval');
    Route::post('/admin/requests/{id}/approve',[AdminApprovalController::class, 'adminApprove'])->name('admin.approve');
});

