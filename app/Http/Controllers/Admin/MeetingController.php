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
    public function index(Request $request)
    {
        $startMonth = Setting::where('key', 'filter_start_month')->value('value') ?? date('Y-01');
        $endMonth = Setting::where('key', 'filter_end_month')->value('value') ?? date('Y-12');

        // 🌟 ถ้าระบบเรียกข้อมูลผ่าน AJAX
        if ($request->ajax()) {
            $meetings = MeetingRecord::whereBetween('month_year', [$startMonth, $endMonth])
                ->with('user:id,name') // ดึงมาแค่ id กับ name (ประหยัด RAM สุดๆ)
                ->orderBy('id', 'desc')
                ->get();
            
            $data = [];
            foreach ($meetings as $meeting) {
                $val = floatval($meeting->total_hours);
                $hoursText = $val == floor($val) ? $val . ' ชม.' : number_format($val, 1) . ' ชม.';

                $editUrl = route('admin.meetings.edit', $meeting->id);
                $deleteUrl = route('admin.meetings.destroy', $meeting->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                $actionBtns = '
                    <a href="'.$editUrl.'" class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-square"></i></a>
                    <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'ลบข้อมูลการประชุมนี้ ใช่หรือไม่?\');">
                        '.$csrf.$method.'
                        <button type="submit" class="btn btn-sm btn-danger shadow-sm"><i class="bi bi-trash"></i></button>
                    </form>';

                $data[] = [
                    "id" => '<div class="text-center">' . $meeting->id . '</div>',
                    "user_name" => '<div class="fw-bold">' . ($meeting->user->name ?? 'ไม่พบผู้ใช้') . '</div>',
                    "topic" => '<div class="topic-cell">' . $meeting->topic . '</div>',
                    "start_time" => '<div class="text-center">' . Carbon::parse($meeting->start_time)->format('d/m/Y') . '</div>',
                    "end_time" => '<div class="text-center">' . Carbon::parse($meeting->end_time)->format('d/m/Y') . '</div>',
                    "total_hours" => '<div class="text-center text-danger fw-bold">' . $hoursText . '</div>',
                    "month_year" => '<div class="text-center">' . $meeting->month_year . '</div>',
                    "action" => '<div class="text-center">' . $actionBtns . '</div>'
                ];
            }
            return response()->json(['data' => $data]);
        }
                
        return view('admin.meetings.index');
    }

    public function create()
    {
        $users = User::orderBy('name', 'asc')->get();
        $topics = MeetingRecord::select('topic')->distinct()->whereNotNull('topic')->orderBy('topic')->get();
        $organizers = MeetingRecord::select('organizer')->distinct()->whereNotNull('organizer')->orderBy('organizer')->get();
        $locations = MeetingRecord::select('location')->distinct()->whereNotNull('location')->orderBy('location')->get();
        
        return view('admin.meetings.form', compact('users', 'topics', 'organizers', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'total_hours' => 'required',
        ]);

        $final_hours = ($request->total_hours == 'custom') ? $request->custom_hours : $request->total_hours;

        MeetingRecord::create([
            'user_id' => $request->user_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_hours' => floatval($final_hours),
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
        $topics = MeetingRecord::select('topic')->distinct()->whereNotNull('topic')->orderBy('topic')->get();
        $organizers = MeetingRecord::select('organizer')->distinct()->whereNotNull('organizer')->orderBy('organizer')->get();
        $locations = MeetingRecord::select('location')->distinct()->whereNotNull('location')->orderBy('location')->get();
        
        return view('admin.meetings.form', compact('meeting', 'users', 'topics', 'organizers', 'locations'));
    }

    public function update(Request $request, MeetingRecord $meeting)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        $final_hours = ($request->total_hours == 'custom') ? $request->custom_hours : $request->total_hours;

        $meeting->update([
            'user_id' => $request->user_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_hours' => floatval($final_hours),
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
