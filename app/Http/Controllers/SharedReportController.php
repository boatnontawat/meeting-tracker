<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MeetingRecord;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SharedReportController extends Controller
{
    private function getSettings()
    {
        return [
            'start' => Setting::where('key', 'filter_start_month')->value('value') ?? date('Y-01'),
            'end'   => Setting::where('key', 'filter_end_month')->value('value') ?? date('Y-12'),
            'kpi'   => (int)(Setting::where('key', 'kpi_hours')->value('value') ?? 60),
        ];
    }

    // 🌟 ล็อก Query ให้ดึงเฉพาะ $department จาก URL
    private function getFilteredUsersQuery($department, Request $request)
    {
        $query = User::where('status', 'active')->where('department', $department);

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        return $query;
    }

    private function getFilterOptions($department)
    {
        return [
            'filterPositions' => User::where('status', 'active')
                                     ->where('department', $department)
                                     ->whereNotNull('position')
                                     ->distinct()
                                     ->orderBy('position')
                                     ->pluck('position'),
            'department' => $department // ส่งชื่อแผนกไปแสดงที่ View
        ];
    }

    public function index($department, Request $request)
    {
        $settings = $this->getSettings();
        $targetHours = $settings['kpi'];
        $options = $this->getFilterOptions($department);
        $users = $this->getFilteredUsersQuery($department, $request)->get();

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

        return view('shared_reports.index', array_merge(compact('users', 'targetHours'), $options));
    }

    public function masterSummary($department, Request $request)
    {
        $settings = $this->getSettings();
        $targetHours = $settings['kpi'];
        $options = $this->getFilterOptions($department);
        $users = $this->getFilteredUsersQuery($department, $request)->get();

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

        return view('shared_reports.master', array_merge(compact('departments', 'targetHours'), $options));
    }

    public function pivotSummary($department, Request $request)
    {
        $settings = $this->getSettings();
        $startMonth = $settings['start'];
        $endMonth = $settings['end'];
        $targetHours = $settings['kpi'];

        $months = [];
        $current = Carbon::parse($startMonth)->startOfMonth();
        $end = Carbon::parse($endMonth)->startOfMonth();

        while ($current->lte($end)) {
            $months[] = $current->format('Y-m');
            $current->addMonth();
        }

        $options = $this->getFilterOptions($department);
        $users = $this->getFilteredUsersQuery($department, $request)->get();
        
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

        $filter = ['start' => $startMonth, 'end' => $endMonth];

        return view('shared_reports.pivot', array_merge(compact('users', 'months', 'filter'), $options));
    }

    public function departmentOverview($department, Request $request)
    {
        $settings = $this->getSettings();
        $targetHours = $settings['kpi'];
        $options = $this->getFilterOptions($department);
        $users = $this->getFilteredUsersQuery($department, $request)->get();

        $meetingTotals = \App\Models\MeetingRecord::whereBetween('month_year', [$settings['start'], $settings['end']])
            ->selectRaw('user_id, SUM(total_hours) as total_hours')
            ->groupBy('user_id')
            ->pluck('total_hours', 'user_id');

        $departments = [];
        foreach ($users as $user) {
            $hours = $meetingTotals[$user->id] ?? 0;
            $dept = $user->department ?? 'ไม่ระบุ';
            $pos = $user->position ?? 'ไม่ระบุ';

            if (!isset($departments[$dept])) {
                $departments[$dept] = [
                    'total_dept_hours' => 0,
                    'positions' => []
                ];
            }

            if (!isset($departments[$dept]['positions'][$pos])) {
                $departments[$dept]['positions'][$pos] = [
                    'staff_count' => 0,
                    'total_pos_hours' => 0
                ];
            }

            $departments[$dept]['positions'][$pos]['staff_count'] += 1;
            $departments[$dept]['positions'][$pos]['total_pos_hours'] += $hours;
            $departments[$dept]['total_dept_hours'] += $hours;
        }

        $flatData = [];
        foreach ($departments as $deptName => $deptInfo) {
            foreach ($deptInfo['positions'] as $posName => $posInfo) {
                $flatData[] = [
                    'department' => $deptName,
                    'position' => $posName,
                    'staff_count' => $posInfo['staff_count'],
                    'total_pos_hours' => $posInfo['total_pos_hours'],
                    'total_dept_hours' => $deptInfo['total_dept_hours'],
                ];
            }
        }

        usort($flatData, function($a, $b) {
            return strcmp($a['department'], $b['department']);
        });

        return view('shared_reports.department_overview', array_merge(compact('flatData', 'targetHours'), $options));
    }
}