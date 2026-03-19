<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MeetingRecord;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function getSettings()
    {
        return [
            'start' => Setting::where('key', 'filter_start_month')->value('value') ?? date('Y-01'),
            'end'   => Setting::where('key', 'filter_end_month')->value('value') ?? date('Y-12'),
            'kpi'   => (int)(Setting::where('key', 'kpi_hours')->value('value') ?? 60),
        ];
    }

    private function getFilterOptions()
    {
        return [
            'filterDepartments' => User::where('status', 'active')->whereNotNull('department')->distinct()->orderBy('department')->pluck('department'),
            'filterPositions' => User::where('status', 'active')->whereNotNull('position')->distinct()->orderBy('position')->pluck('position'),
        ];
    }

    // 1. รายงานสรุป 10 วัน (รายบุคคล)
    public function index(Request $request)
    {
        $settings = $this->getSettings();
        $targetHours = $settings['kpi'];
        $options = $this->getFilterOptions();

        if ($request->ajax()) {
            $query = DB::table('users')->where('status', $request->status ?? 'active');

            if ($request->filled('department')) $query->where('department', $request->department);
            if ($request->filled('position')) $query->where('position', $request->position);

            $hoursQuery = DB::table('meeting_records')
                ->select('user_id', DB::raw('SUM(total_hours) as total_hours'))
                ->whereBetween('month_year', [$settings['start'], $settings['end']])
                ->groupBy('user_id');

            $query->leftJoinSub($hoursQuery, 'meetings', function ($join) {
                $join->on('users.id', '=', 'meetings.user_id');
            });

            $query->select('id', 'name', 'department', 'position', 'status', DB::raw('COALESCE(meetings.total_hours, 0) as total_hours'));
            
            $users = $query->get();
            $data = [];

            foreach ($users as $user) {
                $kpi_percentage = min(($user->total_hours / $targetHours) * 100, 100);
                $kpi_passed = $user->total_hours >= $targetHours;

                if ($request->filled('kpi_status')) {
                    if ($request->kpi_status === 'passed' && !$kpi_passed) continue;
                    if ($request->kpi_status === 'failed' && $kpi_passed) continue;
                }

                $rowClass = 'table-danger';
                if ($kpi_percentage >= 100) $rowClass = 'table-success';
                elseif ($kpi_percentage >= 50) $rowClass = 'table-warning';

                $statusBadge = $user->status !== 'active' ? '<span class="badge bg-danger ms-1" style="font-size: 0.75em;">ลาออก</span>' : '';
                $kpiBadge = $kpi_passed ? '<span class="badge bg-success px-2 py-1 shadow-sm">✅ ผ่าน</span>' : '<span class="badge bg-danger px-2 py-1 shadow-sm">❌ ไม่ผ่าน</span>';
                $progressBar = '<div class="progress shadow-sm" style="height: 20px; font-size: 12px; background-color: rgba(255,255,255,0.5);"><div class="progress-bar bg-dark text-white fw-bold" role="progressbar" style="width: '.($kpi_percentage > 100 ? 100 : $kpi_percentage).'%;" aria-valuenow="'.$kpi_percentage.'" aria-valuemin="0" aria-valuemax="100">'.number_format($kpi_percentage, 1).'%</div></div>';

                $data[] = [
                    "DT_RowClass" => "align-middle " . $rowClass,
                    "index" => count($data) + 1,
                    "name" => '<div class="text-start fw-bold">' . $user->name . $statusBadge . '</div>',
                    "department" => $user->department,
                    "position" => $user->position,
                    "total_hours" => '<div class="text-danger fw-bold fs-6">' . number_format($user->total_hours, 1) . '</div>',
                    "progress" => $progressBar,
                    "status" => $kpiBadge
                ];
            }
            return response()->json(['data' => $data]);
        }
        return view('admin.reports.index', array_merge(compact('targetHours'), $options));
    }

    // 2. รายงาน Master Summary (สรุปรายแผนก)
    public function masterSummary(Request $request)
    {
        $settings = $this->getSettings();
        $targetHours = $settings['kpi'];
        $options = $this->getFilterOptions();

        if ($request->ajax()) {
            $query = DB::table('users')->where('status', $request->status ?? 'active');
            if ($request->filled('department')) $query->where('department', $request->department);
            if ($request->filled('position')) $query->where('position', $request->position);

            $hoursQuery = DB::table('meeting_records')
                ->select('user_id', DB::raw('SUM(total_hours) as total_hours'))
                ->whereBetween('month_year', [$settings['start'], $settings['end']])
                ->groupBy('user_id');

            $query->leftJoinSub($hoursQuery, 'meetings', function ($join) {
                $join->on('users.id', '=', 'meetings.user_id');
            });
            $query->select('department', 'position', DB::raw('COALESCE(meetings.total_hours, 0) as total_hours'));

            $users = $query->get();
            $departments = [];

            foreach ($users as $user) {
                $passed = $user->total_hours >= $targetHours ? 1 : 0;
                
                if ($request->filled('kpi_status')) {
                    if ($request->kpi_status === 'passed' && !$passed) continue;
                    if ($request->kpi_status === 'failed' && $passed) continue;
                }

                $dept = $user->department ?? 'ไม่ระบุ';
                $pos = $user->position ?? 'ไม่ระบุ';

                if (!isset($departments[$dept])) {
                    $departments[$dept] = ['positions' => [], 'total_staff' => 0, 'total_passed' => 0];
                }
                if (!isset($departments[$dept]['positions'][$pos])) {
                    $departments[$dept]['positions'][$pos] = ['staff_count' => 0, 'passed_count' => 0];
                }

                $departments[$dept]['positions'][$pos]['staff_count'] += 1;
                $departments[$dept]['positions'][$pos]['passed_count'] += $passed;
                $departments[$dept]['total_staff'] += 1;
                $departments[$dept]['total_passed'] += $passed;
            }

            $data = [];
            foreach ($departments as $deptName => $deptData) {
                foreach ($deptData['positions'] as $posName => $posData) {
                    $percent = $posData['staff_count'] > 0 ? ($posData['passed_count'] / $posData['staff_count']) * 100 : 0;
                    $badgeClass = $percent >= 100 ? 'bg-success' : 'bg-secondary';
                    
                    $data[] = [
                        "department" => '<div class="text-start fw-bold">' . $deptName . '</div>',
                        "position" => '<div class="text-start">' . $posName . '</div>',
                        "staff_count" => $posData['staff_count'],
                        "passed_count" => $posData['passed_count'],
                        "percent" => '<span class="badge ' . $badgeClass . '">' . number_format($percent, 1) . '%</span>'
                    ];
                }
                $totalPercent = $deptData['total_staff'] > 0 ? ($deptData['total_passed'] / $deptData['total_staff']) * 100 : 0;
                $data[] = [
                    "DT_RowClass" => "table-secondary fw-bold text-primary",
                    "department" => '<div class="text-end">' . $deptName . '</div>',
                    "position" => '<div class="text-end">ผลรวม</div>',
                    "staff_count" => $deptData['total_staff'],
                    "passed_count" => $deptData['total_passed'],
                    "percent" => number_format($totalPercent, 1) . '%'
                ];
            }
            return response()->json(['data' => $data]);
        }
        return view('admin.reports.master', array_merge(compact('targetHours'), $options));
    }

    // 3. รายงาน Sum Pivot (แยกรายเดือน)
    public function pivotSummary(Request $request)
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

        $options = $this->getFilterOptions();

        if ($request->ajax()) {
            $query = DB::table('users')->where('status', $request->status ?? 'active');
            if ($request->filled('department')) $query->where('department', $request->department);
            if ($request->filled('position')) $query->where('position', $request->position);
            $query->select('id', 'name', 'department', 'position', 'status');
            
            $users = $query->get();
            $userIds = $users->pluck('id')->toArray();

            $meetingData = DB::table('meeting_records')
                ->select('user_id', 'month_year', DB::raw('SUM(total_hours) as total'))
                ->whereIn('user_id', $userIds)
                ->whereBetween('month_year', [$startMonth, $endMonth])
                ->groupBy('user_id', 'month_year')
                ->get();

            $userMonths = [];
            $userTotals = [];
            foreach ($meetingData as $data) {
                $userMonths[$data->user_id][$data->month_year] = $data->total;
                $userTotals[$data->user_id] = ($userTotals[$data->user_id] ?? 0) + $data->total;
            }

            $data = [];
            foreach ($users as $user) {
                $total_hours = $userTotals[$user->id] ?? 0;
                $kpi_passed = $total_hours >= $targetHours;

                if ($request->filled('kpi_status')) {
                    if ($request->kpi_status === 'passed' && !$kpi_passed) continue;
                    if ($request->kpi_status === 'failed' && $kpi_passed) continue;
                }

                $statusBadge = $user->status !== 'active' ? '<span class="badge bg-danger ms-1" style="font-size: 0.75em;">ลาออก</span>' : '';
                
                $row = [
                    "DT_RowClass" => $total_hours == 0 ? 'table-danger' : '',
                    "department" => '<div class="text-start">' . $user->department . '</div>',
                    "name" => '<div class="text-start fw-bold">' . $user->name . $statusBadge . '</div>',
                    "position" => $user->position,
                ];

                foreach ($months as $month) {
                    $val = $userMonths[$user->id][$month] ?? 0;
                    $row[$month] = $val > 0 ? $val : '<span class="'.($total_hours == 0 ? 'text-danger' : 'text-muted').' opacity-50">-</span>';
                }

                $row['total_hours'] = '<div class="text-danger fw-bold fs-6">' . $total_hours . '</div>';
                $data[] = $row;
            }
            return response()->json(['data' => $data]);
        }

        $filter = ['start' => $startMonth, 'end' => $endMonth];
        return view('admin.reports.pivot', array_merge(compact('months', 'filter'), $options));
    }

    // 4. ภาพรวมหน่วยงาน (Department Overview)
    public function departmentOverview(Request $request)
    {
        $settings = $this->getSettings();
        $targetHours = $settings['kpi'];
        $options = $this->getFilterOptions();

        if ($request->ajax()) {
            $query = DB::table('users')->where('status', 'active');
            if ($request->filled('department')) $query->where('department', $request->department);

            $hoursQuery = DB::table('meeting_records')
                ->select('user_id', DB::raw('SUM(total_hours) as total_hours'))
                ->whereBetween('month_year', [$settings['start'], $settings['end']])
                ->groupBy('user_id');

            $query->leftJoinSub($hoursQuery, 'meetings', function ($join) {
                $join->on('users.id', '=', 'meetings.user_id');
            });
            $query->select('department', 'position', DB::raw('COALESCE(meetings.total_hours, 0) as total_hours'));

            $users = $query->get();
            $departments = [];

            foreach ($users as $user) {
                $dept = $user->department ?? 'ไม่ระบุ';
                $pos = $user->position ?? 'ไม่ระบุ';

                if (!isset($departments[$dept])) {
                    $departments[$dept] = ['total_dept_hours' => 0, 'positions' => []];
                }
                if (!isset($departments[$dept]['positions'][$pos])) {
                    $departments[$dept]['positions'][$pos] = ['staff_count' => 0, 'total_pos_hours' => 0];
                }

                $departments[$dept]['positions'][$pos]['staff_count'] += 1;
                $departments[$dept]['positions'][$pos]['total_pos_hours'] += $user->total_hours;
                $departments[$dept]['total_dept_hours'] += $user->total_hours;
            }

            $data = [];
            $index = 1;
            ksort($departments); 
            foreach ($departments as $deptName => $deptInfo) {
                foreach ($deptInfo['positions'] as $posName => $posInfo) {
                    $data[] = [
                        "index" => $index++,
                        "department" => $deptName,
                        "position" => '<div class="text-start ps-4"><i class="bi bi-person-badge text-muted me-2"></i> ' . $posName . '</div>',
                        "staff_count" => '<div class="fw-bold">' . $posInfo['staff_count'] . '</div>',
                        "total_pos_hours" => '<div class="text-primary fw-bold fs-6">' . number_format($posInfo['total_pos_hours'], 1) . '</div>',
                        "total_dept_hours" => number_format($deptInfo['total_dept_hours'], 1) 
                    ];
                }
            }
            return response()->json(['data' => $data]);
        }
        return view('admin.reports.department_overview', array_merge(compact('targetHours'), $options));
    }

    public function generateLinks()
    {
        $departments = User::where('status', 'active')
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
        
        $links = [];
        foreach ($departments as $dept) {
            $links[$dept] = \Illuminate\Support\Facades\URL::signedRoute('shared.reports.index', ['department' => $dept]);
        }

        return view('admin.reports.links', compact('links'));
    }
}
