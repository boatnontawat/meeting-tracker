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
        // 🌟 1. นับเฉพาะคนที่ยังปฏิบัติงานอยู่ (ตัดคนลาออก)
        $totalUsers = User::where('status', 'active')->count();
        
        $startMonth = Setting::where('key', 'filter_start_month')->value('value') ?? date('Y-01');
        $endMonth = Setting::where('key', 'filter_end_month')->value('value') ?? date('Y-12');

        // 🌟 2. ดึงข้อมูลการประชุมเฉพาะคนที่ยังปฏิบัติงานอยู่เท่านั้น
        $baseQuery = MeetingRecord::whereBetween('month_year', [$startMonth, $endMonth])
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            });

        $totalMeetings = (clone $baseQuery)->count();
        $totalHours = (clone $baseQuery)->sum('total_hours');

        $departmentData = (clone $baseQuery)
            ->join('users', 'meeting_records.user_id', '=', 'users.id')
            ->selectRaw('users.department, SUM(meeting_records.total_hours) as sum_hours')
            ->whereNotNull('users.department')
            ->where('users.department', '!=', '')
            ->groupBy('users.department')
            ->orderByDesc('sum_hours')
            ->get();

        $positionData = (clone $baseQuery)
            ->join('users', 'meeting_records.user_id', '=', 'users.id')
            ->selectRaw('users.position, SUM(meeting_records.total_hours) as sum_hours')
            ->whereNotNull('users.position')
            ->where('users.position', '!=', '')
            ->groupBy('users.position')
            ->orderByDesc('sum_hours')
            ->get();

        $typeData = (clone $baseQuery)
            ->selectRaw('meeting_type, COUNT(meeting_records.id) as count')
            ->groupBy('meeting_type')
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalMeetings', 'totalHours', 
            'departmentData', 'positionData', 'typeData'
        ));
    }
}