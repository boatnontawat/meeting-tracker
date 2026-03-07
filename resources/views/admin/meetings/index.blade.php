@extends('admin.layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
    .topic-cell {
        white-space: normal !important; /* บังคับให้ข้อความตัดขึ้นบรรทัดใหม่ได้ */
        min-width: 250px; 
        max-width: 400px; /* ปรับความกว้างสูงสุดได้ตามต้องการ */
    }
    /* ตกแต่งระยะห่างของปุ่ม Export */
    div.dt-buttons .btn { margin: 0 5px; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📅 จัดการการประชุม (Meetings)</h2>
        <a href="{{ route('admin.meetings.create') }}" class="btn btn-primary">+ เพิ่มการประชุม</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0" id="adminMeetingsTable" style="width: 100%;">
                    <thead class="table-dark">
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
                        <tr class="align-middle">
                            <td class="text-center">{{ $meeting->id }}</td>
                            <td class="fw-bold">{{ $meeting->user->name ?? 'ไม่พบผู้ใช้' }}</td>
                            <td class="topic-cell">{{ $meeting->topic }}</td>
                            <td>{{ \Carbon\Carbon::parse($meeting->start_time)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($meeting->end_time)->format('d/m/Y') }}</td>
                            <td class="text-center text-danger fw-bold">{{ $meeting->total_hours }}</td>
                            <td class="text-center">{{ $meeting->month_year }}</td>
                            <td class="text-center" style="white-space: nowrap;">
                                <a href="{{ route('admin.meetings.edit', $meeting->id) }}" class="btn btn-sm btn-warning text-dark"><i class="bi bi-pencil-square"></i></a>
                                <form action="{{ route('admin.meetings.destroy', $meeting->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ลบข้อมูลการประชุมนี้ ใช่หรือไม่?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
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
        $('#adminMeetingsTable').DataTable({
            "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
            "pageLength": 100,
            "order": [[ 0, "desc" ]], // เรียงจาก ID ล่าสุด
            
            // 🔴 กำหนด Layout ของ DataTables
            "dom": "<'row mb-3 align-items-center'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-center'B><'col-sm-12 col-md-4'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                   
            // 🔴 ตั้งค่าปุ่ม Export
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel-fill"></i> Export Excel',
                    className: 'btn btn-success btn-sm shadow-sm rounded-pill px-3',
                    title: 'รายการจัดการการประชุม',
                    // ไม่เอาคอลัมน์สุดท้าย (จัดการ) ไปโชว์ใน Excel
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } 
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> พิมพ์ / PDF',
                    className: 'btn btn-danger btn-sm shadow-sm rounded-pill px-3',
                    title: 'รายการจัดการการประชุม',
                    // ไม่เอาคอลัมน์สุดท้าย (จัดการ) ไปโชว์ใน PDF
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
    });
</script>
@endsection