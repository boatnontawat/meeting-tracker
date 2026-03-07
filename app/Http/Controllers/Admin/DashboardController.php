<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MeetingRecord;
use Illuminate\Http\Request;
use App\Helpers\GlobalSetting;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        
        $totalMeetings = MeetingRecord::inActivePeriod()->count();
        $totalHours = MeetingRecord::inActivePeriod()->sum('total_hours');

        // 1. ดึงข้อมูลดิบจากฐานข้อมูลมาเป็น Key-Value ก่อน (เช่น ['2026-01' => 15, '2026-02' => 20])
        $rawChartData = MeetingRecord::inActivePeriod()
            ->selectRaw('month_year, SUM(total_hours) as sum_hours')
            ->groupBy('month_year')
            ->pluck('sum_hours', 'month_year');

        // 2. ดึงช่วงวันที่ตั้งค่าไว้ และสร้างแกน X แบบบังคับ (ไม่เอาข้อมูลขยะ)
        $filter = GlobalSetting::getDateFilter();
        $months = [];
        $currentMonth = $filter['start']->copy();
        
        while ($currentMonth->lte($filter['end'])) {
            $months[] = $currentMonth->format('Y-m'); // จะได้ '2026-01', '2026-02', ...
            $currentMonth->addMonth();
        }

        // 3. เอาข้อมูลดิบมาหยอดลงในเดือนที่ถูกต้อง เดือนไหนไม่มีให้เป็น 0
        $chartData = [];
        foreach ($months as $month) {
            $chartData[] = [
                'month_year' => $month,
                'sum_hours' => $rawChartData[$month] ?? 0
            ];
        }

        $typeData = MeetingRecord::inActivePeriod()
            ->selectRaw('meeting_type, COUNT(id) as count')
            ->groupBy('meeting_type')
            ->get();

        return view('admin.dashboard', compact('totalUsers', 'totalMeetings', 'totalHours', 'chartData', 'typeData'));
    }
}