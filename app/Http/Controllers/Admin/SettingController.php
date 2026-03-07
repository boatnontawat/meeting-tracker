<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // หน้าฟอร์มตั้งค่า
    public function index()
{
    // ดึงค่าปัจจุบันมาแสดงในฟอร์ม
    $kpi_hours = Setting::where('key', 'kpi_hours')->value('value') ?? 60;
    $start_month = Setting::where('key', 'filter_start_month')->value('value') ?? '2025-10';
    $end_month = Setting::where('key', 'filter_end_month')->value('value') ?? '2026-10';

    return view('admin.settings.index', compact('kpi_hours', 'start_month', 'end_month'));
}

public function update(Request $request)
{
    $request->validate([
        'kpi_hours'          => 'required|numeric|min:1',
        'filter_start_month' => 'required|date_format:Y-m', // ตรวจสอบ format ปี-เดือน
        'filter_end_month'   => 'required|date_format:Y-m',
    ]);

    // บันทึกลงฐานข้อมูลแบบ Update หรือ Create ใหม่
    Setting::updateOrCreate(['key' => 'kpi_hours'], ['value' => $request->kpi_hours]);
    Setting::updateOrCreate(['key' => 'filter_start_month'], ['value' => $request->filter_start_month]);
    Setting::updateOrCreate(['key' => 'filter_end_month'], ['value' => $request->filter_end_month]);

    return redirect()->back()->with('success', 'บันทึกการตั้งค่าช่วงเวลาและ KPI เรียบร้อยแล้ว!');
}
}