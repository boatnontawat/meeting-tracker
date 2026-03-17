@extends('admin.layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
    /* ป้องกันตารางล้นจอและบังคับการตัดคำ */
    .table-nowrap th, .table-nowrap td { white-space: nowrap; vertical-align: middle; }
    .topic-cell {
        white-space: normal !important; 
        min-width: 200px; 
        max-width: 350px;
    }
    div.dt-buttons .btn { margin: 2px; }
</style>

<div class="container-fluid py-4 px-3 px-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-event text-primary me-2"></i> จัดการการประชุม</h2>
        <a href="{{ route('admin.meetings.create') }}" class="btn btn-primary shadow-sm w-100 w-md-auto btn-lg">
            <i class="bi bi-plus-circle me-1"></i> เพิ่มการประชุม
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius: 1rem; overflow: hidden;">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 table-nowrap w-100" id="adminMeetingsTable">
                    <thead class="table-dark text-center align-middle">
                        <tr>
                            <th>ID</th>
                            <th>เจ้าหน้าที่</th>
                            <th class="topic-cell">เรื่องประชุม</th>
                            <th>เริ่ม</th>
                            <th>สิ้นสุด</th>
                            <th>รวม (ชม.)</th>
                            <th>เดือน-ปี</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($meetings as $meeting)
                        <tr>
                            <td class="text-center">{{ $meeting->id }}</td>
                            <td class="fw-bold">{{ $meeting->user->name ?? 'ไม่พบผู้ใช้' }}</td>
                            <td class="topic-cell">{{ $meeting->topic }}</td>
                            
                            <td class="text-center">{{ \Carbon\Carbon::parse($meeting->start_time)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($meeting->end_time)->format('d/m/Y') }}</td>
                            
                            <td class="text-center text-primary fw-bold">
                                @php
                                    $val = floatval($meeting->total_hours);
                                @endphp
                                
                                {{ $val == floor($val) ? $val . ' ชม.' : number_format($val, 1) . ' ชม.' }}
                            </td>
                            
                            <td class="text-center">{{ $meeting->month_year }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.meetings.edit', $meeting->id) }}" class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-square"></i></a>
                                <form action="{{ route('admin.meetings.destroy', $meeting->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ลบข้อมูลการประชุมนี้ ใช่หรือไม่?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger shadow-sm"><i class="bi bi-trash"></i></button>
                                </form>
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
        var table = $('#adminMeetingsTable').DataTable({
            "scrollX": true,
            "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
            "pageLength": 100,
            "order": [[ 0, "desc" ]],
            
            // ปรับ Layout ให้เรียงซ้อนกันบนจอมือถือ
            "dom": "<'row mb-3 align-items-center'<'col-12 col-md-4 mb-2 mb-md-0 d-flex justify-content-center justify-content-md-start'l><'col-12 col-md-4 mb-2 mb-md-0 d-flex justify-content-center flex-wrap'B><'col-12 col-md-4 d-flex justify-content-center justify-content-md-end'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row mt-3'<'col-12 col-md-5 d-flex justify-content-center justify-content-md-start'i><'col-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>",
                   
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel-fill"></i> Excel',
                    className: 'btn btn-success btn-sm shadow-sm rounded-pill px-3',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } 
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> พิมพ์',
                    className: 'btn btn-danger btn-sm shadow-sm rounded-pill px-3',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } 
                }
            ],

            "language": {
                "lengthMenu": "แสดง _MENU_ รายการ",
                "search": "🔍 ค้นหา:",
                "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน",
                "info": "แสดงรายการที่ _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "infoFiltered": "(กรองจาก _MAX_ รายการ)",
                "paginate": { "first": "แรกสุด", "last": "ท้ายสุด", "next": "ถัดไป", "previous": "ก่อนหน้า" }
            }
        });

        // บังคับคำนวณขนาดตารางใหม่เมื่อ Resize (แก้บัคสเกลเพี้ยนเวลาหมุนมือถือ)
        setTimeout(function(){ table.columns.adjust().draw(); }, 150);
        $(window).on('resize', function () { table.columns.adjust(); });
    });
</script>
@endsection