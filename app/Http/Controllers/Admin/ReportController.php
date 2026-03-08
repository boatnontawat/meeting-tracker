<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MeetingRecord;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    // 💡 ดึงช่วงเดือนและ KPI จาก Setting ตรงๆ
    private function getSettings()
    {
        return [
            'start' => Setting::where('key', 'filter_start_month')->value('value') ?? date('Y-01'),
            'end'   => Setting::where('key', 'filter_end_month')->value('value') ?? date('Y-12'),
            'kpi'   => (int)(Setting::where('key', 'kpi_hours')->value('value') ?? 60),
        ];
    }

    private function getFilteredUsersQuery(Request $request)
    {
        $query = User::query();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } elseif (!$request->filled('status')) {
            $query->where('status', 'active');
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        return $query->orderBy('department');
    }

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
        $settings = $this->getSettings();
        $targetHours = $settings['kpi'];

        $options = $this->getFilterOptions();
        $users = $this->getFilteredUsersQuery($request)->get();

        // 💡 บังคับกรองเฉพาะช่วงเดือนที่ตั้งค่า
        $meetingTotals = MeetingRecord::whereBetween('month_year', [$settings['start'], $settings['end']])
            ->selectRaw('user_id, SUM(total_hours) as total_hours')
            ->groupBy('user_id')
            ->pluck('total_hours', 'user_id');

        foreach ($users as $user) {
            $hours = $meetingTotals[$user->id] ?? 0;
            $user->total_hours = $hours;
            $user->kpi_percentage = min(($hours / $targetHours) * 100, 100); 
            $user->kpi_passed = $hours >= $targetHours; 
        }

        if ($request->filled('kpi_status')) {
            $users = $users->filter(function ($user) use ($request) {
                return $request->kpi_status === 'passed' ? $user->kpi_passed : !$user->kpi_passed;
            });
        }

        return view('admin.reports.index', array_merge(compact('users', 'targetHours'), $options));
    }

    // 2. รายงาน Master Summary (สรุปรายแผนก)
    public function masterSummary(Request $request)
    {
        $settings = $this->getSettings();
        $targetHours = $settings['kpi'];

        $options = $this->getFilterOptions();
        $users = $this->getFilteredUsersQuery($request)->get();

        // 💡 บังคับกรองเฉพาะช่วงเดือนที่ตั้งค่า
        $meetingTotals = MeetingRecord::whereBetween('month_year', [$settings['start'], $settings['end']])
            ->selectRaw('user_id, SUM(total_hours) as total_hours')
            ->groupBy('user_id')
            ->pluck('total_hours', 'user_id');

        $departments = [];
        foreach ($users as $user) {
            $hours = $meetingTotals[$user->id] ?? 0;
            $passed = $hours >= $targetHours ? 1 : 0;
            
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
        $settings = $this->getSettings();
        $startMonth = $settings['start'];
        $endMonth = $settings['end'];
        $targetHours = $settings['kpi'];

        // 💡 1. สร้างคอลัมน์เดือนจาก Setting เท่านั้น (ป้องกันเดือนเก่าโผล่มา)
        $months = [];
        $current = Carbon::parse($startMonth)->startOfMonth();
        $end = Carbon::parse($endMonth)->startOfMonth();

        while ($current->lte($end)) {
            $months[] = $current->format('Y-m');
            $current->addMonth();
        }

        $options = $this->getFilterOptions();
        $users = $this->getFilteredUsersQuery($request)->get();
        
        // 💡 2. ดึงชั่วโมงรวม เฉพาะเดือนที่ตั้งค่า
        $meetingData = MeetingRecord::whereBetween('month_year', [$startMonth, $endMonth])
            ->selectRaw('user_id, month_year, SUM(total_hours) as total')
            ->groupBy('user_id', 'month_year')
            ->get();

        $userMonths = [];
        $userTotals = [];
        foreach ($meetingData as $data) {
            $userMonths[$data->user_id][$data->month_year] = $data->total;
            $userTotals[$data->user_id] = ($userTotals[$data->user_id] ?? 0) + $data->total;
        }

        foreach ($users as $user) {
            $user->total_hours = $userTotals[$user->id] ?? 0;
            $temp_monthly_hours = [];
            foreach ($months as $month) {
                $temp_monthly_hours[$month] = $userMonths[$user->id][$month] ?? 0;
            }
            $user->monthly_hours = $temp_monthly_hours;
            $user->kpi_passed = $user->total_hours >= $targetHours;
        }

        if ($request->filled('kpi_status')) {
            $users = $users->filter(function ($user) use ($request) {
                return $request->kpi_status === 'passed' ? $user->kpi_passed : !$user->kpi_passed;
            });
        }

        // ส่งตัวแปรเป็น Array ธรรมดาไปที่ View
        $filter = ['start' => $startMonth, 'end' => $endMonth];

        return view('admin.reports.pivot', array_merge(compact('users', 'months', 'filter'), $options));
    }
}