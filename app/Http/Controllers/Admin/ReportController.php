<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MeetingRecord;
use App\Models\Setting;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // ฟังก์ชันช่วยเหลือสำหรับสร้าง Query ของ User ตาม Filter
    private function getFilteredUsersQuery(Request $request)
    {
        $query = User::query();

        // กรองสถานะ ปฏิบัติงาน/ลาออก (ค่าเริ่มต้นคือ ปฏิบัติงาน)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } elseif (!$request->filled('status')) {
            $query->where('status', 'active');
        }

        // กรองหน่วยงาน
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // กรองตำแหน่ง
        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        return $query->orderBy('department');
    }

    // ฟังก์ชันช่วยเหลือสำหรับดึงข้อมูลใส่ Dropdown
    private function getFilterOptions()
    {
        return [
            'filterDepartments' => User::whereNotNull('department')->distinct()->orderBy('department')->pluck('department'),
            'filterPositions' => User::whereNotNull('position')->distinct()->orderBy('position')->pluck('position'),
        ];
    }

    // 1. รายงานสรุป 10 วัน (รายบุคคล)
    public function index(Request $request)
    {
        $kpiSetting = Setting::where('key', 'kpi_hours')->first();
        $targetHours = $kpiSetting ? (int)$kpiSetting->value : 60;

        $options = $this->getFilterOptions();
        $users = $this->getFilteredUsersQuery($request)->get();

        $meetingTotals = MeetingRecord::inActivePeriod()
            ->selectRaw('user_id, SUM(total_hours) as total_hours')
            ->groupBy('user_id')
            ->pluck('total_hours', 'user_id');

        foreach ($users as $user) {
            $hours = $meetingTotals[$user->id] ?? 0;
            $user->total_hours = $hours;
            $user->kpi_percentage = min(($hours / $targetHours) * 100, 100); 
            $user->kpi_passed = $hours >= $targetHours; 
        }

        // กรองสถานะ KPI ผ่าน/ไม่ผ่าน
        if ($request->filled('kpi_status')) {
            $users = $users->filter(function ($user) use ($request) {
                return $request->kpi_status === 'passed' ? $user->kpi_passed : !$user->kpi_passed;
            });
        }

        return view('admin.reports.index', array_merge(compact('users', 'targetHours'), $options));
    }

    // 2. รายงาน Master Summary (สรุปรายแผนกและตำแหน่ง)
    public function masterSummary(Request $request)
    {
        $kpiSetting = Setting::where('key', 'kpi_hours')->first();
        $targetHours = $kpiSetting ? (int)$kpiSetting->value : 60;

        $options = $this->getFilterOptions();
        $users = $this->getFilteredUsersQuery($request)->get();

        $meetingTotals = MeetingRecord::inActivePeriod()
            ->selectRaw('user_id, SUM(total_hours) as total_hours')
            ->groupBy('user_id')
            ->pluck('total_hours', 'user_id');

        $departments = [];
        foreach ($users as $user) {
            $hours = $meetingTotals[$user->id] ?? 0;
            $passed = $hours >= $targetHours ? 1 : 0;
            
            // กรองสถานะ KPI ผ่าน/ไม่ผ่าน สำหรับรายงานแผนก
            if ($request->filled('kpi_status')) {
                if ($request->kpi_status === 'passed' && !$passed) continue;
                if ($request->kpi_status === 'failed' && $passed) continue;
            }

            if (!isset($departments[$user->department])) {
                $departments[$user->department] = ['positions' => [], 'total_staff' => 0, 'total_passed' => 0];
            }
            if (!isset($departments[$user->department]['positions'][$user->position])) {
                $departments[$user->department]['positions'][$user->position] = ['staff_count' => 0, 'passed_count' => 0];
            }

            $departments[$user->department]['positions'][$user->position]['staff_count'] += 1;
            $departments[$user->department]['positions'][$user->position]['passed_count'] += $passed;
            $departments[$user->department]['total_staff'] += 1;
            $departments[$user->department]['total_passed'] += $passed;
        }

        return view('admin.reports.master', array_merge(compact('departments', 'targetHours'), $options));
    }

    // 3. รายงาน Sum Pivot (แยกรายเดือน)
    public function pivotSummary(Request $request)
    {
        $filter = \App\Helpers\GlobalSetting::getDateFilter();

        $months = MeetingRecord::inActivePeriod() 
                    ->select('month_year')
                    ->whereNotNull('month_year')
                    ->distinct()
                    ->orderBy('month_year', 'asc')
                    ->pluck('month_year');

        $options = $this->getFilterOptions();
        $users = $this->getFilteredUsersQuery($request)->get();
        
        $meetingData = MeetingRecord::inActivePeriod()
            ->selectRaw('user_id, month_year, SUM(total_hours) as total')
            ->groupBy('user_id', 'month_year')
            ->get();

        $userMonths = [];
        $userTotals = [];
        foreach ($meetingData as $data) {
            $userMonths[$data->user_id][$data->month_year] = $data->total;
            $userTotals[$data->user_id] = ($userTotals[$data->user_id] ?? 0) + $data->total;
        }

        $kpiSetting = Setting::where('key', 'kpi_hours')->first();
        $targetHours = $kpiSetting ? (int)$kpiSetting->value : 60;

        foreach ($users as $user) {
            $user->total_hours = $userTotals[$user->id] ?? 0;
            $temp_monthly_hours = [];
            foreach ($months as $month) {
                $temp_monthly_hours[$month] = $userMonths[$user->id][$month] ?? 0;
            }
            $user->monthly_hours = $temp_monthly_hours;
            $user->kpi_passed = $user->total_hours >= $targetHours;
        }

        // กรองสถานะ KPI
        if ($request->filled('kpi_status')) {
            $users = $users->filter(function ($user) use ($request) {
                return $request->kpi_status === 'passed' ? $user->kpi_passed : !$user->kpi_passed;
            });
        }

        return view('admin.reports.pivot', array_merge(compact('users', 'months', 'filter'), $options));
    }
}