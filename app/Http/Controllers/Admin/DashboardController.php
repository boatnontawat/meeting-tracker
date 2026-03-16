<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MeetingRecord;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        
        // ดึงการตั้งค่าตรงๆ
        $startMonth = Setting::where('key', 'filter_start_month')->value('value') ?? date('Y-01');
        $endMonth = Setting::where('key', 'filter_end_month')->value('value') ?? date('Y-12');

        // สร้าง Base Query บังคับกรองเดือน
        $baseQuery = MeetingRecord::whereBetween('month_year', [$startMonth, $endMonth]);

        $totalMeetings = (clone $baseQuery)->count();
        $totalHours = (clone $baseQuery)->sum('total_hours');

        // 🌟 1. ดึงชั่วโมงการประชุม แยกตาม "หน่วยงาน" (เรียงจากมากไปน้อย)
        $departmentData = (clone $baseQuery)
            ->join('users', 'meeting_records.user_id', '=', 'users.id')
            ->selectRaw('users.department, SUM(meeting_records.total_hours) as sum_hours')
            ->whereNotNull('users.department')
            ->where('users.department', '!=', '')
            ->groupBy('users.department')
            ->orderByDesc('sum_hours')
            ->get();

        // 🌟 2. ดึงชั่วโมงการประชุม แยกตาม "วิชาชีพ/ตำแหน่ง" (เรียงจากมากไปน้อย)
        $positionData = (clone $baseQuery)
            ->join('users', 'meeting_records.user_id', '=', 'users.id')
            ->selectRaw('users.position, SUM(meeting_records.total_hours) as sum_hours')
            ->whereNotNull('users.position')
            ->where('users.position', '!=', '')
            ->groupBy('users.position')
            ->orderByDesc('sum_hours')
            ->get();

        // 🌟 3. ข้อมูลกราฟโดนัท (สัดส่วนประเภทการประชุม) ยังคงไว้เหมือนเดิม
        $typeData = (clone $baseQuery)
            ->selectRaw('meeting_type, COUNT(id) as count')
            ->groupBy('meeting_type')
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalMeetings', 'totalHours', 
            'departmentData', 'positionData', 'typeData'
        ));
    }
}