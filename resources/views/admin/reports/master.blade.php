@extends('admin.layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
    div.dt-buttons .btn { margin: 0 5px; }
</style>

<div class="container-fluid px-3 px-md-4 mb-5">
    <ul class="nav nav-tabs mb-4 mt-3 border-bottom-2">
        <li class="nav-item"><a class="nav-link text-dark" href="{{ route('admin.reports.index') }}">รายบุคคล (สรุป 10 วัน)</a></li>
        <li class="nav-item"><a class="nav-link active fw-bold text-primary border-bottom-0 shadow-sm" href="{{ route('admin.reports.master') }}">Master Summary (รายแผนก)</a></li>
        <li class="nav-item"><a class="nav-link text-dark" href="{{ route('admin.reports.pivot') }}">Sum Pivot (รายเดือน)</a></li>
    </ul>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="text-primary fw-bold mb-1">📊 Master Summary (เกณฑ์ผ่าน {{ $targetHours }} ชม.)</h2>
            <p class="text-muted mb-0">
                <i class="bi bi-calendar3"></i> ช่วงเวลาประเมิน: 
                <span class="badge bg-secondary fs-6">{{ \App\Models\Setting::where('key', 'filter_start_month')->value('value') }}</span> ถึง 
                <span class="badge bg-secondary fs-6">{{ \App\Models\Setting::where('key', 'filter_end_month')->value('value') }}</span>
            </p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body p-3">
            <form action="{{ request()->url() }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-building"></i> หน่วยงาน</label>
                    <select name="department" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($filterDepartments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-person-badge"></i> ตำแหน่ง</label>
                    <select name="position" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($filterPositions as $pos)
                            <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-person-check"></i> สถานะการทำงาน</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>ปฏิบัติงาน</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>ลาออก</option>
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-bullseye"></i> เกณฑ์ (KPI)</label>
                    <select name="kpi_status" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="passed" {{ request('kpi_status') == 'passed' ? 'selected' : '' }}>✅ ผ่าน</option>
                        <option value="failed" {{ request('kpi_status') == 'failed' ? 'selected' : '' }}>❌ ไม่ผ่าน</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 mb-1"><i class="bi bi-funnel"></i> กรองข้อมูล</button>
                    <a href="{{ request()->url() }}" class="btn btn-secondary btn-sm w-100">ล้างค่า</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 w-100">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 text-center" id="masterTable" style="width: 100%;">
                    <thead class="table-dark">
                        <tr>
                            <th class="align-middle">แผนก</th>
                            <th class="align-middle text-start">ตำแหน่ง</th>
                            <th class="align-middle">จำนวน จนท.</th>
                            <th class="align-middle">ผ่านเกณฑ์ (คน)</th>
                            <th class="align-middle">% ผู้ผ่านเกณฑ์</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $deptName => $deptData)
                            @foreach($deptData['positions'] as $posName => $posData)
                            <tr>
                                <td class="text-start fw-bold">{{ $deptName }}</td>
                                <td class="text-start">{{ $posName }}</td>
                                <td>{{ $posData['staff_count'] }}</td>
                                <td>{{ $posData['passed_count'] }}</td>
                                <td>
                                    @if($posData['staff_count'] > 0)
                                        @php $percent = ($posData['passed_count'] / $posData['staff_count']) * 100; @endphp
                                        <span class="{{ $percent >= 100 ? 'text-success fw-bold' : '' }}">
                                            {{ number_format($percent, 1) }}%
                                        </span>
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            <tr class="table-secondary fw-bold text-primary">
                                <td class="text-end">{{ $deptName }}</td>
                                <td class="text-end">ผลรวม</td>
                                <td>{{ $deptData['total_staff'] }}</td>
                                <td>{{ $deptData['total_passed'] }}</td>
                                <td>
                                    {{ $deptData['total_staff'] > 0 ? number_format(($deptData['total_passed'] / $deptData['total_staff']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        $('#masterTable').DataTable({
            "ordering": false,
            "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
            "pageLength": -1, 
            
            "dom": "<'row mb-3 align-items-center'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-center'B><'col-sm-12 col-md-4'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                   
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel-fill"></i> Export Excel',
                    className: 'btn btn-success btn-sm shadow-sm rounded-pill px-3',
                    title: 'รายงาน Master Summary (สรุปรายแผนก)',
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> พิมพ์ / PDF',
                    className: 'btn btn-danger btn-sm shadow-sm rounded-pill px-3',
                    title: 'รายงาน Master Summary (สรุปรายแผนก)',
                    exportOptions: { columns: ':visible' }
                }
            ],
            
            "language": {
                "lengthMenu": "แสดง _MENU_ รายการ",
                "search": "🔍 ค้นหา:",
                "zeroRecords": "ไม่พบข้อมูล",
                "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "infoFiltered": "(กรองจาก _MAX_ รายการ)",
                "paginate": { "first": "แรกสุด", "last": "ท้ายสุด", "next": "ถัดไป", "previous": "ก่อนหน้า" }
            }
        });
    });
</script>
@endsection