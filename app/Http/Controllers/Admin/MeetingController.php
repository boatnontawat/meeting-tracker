<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeetingRecord;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MeetingController extends Controller
{
    public function index()
    {
        $startMonth = Setting::where('key', 'filter_start_month')->value('value') ?? date('Y-01');
        $endMonth = Setting::where('key', 'filter_end_month')->value('value') ?? date('Y-12');

        $meetings = MeetingRecord::whereBetween('month_year', [$startMonth, $endMonth])
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
                
        return view('admin.meetings.index', compact('meetings'));
    }

    public function create()
    {
        $users = User::orderBy('name', 'asc')->get();
        
        // 🌟 ดึงข้อมูลประวัติการพิมพ์มาแสดงเป็นตัวเลือก (Dropdown)
        $topics = MeetingRecord::select('topic')->distinct()->whereNotNull('topic')->get();
        $organizers = MeetingRecord::select('organizer')->distinct()->whereNotNull('organizer')->get();
        $locations = MeetingRecord::select('location')->distinct()->whereNotNull('location')->get();
        
        return view('admin.meetings.form', compact('users', 'topics', 'organizers', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        // 🌟 จัดการเรื่องชั่วโมง (ถ้าระบุเองให้ใช้ค่าจาก custom_hours)
        $final_hours = $request->total_hours;
        if ($request->total_hours == 'custom') {
            $final_hours = $request->custom_hours;
        }

        MeetingRecord::create([
            'user_id' => $request->user_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_hours' => $final_hours,
            'topic' => $request->topic,
            'meeting_type' => $request->meeting_type,
            'organizer' => $request->organizer,
            'location' => $request->location,
            'month_year' => $request->month_year,
        ]);

        return redirect()->route('admin.meetings.index')->with('success', 'เพิ่มข้อมูลการประชุมเรียบร้อยแล้ว!');
    }

    public function edit(MeetingRecord $meeting)
    {
        $users = User::orderBy('name', 'asc')->get();
        
        // 🌟 ดึงข้อมูลประวัติการพิมพ์มาแสดงเป็นตัวเลือก
        $topics = MeetingRecord::select('topic')->distinct()->whereNotNull('topic')->get();
        $organizers = MeetingRecord::select('organizer')->distinct()->whereNotNull('organizer')->get();
        $locations = MeetingRecord::select('location')->distinct()->whereNotNull('location')->get();
        
        return view('admin.meetings.form', compact('meeting', 'users', 'topics', 'organizers', 'locations'));
    }

    public function update(Request $request, MeetingRecord $meeting)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
        ]);

        // 🌟 จัดการเรื่องชั่วโมง (ถ้าระบุเองให้ใช้ค่าจาก custom_hours)
        $final_hours = $request->total_hours;
        if ($request->total_hours == 'custom') {
            $final_hours = $request->custom_hours;
        }

        $meeting->update([
            'user_id' => $request->user_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_hours' => $final_hours,
            'topic' => $request->topic,
            'meeting_type' => $request->meeting_type,
            'organizer' => $request->organizer,
            'location' => $request->location,
            'month_year' => $request->month_year,
        ]);

        return redirect()->route('admin.meetings.index')->with('success', 'อัปเดตข้อมูลการประชุมเรียบร้อยแล้ว!');
    }

    public function destroy(MeetingRecord $meeting)
    {
        $meeting->delete();
        return redirect()->route('admin.meetings.index')->with('success', 'ลบข้อมูลการประชุมเรียบร้อยแล้ว!');
    }
}