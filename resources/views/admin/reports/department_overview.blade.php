@extends('admin.layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<style>
    div.dt-buttons .btn { margin: 2px; }
    .nav-tabs-scrollable { flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; white-space: nowrap; -webkit-overflow-scrolling: touch; }
    .nav-tabs-scrollable::-webkit-scrollbar { height: 4px; }
    .nav-tabs-scrollable::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .table-nowrap th, .table-nowrap td { white-space: nowrap; vertical-align: middle; }
    
    /* 🌟 สไตล์สำหรับแถวที่เป็นหัวข้อกลุ่ม (ชื่อแผนก) */
    tr.group, tr.group:hover {
        background-color: #e0f2fe !important; /* สีฟ้าอ่อนสบายตา */
        border-bottom: 2px solid #bae6fd;
    }
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
            <h2 class="text-primary fw-bold mb-1">🏥 ภาพรวมหน่วยงาน (แยกตามตำแหน่ง)</h2>
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
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-building"></i> หน่วยงาน</label>
                    <select name="department" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($filterDepartments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-funnel"></i> กรอง</button>
                    <a href="{{ request()->url() }}" class="btn btn-secondary btn-sm flex-fill">ล้างค่า</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-2 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 text-center table-nowrap w-100" id="deptOverviewTable">
                    <thead class="table-dark align-middle">
                        <tr>
                            <th width="10%">ลำดับ</th>
                            <th>แผนก</th> <th width="40%">ตำแหน่ง</th>
                            <th width="25%" class="bg-secondary text-white">คนทั้งหมด</th>
                            <th width="25%" class="bg-primary text-white">ชั่วโมงรวมของตำแหน่ง</th>
                            <th>รวมชั่วโมงแผนก</th> </tr>
                    </thead>
                    <tbody>
                        @foreach($flatData as $index => $row)
                        <tr class="align-middle">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['department'] }}</td> <td class="text-start ps-4"><i class="bi bi-person-badge text-muted me-2"></i> {{ $row['position'] }}</td>
                            <td class="fw-bold">{{ $row['staff_count'] }}</td>
                            <td class="text-primary fw-bold fs-6">{{ number_format($row['total_pos_hours'], 1) }}</td>
                            <td>{{ number_format($row['total_dept_hours'], 1) }}</td> </tr>
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
        var table = $('#deptOverviewTable').DataTable({
            "scrollX": true,
            "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
            "pageLength": 100,
            
            // 🌟 1. ซ่อนคอลัมน์ แผนก (1) และ รวมชั่วโมงแผนก (5) ไม่ให้เกะกะ
            "columnDefs": [
                { "visible": false, "targets": [1, 5] }
            ],
            // 🌟 2. ล็อกให้เรียงลำดับตามชื่อแผนกเป็นหลัก เพื่อให้ระบบจัดกลุ่มทำงานได้สมบูรณ์
            "order": [[ 1, 'asc' ]],

            "dom": "<'row mb-3 align-items-center'<'col-12 col-md-4 mb-2 mb-md-0 d-flex justify-content-center justify-content-md-start'l><'col-12 col-md-4 mb-2 mb-md-0 d-flex justify-content-center flex-wrap'B><'col-12 col-md-4 d-flex justify-content-center justify-content-md-end'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row mt-3'<'col-12 col-md-5 d-flex justify-content-center justify-content-md-start'i><'col-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>",
                   
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel-fill"></i> Excel',
                    className: 'btn btn-success btn-sm shadow-sm rounded-pill px-3',
                    title: 'รายงานภาพรวมหน่วยงาน',
                    // Export ข้อมูลไป Excel ให้ครบทุกคอลัมน์ (รวมคอลัมน์ที่ซ่อนไว้ด้วย)
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] } 
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> พิมพ์ / PDF',
                    className: 'btn btn-danger btn-sm shadow-sm rounded-pill px-3',
                    title: 'รายงานภาพรวมหน่วยงาน',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] } 
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
            },

            // 🌟 3. ระบบสร้างแถว "จำแนกแผนก" คั่นให้อัตโนมัติ (Group Row)
            "drawCallback": function (settings) {
                var api = this.api();
                var rows = api.rows({page:'current'}).nodes();
                var last = null;

                api.column(1, {page:'current'}).data().each(function (group, i) {
                    if (last !== group) {
                        var totalHours = api.cell(i, 5).data(); // ดึงชั่วโมงรวมแผนกมาโชว์
                        
                        $(rows).eq(i).before(
                            '<tr class="group">' +
                                '<td colspan="4" class="text-start py-3">' +
                                    '<div class="d-flex justify-content-between align-items-center">' +
                                        '<span class="fw-bold fs-5 text-dark"><i class="bi bi-building-fill text-primary me-2"></i> แผนก: <span class="text-primary">' + group + '</span></span>' +
                                        '<span class="badge bg-success fs-6 shadow-sm px-3 py-2"><i class="bi bi-clock-history me-1"></i> รวมทั้งแผนก: ' + totalHours + ' ชม.</span>' +
                                    '</div>' +
                                '</td>' +
                            '</tr>'
                        );
                        last = group;
                    }
                });
            }
        });
    });
</script>
@endsection