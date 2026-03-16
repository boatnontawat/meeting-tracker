<?php

use Illuminate\Support\Facades\Route;

// ของเดิม (ฝั่ง User)
use App\Http\Controllers\MeetingController; 

// ของฝั่ง Admin 
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MeetingController as AdminMeetingController; 
use App\Http\Controllers\Admin\SettingController;

// 🌟 เพิ่มบรรทัดนี้เข้ามาครับ เพื่อให้ระบบรู้จัก ReportController
use App\Http\Controllers\Admin\ReportController;


// ===== (Route ฝั่ง User ปกติของคุณ) =====
Route::get('/form', [MeetingController::class, 'create'])->name('form.create');
Route::post('/form', [MeetingController::class, 'store'])->name('form.store');
Route::get('/summary', [MeetingController::class, 'summary'])->name('form.summary');


// ===== (Route ฝั่ง Admin) =====
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class); 
    Route::resource('meetings', AdminMeetingController::class); 
    
    // หน้ารายงาน และ หน้าตั้งค่า
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/master', [ReportController::class, 'masterSummary'])->name('reports.master'); // 🌟 เพิ่มบรรทัดนี้
    Route::get('/reports/pivot', [ReportController::class, 'pivotSummary'])->name('reports.pivot');
    Route::get('/reports/department', [ReportController::class, 'departmentOverview'])->name('reports.department');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    
});