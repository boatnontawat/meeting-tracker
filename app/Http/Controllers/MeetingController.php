<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MeetingRecord;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon; 

class MeetingController extends Controller
{
    public function create()
    {
        $users = User::all();
        $departments = User::select('department')->distinct()->whereNotNull('department')->get();
        $positions = User::select('position')->distinct()->whereNotNull('position')->get();
        
        $topics = MeetingRecord::select('topic')->distinct()->whereNotNull('topic')->get();
        $organizers = MeetingRecord::select('organizer')->distinct()->whereNotNull('organizer')->get();
        $locations = MeetingRecord::select('location')->distinct()->whereNotNull('location')->get();
        
        return view('meeting_form', compact('users', 'departments', 'positions', 'topics', 'organizers', 'locations'));
    }

    public function store(Request $request)
    {
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

        $final_hours = $request->total_hours;
        if ($request->total_hours == 'custom') {
            $final_hours = $request->custom_hours;
        }

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
            // 🌟 เรียงลำดับตาม ID DESC เพื่อให้คนแอดข้อมูลล่าสุดอยู่บนสุด
            $query = MeetingRecord::with('user')->orderBy('id', 'desc');
            
            $isFiltering = $request->has('department');

            if ($isFiltering) {
                $startMonth = Setting::where('key', 'filter_start_month')->value('value');
                $endMonth = Setting::where('key', 'filter_end_month')->value('value');
                
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
                }
            }

            $records = $query->get()->map(function($record) {
                return [
                    'user_name' => $record->user ? $record->user->name : 'ไม่ระบุชื่อ',
                    'user_department' => $record->user ? $record->user->department : '-',
                    'user_position' => $record->user ? $record->user->position : '-',
                    
                    // 🌟 ปรับเป็นปี ค.ศ. (นำ addYears(543) ออก) และเรียงแบบ วัน/เดือน/ปี
                    'start_time_formatted' => $record->start_time ? Carbon::parse($record->start_time)->format('d/m/Y') : '-',
                    'end_time_formatted' => $record->end_time ? Carbon::parse($record->end_time)->format('d/m/Y') : '-',
                    
                    'total_hours' => $record->total_hours ?? 0,
                    'topic' => $record->topic ?? '-',
                    'meeting_type' => $record->meeting_type ?? '-',
                    'organizer' => $record->organizer ?? '-',
                    'location' => $record->location ?? '-',
                    'user_status' => $record->user ? $record->user->status : 'active',
                    'month_year' => $record->month_year ?? '-'
                ];
            });
            return response()->json(['data' => $records]);
        }

        $filterDepartments = User::whereNotNull('department')->distinct()->orderBy('department')->pluck('department');
        $filterPositions = User::whereNotNull('position')->distinct()->orderBy('position')->pluck('position');

        return view('meeting_summary', compact('filterDepartments', 'filterPositions'));
    }
}