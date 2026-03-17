<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MeetingRecord;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 🌟 1. นับจำนวนบุคลากรทั้งหมดที่ยังปฏิบัติงานอยู่ (status = active)
        $totalUsers = User::where('status', 'active')->count();
        
        // ดึงการตั้งค่าช่วงเดือน และ "เกณฑ์ชั่วโมงการผ่าน (kpi_hours)" จาก Setting
        $startMonth = Setting::where('key', 'filter_start_month')->value('value') ?? date('Y-01');
        $endMonth = Setting::where('key', 'filter_end_month')->value('value') ?? date('Y-12');
        $kpiHours = (float)(Setting::where('key', 'kpi_hours')->value('value') ?? 60);

        // 🌟 2. ดึงข้อมูลผู้ใช้งาน (เฉพาะ active) และคำนวณชั่วโมงรวมของแต่ละคน
        $userHours = User::where('status', 'active')
            ->leftJoin('meeting_records', function($join) use ($startMonth, $endMonth) {
                $join->on('users.id', '=', 'meeting_records.user_id')
                     ->whereBetween('meeting_records.month_year', [$startMonth, $endMonth]);
            })
            ->select(
                'users.id', 
                DB::raw('COALESCE(SUM(meeting_records.total_hours), 0) as total_hours')
            )
            ->groupBy('users.id')
            ->get();

        // 🌟 3. คำนวณจำนวนคนที่ ผ่าน / ไม่ผ่าน(แต่เกิน 50%) / ไม่ผ่าน(ต่ำกว่า 50%)
        $passedCount = 0;
        $failedButOver50Count = 0;
        $failedCount = 0;

        $halfKpi = $kpiHours / 2; // คำนวณ 50% ของ KPI

        foreach ($userHours as $user) {
            $achieved = (float)$user->total_hours;

            if ($achieved >= $kpiHours) {
                $passedCount++; // ผ่านเกณฑ์ (ได้ครบตาม KPI หรือมากกว่า)
            } elseif ($achieved >= $halfKpi) {
                $failedButOver50Count++; // ไม่ผ่าน แต่ทำได้ตั้งแต่ 50% ขึ้นไป
            } else {
                $failedCount++; // ไม่ผ่าน (ทำได้น้อยกว่า 50%)
            }
        }

        // 🌟 4. ดึงข้อมูลสำหรับกราฟต่างๆ (เฉพาะ active)
        $baseQuery = MeetingRecord::whereBetween('month_year', [$startMonth, $endMonth])
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            });

        $departmentData = (clone $baseQuery)
            ->join('users', 'meeting_records.user_id', '=', 'users.id')
            ->selectRaw('users.department, SUM(meeting_records.total_hours) as sum_hours')
            ->whereNotNull('users.department')
            ->where('users.department', '!=', '')
            ->groupBy('users.department')
            ->orderByDesc('sum_hours')
            ->get();

        $typeData = (clone $baseQuery)
            ->selectRaw('meeting_type, COUNT(meeting_records.id) as count')
            ->groupBy('meeting_type')
            ->get();

        // 🌟 5. ดึงข้อมูลสำหรับตาราง Department Overview (เฉพาะ active)
        $departmentOverview = User::where('status', 'active')
            ->leftJoin('meeting_records', function($join) use ($startMonth, $endMonth) {
                $join->on('users.id', '=', 'meeting_records.user_id')
                     ->whereBetween('meeting_records.month_year', [$startMonth, $endMonth]);
            })
            ->select(
                'users.department',
                DB::raw('COUNT(DISTINCT users.id) as total_users_in_dept'),
                DB::raw('COALESCE(SUM(meeting_records.total_hours), 0) as total_hours_dept')
            )
            ->whereNotNull('users.department')
            ->where('users.department', '!=', '')
            ->groupBy('users.department')
            ->orderBy('users.department')
            ->get();

        // ส่งข้อมูล $kpiHours ไปแสดงใน View ด้วย
        return view('admin.dashboard', compact(
            'totalUsers', 'passedCount', 'failedButOver50Count', 'failedCount',
            'departmentData', 'typeData',
            'startMonth', 'endMonth', 'departmentOverview', 'kpiHours'
        ));
    }
}