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
        
        $startMonth = Setting::where('key', 'filter_start_month')->value('value') ?? date('Y-01');
        $endMonth = Setting::where('key', 'filter_end_month')->value('value') ?? date('Y-12');

        // 🌟 2. ดึงข้อมูลผู้ใช้งาน (เฉพาะ active) พร้อมชั่วโมงที่ต้องทำ (required_hours)
        // และนำไป JOIN กับชั่วโมงการประชุมที่ทำได้จริงในช่วงเวลาที่กำหนด
        $userHours = User::where('status', 'active')
            ->leftJoin('meeting_records', function($join) use ($startMonth, $endMonth) {
                $join->on('users.id', '=', 'meeting_records.user_id')
                     ->whereBetween('meeting_records.month_year', [$startMonth, $endMonth]);
            })
            ->select(
                'users.id', 
                'users.required_hours', 
                DB::raw('COALESCE(SUM(meeting_records.total_hours), 0) as total_hours')
            )
            ->groupBy('users.id', 'users.required_hours')
            ->get();

        // 🌟 3. คำนวณจำนวนคนที่ ผ่าน / ไม่ผ่าน(แต่เกิน 50%) / ไม่ผ่าน(ต่ำกว่า 50%)
        $passedCount = 0;
        $failedButOver50Count = 0;
        $failedCount = 0;

        foreach ($userHours as $user) {
            // ดึงเกณฑ์ชั่วโมงของคนนั้นๆ (ถ้าค่าเป็น null หรือไม่เจอให้ default เป็น 0)
            $required = (float)($user->required_hours ?? 0);
            $achieved = (float)$user->total_hours;

            // ถ้าเกณฑ์เป็น 0 (อาจจะไม่ได้ตั้งไว้ หรือไม่มีภาระงาน) ให้ถือว่าผ่านเกณฑ์ไปก่อน
            if ($required == 0) {
                $passedCount++;
                continue;
            }

            // คำนวณ 50% ของเกณฑ์รายบุคคล
            $halfRequired = $required / 2;

            if ($achieved >= $required) {
                $passedCount++; // ผ่านเกณฑ์ (ได้ครบตามที่ตัวเองต้องทำ หรือมากกว่า)
            } elseif ($achieved >= $halfRequired) {
                $failedButOver50Count++; // ไม่ผ่าน แต่ทำได้ตั้งแต่ 50% ขึ้นไปของเกณฑ์ตัวเอง
            } else {
                $failedCount++; // ไม่ผ่าน (ทำได้น้อยกว่า 50% ของเกณฑ์ตัวเอง)
            }
        }

        // 🌟 4. ดึงข้อมูลสำหรับกราฟต่างๆ (เฉพาะ active)
        $baseQuery = MeetingRecord::whereBetween('month_year', [$startMonth, $endMonth])
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            });

        // ข้อมูลกราฟแท่ง (ชั่วโมงแยกตามแผนก)
        $departmentData = (clone $baseQuery)
            ->join('users', 'meeting_records.user_id', '=', 'users.id')
            ->selectRaw('users.department, SUM(meeting_records.total_hours) as sum_hours')
            ->whereNotNull('users.department')
            ->where('users.department', '!=', '')
            ->groupBy('users.department')
            ->orderByDesc('sum_hours')
            ->get();

        // ข้อมูลกราฟโดนัท (ประเภทการประชุม)
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

        return view('admin.dashboard', compact(
            'totalUsers', 'passedCount', 'failedButOver50Count', 'failedCount',
            'departmentData', 'typeData',
            'startMonth', 'endMonth', 'departmentOverview'
        ));
    }
}