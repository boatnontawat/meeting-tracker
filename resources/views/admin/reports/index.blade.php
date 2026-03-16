@extends('admin.layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<style>
    div.dt-buttons .btn { margin: 2px; }
    /* ทำให้ Tabs สามารถสไลด์ซ้ายขวาได้บนมือถือ */
    .nav-tabs-scrollable { flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; white-space: nowrap; -webkit-overflow-scrolling: touch; }
    .nav-tabs-scrollable::-webkit-scrollbar { height: 4px; }
    .nav-tabs-scrollable::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
</style>

<div class="container-fluid px-3 px-md-4 mb-5">
    <ul class="nav nav-tabs nav-tabs-scrollable mb-4 mt-3 border-bottom-2 pb-1">
        <li class="nav-item">
            <a class="nav-link text-dark" href="{{ route('admin.reports.index') }}">รายบุคคล (สรุป 10 วัน)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark" href="{{ route('admin.reports.master') }}">Master Summary (รายแผนก)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active fw-bold text-primary border-bottom-0 shadow-sm" href="{{ route('admin.reports.pivot') }}">Sum Pivot (รายเดือน)</a>
        </li>
        <li class="nav-item">
        <a class="nav-link text-dark" href="{{ route('admin.reports.department') }}">ภาพรวมหน่วยงาน</a>
        </li>
    </ul>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="text-primary fw-bold mb-1">📈 รายงานสรุปการอบรม (เกณฑ์ {{ $targetHours }} ชม.)</h2>
            <p class="text-muted mb-0 small">
                <i class="bi bi-calendar3"></i> ช่วงเวลาประเมิน: 
                <span class="badge bg-secondary px-2">{{ \App\Models\Setting::where('key', 'filter_start_month')->value('value') }}</span> ถึง 
                <span class="badge bg-secondary px-2">{{ \App\Models\Setting::where('key', 'filter_end_month')->value('value') }}</span>
            </p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body p-3">
            <form action="{{ request()->url() }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-building"></i> หน่วยงาน</label>
                    <select name="department" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($filterDepartments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-person-badge"></i> ตำแหน่ง</label>
                    <select name="position" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($filterPositions as $pos)
                            <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-4 col-lg-2">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-person-check"></i> สถานะการทำงาน</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>ปฏิบัติงาน</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>ลาออก</option>
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                    </select>
                </div>
                <div class="col-12 col-sm-4 col-lg-2">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-bullseye"></i> เกณฑ์ (KPI)</label>
                    <select name="kpi_status" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="passed" {{ request('kpi_status') == 'passed' ? 'selected' : '' }}>✅ ผ่าน</option>
                        <option value="failed" {{ request('kpi_status') == 'failed' ? 'selected' : '' }}>❌ ไม่ผ่าน</option>
                    </select>
                </div>
                <div class="col-12 col-sm-4 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-funnel"></i> กรอง</button>
                    <a href="{{ request()->url() }}" class="btn btn-secondary btn-sm flex-fill">ล้างค่า</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-2 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 text-center table-nowrap w-100" id="reportTable">
                    <thead class="table-dark align-middle">
                        <tr>
                            <th>ลำดับ</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>แผนก</th>
                            <th>ตำแหน่ง</th>
                            <th class="bg-primary text-white">รวม ชม.อบรม</th>
                            <th class="bg-info text-dark" style="min-width: 150px;">ความคืบหน้า (ร้อยละ)</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                        @php
                            // 🌟 กำหนดสีของทั้งแถวตามเปอร์เซ็นต์ KPI
                            $rowClass = 'table-danger'; // ค่าเริ่มต้นสีแดง (ต่ำกว่า 50%)
                            
                            if ($user->kpi_percentage >= 100) {
                                $rowClass = 'table-success'; // ครบ 100% ให้เป็นแถวสีเขียว
                            } elseif ($user->kpi_percentage >= 50) {
                                $rowClass = 'table-warning'; // 50% ขึ้นไป ให้เป็นแถวสีเหลือง
                            }
                        @endphp

                        <tr class="align-middle {{ $rowClass }}">
                            <td>{{ $index + 1 }}</td>
                            <td class="text-start fw-bold">
                                {{ $user->name }}
                                @if($user->status !== 'active')
                                    <span class="badge bg-danger ms-1" style="font-size: 0.75em;">ลาออก</span>
                                @endif
                            </td>
                            <td>{{ $user->department }}</td>
                            <td>{{ $user->position }}</td>
                            <td class="text-danger fw-bold fs-6">{{ number_format($user->total_hours, 1) }}</td>
                            <td>
                                <div class="progress shadow-sm" style="height: 20px; font-size: 12px; background-color: rgba(255,255,255,0.5);">
                                    <div class="progress-bar bg-dark text-white fw-bold" 
                                         role="progressbar" 
                                         style="width: {{ $user->kpi_percentage > 100 ? 100 : $user->kpi_percentage }}%;" 
                                         aria-valuenow="{{ $user->kpi_percentage }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ number_format($user->kpi_percentage, 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->kpi_passed)
                                    <span class="badge bg-success px-2 py-1 shadow-sm">✅ ผ่าน</span>
                                @else
                                    <span class="badge bg-danger px-2 py-1 shadow-sm">❌ ไม่ผ่าน</span>
                                @endif
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
        var table = $('#reportTable').DataTable({
            "scrollX": true,
            "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
            "pageLength": 100,
            
            // Responsive DOM จัด Layout บนมือถือ
            "dom": "<'row mb-3 align-items-center'<'col-12 col-md-4 mb-2 mb-md-0 d-flex justify-content-center justify-content-md-start'l><'col-12 col-md-4 mb-2 mb-md-0 d-flex justify-content-center flex-wrap'B><'col-12 col-md-4 d-flex justify-content-center justify-content-md-end'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row mt-3'<'col-12 col-md-5 d-flex justify-content-center justify-content-md-start'i><'col-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>",
                   
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel-fill"></i> Excel',
                    className: 'btn btn-success btn-sm shadow-sm rounded-pill px-3',
                    title: 'รายงานสรุปการอบรมรายบุคคล',
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> พิมพ์ / PDF',
                    className: 'btn btn-danger btn-sm shadow-sm rounded-pill px-3',
                    title: 'รายงานสรุปการอบรมรายบุคคล',
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
        
        setTimeout(function(){ table.columns.adjust().draw(); }, 150);
        $(window).on('resize', function () { table.columns.adjust(); });
    });
</script>
@endsection