<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 🌟 1. ดึงการตั้งค่า
        $startMonth = Setting::where('key', 'filter_start_month')->value('value') ?? date('Y-01');
        $endMonth = Setting::where('key', 'filter_end_month')->value('value') ?? date('Y-12');
        $kpiHours = (float)(Setting::where('key', 'kpi_hours')->value('value') ?? 60);

        // 🌟 2. ใช้ Query Builder แทน Eloquent (ประหยัด RAM ได้เกือบ 100%)
        $totalUsers = DB::table('users')->where('status', 'active')->count();

        // 🌟 3. คำนวณชั่วโมงรายบุคคล และคำนวณ KPI รวดเดียวจบ
        $userHours = DB::table('users')
            ->where('status', 'active')
            ->leftJoin('meeting_records', function($join) use ($startMonth, $endMonth) {
                $join->on('users.id', '=', 'meeting_records.user_id')
                     ->whereBetween('meeting_records.month_year', [$startMonth, $endMonth]);
            })
            ->select('users.id', DB::raw('COALESCE(SUM(meeting_records.total_hours), 0) as total_hours'))
            ->groupBy('users.id')
            ->get();

        $passedCount = 0;
        $failedButOver50Count = 0;
        $failedCount = 0;
        $halfKpi = $kpiHours / 2;

        foreach ($userHours as $user) {
            $achieved = (float)$user->total_hours;
            if ($achieved >= $kpiHours) $passedCount++;
            elseif ($achieved >= $halfKpi) $failedButOver50Count++;
            else $failedCount++;
        }

        // 🌟 4. ดึงข้อมูลสำหรับกราฟ (Query ตรงจากฐานข้อมูล เร็วปรี๊ด)
        $departmentData = DB::table('meeting_records')
            ->join('users', 'meeting_records.user_id', '=', 'users.id')
            ->where('users.status', 'active')
            ->whereBetween('meeting_records.month_year', [$startMonth, $endMonth])
            ->selectRaw('users.department, SUM(meeting_records.total_hours) as sum_hours')
            ->whereNotNull('users.department')
            ->where('users.department', '!=', '')
            ->groupBy('users.department')
            ->orderByDesc('sum_hours')
            ->get();

        $typeData = DB::table('meeting_records')
            ->join('users', 'meeting_records.user_id', '=', 'users.id')
            ->where('users.status', 'active')
            ->whereBetween('meeting_records.month_year', [$startMonth, $endMonth])
            ->selectRaw('meeting_records.meeting_type, COUNT(meeting_records.id) as count')
            ->groupBy('meeting_records.meeting_type')
            ->get();

        // 🌟 5. ดึงข้อมูลตาราง Department Overview (ใช้ DB Table เร็วกว่า Model มาก)
        $departmentOverview = DB::table('users')
            ->where('status', 'active')
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

        return view('admin.dashboard', compact(
            'totalUsers', 'passedCount', 'failedButOver50Count', 'failedCount',
            'departmentData', 'typeData',
            'startMonth', 'endMonth', 'departmentOverview', 'kpiHours'
        ));
    }
}
