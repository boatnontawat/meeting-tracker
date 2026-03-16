<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MeetingRecord;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon; 

class MeetingController extends Controller
{
    public function create()
    {
        $users = User::all();
        $departments = User::select('department')->distinct()->whereNotNull('department')->get();
        $positions = User::select('position')->distinct()->whereNotNull('position')->get();
        
        // 🌟 ดึงข้อมูลประวัติการพิมพ์จากตาราง meeting_records มาทำเป็นตัวเลือก (Dropdown)
        $topics = MeetingRecord::select('topic')->distinct()->whereNotNull('topic')->get();
        $organizers = MeetingRecord::select('organizer')->distinct()->whereNotNull('organizer')->get();
        $locations = MeetingRecord::select('location')->distinct()->whereNotNull('location')->get();
        
        return view('meeting_form', compact('users', 'departments', 'positions', 'topics', 'organizers', 'locations'));
    }

    public function store(Request $request)
    {
        // ค้นหาชื่อ User ก่อน
        $user = User::where('name', $request->name)->first();

        if ($user) {
            $user->update([
                'department' => $request->department,
                'position' => $request->position,
            ]);
        } else {
            $user = User::create([
                'name' => $request->name,
                'department' => $request->department,
                'position' => $request->position,
                'status' => 'active',
                'email' => 'user_' . time() . '@domain.com',
                'password' => Hash::make('12345678')
            ]);
        }

        // จัดการเรื่องชั่วโมง (ถ้าระบุเองให้ใช้ค่าจากช่อง custom_hours)
        $final_hours = $request->total_hours;
        if ($request->total_hours == 'custom') {
            $final_hours = $request->custom_hours;
        }

        // บันทึกข้อมูลการประชุม (พิมพ์อะไรมาใหม่ ระบบจะจำเข้า DB ให้อัตโนมัติ)
        MeetingRecord::create([
            'user_id'      => $user->id,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
            'total_hours'  => $final_hours, 
            'topic'        => $request->topic,
            'meeting_type' => $request->meeting_type,
            'organizer'    => $request->organizer,
            'location'     => $request->location,
            'month_year'   => $request->month_year,
        ]);
        
        return redirect()->route('form.summary')->with('success', 'บันทึกข้อมูลการประชุมเรียบร้อยแล้ว!');
    }

    public function summary(Request $request)
    {
        if ($request->ajax()) {
            $startMonth = \App\Models\Setting::where('key', 'filter_start_month')->value('value');
            $endMonth = \App\Models\Setting::where('key', 'filter_end_month')->value('value');

            $query = MeetingRecord::with('user');
            
            if ($startMonth && $endMonth) {
                $query->whereBetween('month_year', [$startMonth, $endMonth]);
            }

            if ($request->filled('department')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('department', $request->department);
                });
            }

            if ($request->filled('position')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('position', $request->position);
                });
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('status', $request->status);
                });
            } elseif (!$request->filled('status')) {
                $query->whereHas('user', function($q) {
                    $q->where('status', 'active');
                });
            }

            $records = $query->orderBy('start_time', 'desc')->get()->map(function($record) {
                $record->start_time_formatted = Carbon::parse($record->start_time)->format('d/m/Y');
                $record->end_time_formatted = Carbon::parse($record->end_time)->format('d/m/Y');
                return $record;
            });

            return response()->json(['data' => $records]);
        }

        $filterDepartments = User::whereNotNull('department')->distinct()->orderBy('department')->pluck('department');
        $filterPositions = User::whereNotNull('position')->distinct()->orderBy('position')->pluck('position');

        return view('meeting_summary', compact('filterDepartments', 'filterPositions'));
    }
}