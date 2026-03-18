@extends('admin.layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<style>
    div.dt-buttons .btn { margin: 2px; }
    .table-nowrap th, .table-nowrap td { white-space: nowrap; vertical-align: middle; }
    
    tr.group, tr.group:hover {
        background-color: #e0f2fe !important; 
        border-bottom: 2px solid #bae6fd;
    }
</style>

<div class="container-fluid px-3 px-md-4 mb-5 mt-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="text-primary fw-bold mb-1">🏥 ภาพรวมหน่วยงาน (แผนก: {{ $department }})</h2>
            <p class="text-muted mb-0 small">
                <i class="bi bi-calendar3"></i> ช่วงเวลาประเมิน: 
                <span class="badge bg-secondary px-2">{{ \App\Models\Setting::where('key', 'filter_start_month')->value('value') }}</span> ถึง 
                <span class="badge bg-secondary px-2">{{ \App\Models\Setting::where('key', 'filter_end_month')->value('value') }}</span>
            </p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body p-2 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 text-center table-nowrap w-100" id="deptOverviewTable">
                    <thead class="table-dark align-middle">
                        <tr>
                            <th width="10%">ลำดับ</th>
                            <th width="40%">ตำแหน่ง</th>
                            <th width="25%" class="bg-secondary text-white">คนทั้งหมด</th>
                            <th width="25%" class="bg-primary text-white">ชั่วโมงรวมของตำแหน่ง</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($flatData as $index => $row)
                        <tr class="align-middle">
                            <td>{{ $index + 1 }}</td>
                            <td class="text-start ps-4"><i class="bi bi-person-badge text-muted me-2"></i> {{ $row['position'] }}</td>
                            <td class="fw-bold">{{ $row['staff_count'] }}</td>
                            <td class="text-primary fw-bold fs-6">{{ number_format($row['total_pos_hours'], 1) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(count($flatData) > 0)
                <div class="mt-4 p-3 bg-light border rounded text-end shadow-sm">
                    <span class="fs-5 text-dark me-3">รวมชั่วโมงทั้งหมดของแผนก <strong>{{ $department }}</strong>:</span>
                    <span class="fs-4 fw-bold text-success">{{ number_format($flatData[0]['total_dept_hours'], 1) }} ชั่วโมง</span>
                </div>
            @endif
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

            "dom": "<'row mb-3 align-items-center'<'col-12 col-md-4 mb-2 mb-md-0 d-flex justify-content-center justify-content-md-start'l><'col-12 col-md-4 mb-2 mb-md-0 d-flex justify-content-center flex-wrap'B><'col-12 col-md-4 d-flex justify-content-center justify-content-md-end'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row mt-3'<'col-12 col-md-5 d-flex justify-content-center justify-content-md-start'i><'col-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>",
                   
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel-fill"></i> Excel',
                    className: 'btn btn-success btn-sm shadow-sm rounded-pill px-3',
                    title: 'รายงานภาพรวมหน่วยงาน_{{ $department }}'
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> พิมพ์ / PDF',
                    className: 'btn btn-danger btn-sm shadow-sm rounded-pill px-3',
                    title: 'รายงานภาพรวมหน่วยงาน_{{ $department }}'
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
