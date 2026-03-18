<?php

use Illuminate\Support\Facades\Route;

// ของเดิม (ฝั่ง User)
use App\Http\Controllers\MeetingController; 

// ของฝั่ง Admin 
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MeetingController as AdminMeetingController; 
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ReportController;

// 🌟 นำเข้า Controller ใหม่สำหรับ Report แบบแชร์ (ไม่ต้อง Login)
use App\Http\Controllers\SharedReportController;


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
    Route::get('/reports/master', [ReportController::class, 'masterSummary'])->name('reports.master'); 
    Route::get('/reports/pivot', [ReportController::class, 'pivotSummary'])->name('reports.pivot');
    Route::get('/reports/department', [ReportController::class, 'departmentOverview'])->name('reports.department');
    
    // 🌟 เพิ่ม Route สำหรับให้ Admin เข้าไปคัดลอก Link ส่งให้หัวหน้าแผนก
    Route::get('/reports/links', [ReportController::class, 'generateLinks'])->name('reports.links');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    
});

// ===== (Route สำหรับแชร์ให้หัวหน้าแผนกดู โดยไม่ต้อง Login) =====
// 🌟 บังคับใช้ middleware 'signed' เพื่อป้องกันการปลอมแปลง URL (เปลี่ยนชื่อแผนกเองไม่ได้)
Route::middleware(['signed'])->prefix('shared-reports/{department}')->name('shared.reports.')->group(function () {
    Route::get('/', [SharedReportController::class, 'index'])->name('index');
    Route::get('/master', [SharedReportController::class, 'masterSummary'])->name('master');
    Route::get('/pivot', [SharedReportController::class, 'pivotSummary'])->name('pivot');
    Route::get('/department', [SharedReportController::class, 'departmentOverview'])->name('department');
});