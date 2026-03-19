@extends('admin.layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
    .table-nowrap th, .table-nowrap td { white-space: nowrap; vertical-align: middle; }
</style>

<div class="container-fluid py-4 px-3 px-md-4">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-primary me-2"></i> จัดการผู้ใช้งาน (Users)</h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary shadow-sm w-100 w-md-auto btn-lg">
            <i class="bi bi-person-plus-fill me-1"></i> เพิ่มผู้ใช้งาน
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius: 1rem; overflow: hidden;">
        <div class="card-body p-3 p-md-4"> 
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 text-center table-nowrap w-100" id="usersTable">
                    <thead class="table-dark align-middle">
                        <tr>
                            <th>ID</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>แผนก</th>
                            <th>ตำแหน่ง</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#usersTable').DataTable({
            "processing": true,
            "ajax": {
                "url": "{{ route('admin.users.index') }}",
                "type": "GET"
            },
            "columns": [
                { "data": "id" },
                { "data": "name" },
                { "data": "department" },
                { "data": "position" },
                { "data": "status" },
                { "data": "action", "orderable": false, "searchable": false }
            ],
            "scrollX": true, 
            "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
            "pageLength": 100,
            
            "dom": "<'row mb-3 align-items-center'<'col-12 col-md-6 mb-2 mb-md-0 d-flex justify-content-center justify-content-md-start'l><'col-12 col-md-6 d-flex justify-content-center justify-content-md-end'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row mt-3'<'col-12 col-md-5 d-flex justify-content-center justify-content-md-start'i><'col-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>",
            
            "language": {
                "lengthMenu": "แสดง _MENU_ รายการ",
                "search": "🔍 ค้นหา:",
                "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน",
                "info": "แสดงรายการที่ _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                "paginate": { "first": "หน้าแรก", "last": "หน้าสุดท้าย", "next": "ถัดไป", "previous": "ก่อนหน้า" }
            }
        });

        setTimeout(function(){ table.columns.adjust().draw(); }, 150);
        $(window).on('resize', function () { table.columns.adjust(); });
    });
</script>
@endsection
