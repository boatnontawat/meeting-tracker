<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeetingRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MeetingController extends Controller
{
    public function index()
    {
        $meetings = MeetingRecord::inActivePeriod() // เพิ่มการกรอง
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
    return view('admin.meetings.index', compact('meetings'));
    }

    public function create()
    {
        // ดึงรายชื่อ User ทั้งหมดไปแสดงใน Dropdown ให้แอดมินเลือก
        $users = User::orderBy('name', 'asc')->get();
        return view('admin.meetings.form', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);

        MeetingRecord::create([
            'user_id' => $request->user_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_hours' => $request->total_hours,
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
        return view('admin.meetings.form', compact('meeting', 'users'));
    }

    public function update(Request $request, MeetingRecord $meeting)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'total_hours' => 'required|numeric|min:0',
        ]);

        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);

        $meeting->update([
            'user_id' => $request->user_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_hours' => $request->total_hours,
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