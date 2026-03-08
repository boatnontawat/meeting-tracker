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

        // ข้อมูลกราฟแท่ง
        $rawChartData = (clone $baseQuery)
            ->selectRaw('month_year, SUM(total_hours) as sum_hours')
            ->groupBy('month_year')
            ->pluck('sum_hours', 'month_year');

        // สร้างแกน X จากช่วงเดือนทั้งหมด
        $months = [];
        $currentMonth = Carbon::parse($startMonth)->startOfMonth();
        $endDate = Carbon::parse($endMonth)->startOfMonth();
        
        while ($currentMonth->lte($endDate)) {
            $months[] = $currentMonth->format('Y-m'); 
            $currentMonth->addMonth();
        }

        $chartData = [];
        foreach ($months as $month) {
            $chartData[] = [
                'month_year' => $month,
                'sum_hours' => $rawChartData[$month] ?? 0
            ];
        }

        // ข้อมูลกราฟโดนัท (สัดส่วนการประชุม)
        $typeData = (clone $baseQuery)
            ->selectRaw('meeting_type, COUNT(id) as count')
            ->groupBy('meeting_type')
            ->get();

        return view('admin.dashboard', compact('totalUsers', 'totalMeetings', 'totalHours', 'chartData', 'typeData'));
    }
}