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
        
        return view('meeting_form', compact('users', 'departments', 'positions'));
    }

    public function store(Request $request)
    {
        // 1. ค้นหาชื่อ User ก่อน
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

        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);
        $total_hours = $start->diffInMinutes($end) / 60; 

        MeetingRecord::create([
            'user_id' => $user->id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_hours'  => $request->total_hours,
            'topic' => $request->topic,
            'meeting_type' => $request->meeting_type,
            'organizer' => $request->organizer,
            'location' => $request->location,
            'month_year' => $request->month_year,
        ]);
        
        return redirect()->route('form.summary')->with('success', 'บันทึกข้อมูลการประชุมเรียบร้อยแล้ว!');
    }
    public function summary(Request $request)
{
    // ใช้ inActivePeriod() เพื่อกรองตามช่วงเดือนที่แอดมินตั้งค่าไว้
    $query = MeetingRecord::inActivePeriod()->with('user');

    // กรองตามหน่วยงาน
    if ($request->filled('department')) {
        $query->whereHas('user', function($q) use ($request) {
            $q->where('department', $request->department);
        });
    }

    // กรองตามตำแหน่ง
    if ($request->filled('position')) {
        $query->whereHas('user', function($q) use ($request) {
            $q->where('position', $request->position);
        });
    }

    // กรองสถานะการทำงาน (Default เป็นปฏิบัติงาน)
    if ($request->filled('status') && $request->status !== 'all') {
        $query->whereHas('user', function($q) use ($request) {
            $q->where('status', $request->status);
        });
    } elseif (!$request->filled('status')) {
        $query->whereHas('user', function($q) {
            $q->where('status', 'active');
        });
    }

    $records = $query->orderBy('created_at', 'desc')->get();

    // ดึงตัวเลือกสำหรับ Dropdown
    $filterDepartments = User::whereNotNull('department')->distinct()->orderBy('department')->pluck('department');
    $filterPositions = User::whereNotNull('position')->distinct()->orderBy('position')->pluck('position');

    return view('meeting_summary', compact('records', 'filterDepartments', 'filterPositions'));
}
}