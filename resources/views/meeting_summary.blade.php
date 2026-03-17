<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางสรุปข้อมูลการประชุม</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    
    <style>
        body { background-color: #f8f9fa; overflow-x: hidden; }
        .table-nowrap th, .table-nowrap td { white-space: nowrap; vertical-align: middle; }
        .table-dark { background-color: #212529 !important; }
        
        /* ปรับให้ wrap-cell ยืดหยุ่นขึ้นในจอมือถือ */
        .topic-cell { white-space: normal !important; min-width: 200px; max-width: 350px; }
        .wrap-cell { white-space: normal !important; min-width: 150px; max-width: 250px; }
        
        .table-danger { background-color: #f8d7da !important; }
        .card { border-radius: 1rem; overflow: hidden; }
        
        /* แก้ปัญหาปุ่ม DataTables เบียดกันบนมือถือ */
        div.dt-buttons .btn { margin: 2px; }
    </style>
</head>
<body>

<div class="container-fluid py-4 px-3 px-lg-5">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm text-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <strong>สำเร็จ!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div class="w-100">
            <h3 class="fw-bold text-primary mb-2">📊 ตารางสรุปการเข้าประชุม</h3>
            <p class="text-muted mb-0 small">
                <i class="bi bi-calendar3"></i> ช่วงเวลาประเมิน: 
                <span class="badge bg-primary px-2 shadow-sm">
                    {{ \App\Models\Setting::where('key', 'filter_start_month')->value('value') ?? 'N/A' }} ถึง {{ \App\Models\Setting::where('key', 'filter_end_month')->value('value') ?? 'N/A' }}
                </span>
            </p>
        </div>
        <div class="w-100 text-md-end">
            <a href="{{ route('form.create') }}" class="btn btn-success btn-lg shadow-sm w-100 w-md-auto">
                <i class="bi bi-plus-circle me-1"></i> บันทึกข้อมูลเพิ่ม
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 bg-white">
        <div class="card-body p-3">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-building"></i> หน่วยงาน</label>
                    <select id="department" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($filterDepartments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-person-badge"></i> ตำแหน่ง</label>
                    <select id="position" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($filterPositions as $pos)
                            <option value="{{ $pos }}">{{ $pos }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-person-check"></i> สถานะการทำงาน</label>
                    <select id="status" class="form-select form-select-sm">
                        <option value="active" selected>ปฏิบัติงาน</option>
                        <option value="inactive">ลาออก</option>
                        <option value="all">ทั้งหมด</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i> กรองข้อมูล</button>
                    <button type="button" id="btnReset" class="btn btn-secondary btn-sm w-100"><i class="bi bi-arrow-clockwise"></i> ล้างค่า</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-2 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 table-nowrap w-100" id="meetingTable">
                    <thead class="table-dark text-center align-middle">
                        <tr>
                            <th>ลำดับ</th>
                            <th>เจ้าหน้าที่</th>
                            <th>แผนก</th>
                            <th>ตำแหน่ง</th>
                            <th>วันเริ่ม</th>
                            <th>วันสิ้นสุด</th>
                            <th>ชม.</th>
                            <th class="topic-cell">เรื่องประชุม/อบรม</th>
                            <th>ประเภท</th>
                            <th class="wrap-cell">หน่วยงานที่จัด</th>
                            <th class="wrap-cell">สถานที่</th>
                            <th>สถานะ</th>
                            <th>เดือน-ปี</th>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#meetingTable').DataTable({
            "scrollX": true,
            "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
            "pageLength": 50,
            
            /* 🌟 ระบบ AJAX สำหรับโหลดข้อมูลความเร็วสูง */
            "ajax": {
                "url": "{{ route('form.summary') }}",
                "type": "GET",
                "data": function (d) {
                    // ส่งค่าจากกล่องค้นหาไปให้ Controller
                    d.department = $('#department').val();
                    d.position = $('#position').val();
                    d.status = $('#status').val();
                }
            },
            "deferRender": true, // ช่วยประหยัด RAM ให้เบราว์เซอร์
            
            /* 🌟 ผูกข้อมูลจาก Controller ลงตาราง (ต้องเรียงให้ตรงกับ <thead>) */
            "columns": [
                { data: null, render: function (data, type, row, meta) { return meta.row + 1; }, className: "text-center" },
                { data: 'user_name', defaultContent: '-', className: "fw-bold" },
                { data: 'user_department', defaultContent: '-' },
                { data: 'user_position', defaultContent: '-' },
                // 🌟 ปรับวันที่ให้เป็น ค.ศ. (เรียงแบบ วัน/เดือน/ปี)
                { 
                    data: 'start_time_formatted', 
                    className: "text-center",
                    render: function(data, type, row) {
                        if (type === 'display' && row.start_time) {
                            let dateObj = new Date(row.start_time);
                            let day = String(dateObj.getDate()).padStart(2, '0');
                            let month = String(dateObj.getMonth() + 1).padStart(2, '0');
                            let year = dateObj.getFullYear(); // ใช้ ค.ศ.
                            let hours = String(dateObj.getHours()).padStart(2, '0');
                            let minutes = String(dateObj.getMinutes()).padStart(2, '0');
                            return `${day}/${month}/${year} ${hours}:${minutes}`;
                        }
                        return data;
                    }
                },
                { 
                    data: 'end_time_formatted', 
                    className: "text-center",
                    render: function(data, type, row) {
                        if (type === 'display' && row.end_time) {
                            let dateObj = new Date(row.end_time);
                            let day = String(dateObj.getDate()).padStart(2, '0');
                            let month = String(dateObj.getMonth() + 1).padStart(2, '0');
                            let year = dateObj.getFullYear(); // ใช้ ค.ศ.
                            let hours = String(dateObj.getHours()).padStart(2, '0');
                            let minutes = String(dateObj.getMinutes()).padStart(2, '0');
                            return `${day}/${month}/${year} ${hours}:${minutes}`;
                        }
                        return data;
                    }
                },
                // 🌟 แสดงชั่วโมงเป็นทศนิยมและเติมคำว่า "ชม."
                { 
                    data: 'total_hours', 
                    className: "text-center fw-bold text-primary",
                    render: function(data, type, row) {
                        if(type === 'display') {
                            let hours = parseFloat(data);
                            if(isNaN(hours)) return data; 
                            return hours % 1 === 0 ? hours + ' ชม.' : hours.toFixed(1) + ' ชม.';
                        }
                        return data;
                    }
                },
                { data: 'topic', className: "topic-cell" },
                { data: 'meeting_type', defaultContent: '-' },
                { data: 'organizer', className: "wrap-cell", defaultContent: '-' },
                { data: 'location', className: "wrap-cell", defaultContent: '-' },
                { 
                    data: 'user_status', 
                    className: "text-center",
                    render: function(data, type, row) {
                        return (data === 'active' || data === null) 
                            ? '<span class="badge bg-success">ปฏิบัติงาน</span>' 
                            : '<span class="badge bg-secondary">ลาออก</span>';
                    }
                },
                { data: 'month_year', className: "text-center" }
            ],

            /* 🌟 ใส่สีแดงให้แถวที่ชั่วโมงเป็น 0 */
            "createdRow": function(row, data, dataIndex) {
                if (data.total_hours == 0) {
                    $(row).addClass('table-danger');
                }
            },

            "dom": "<'row mb-3'<'col-12 col-md-4 mb-2 mb-md-0 d-flex justify-content-center justify-content-md-start'l><'col-12 col-md-4 mb-2 mb-md-0 d-flex justify-content-center flex-wrap'B><'col-12 col-md-4 d-flex justify-content-center justify-content-md-end'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row mt-3'<'col-12 col-md-5 d-flex justify-content-center justify-content-md-start'i><'col-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>",
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel-fill"></i> Excel',
                    className: 'btn btn-success btn-sm shadow-sm rounded-pill px-3',
                    title: 'ตารางสรุปการประชุมเจ้าหน้าที่',
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> พิมพ์',
                    className: 'btn btn-danger btn-sm shadow-sm rounded-pill px-3',
                    title: 'ตารางสรุปการประชุมเจ้าหน้าที่',
                    exportOptions: { columns: ':visible' }
                }
            ],
            "language": {
                "lengthMenu": "แสดง _MENU_ รายการ",
                "search": "🔍 ค้นหา:",
                "zeroRecords": "ไม่พบข้อมูลที่ตรงตามเงื่อนไขระบุ", 
                "info": "แสดงรายการที่ _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                "paginate": { "first": "หน้าแรก", "last": "หน้าสุดท้าย", "next": "ถัดไป", "previous": "ก่อนหน้า" }
            }
        });

        // 🌟 ฟังก์ชันกดปุ่ม "กรองข้อมูล" ให้โหลดตารางใหม่ (ไม่ต้องโหลดทั้งหน้าเว็บ)
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload(); 
        });

        // 🌟 ฟังก์ชันกดปุ่ม "ล้างค่า" ให้เคลียร์ช่องแล้วโหลดตารางใหม่
        $('#btnReset').on('click', function() {
            $('#department').val('');
            $('#position').val('');
            $('#status').val('active');
            table.ajax.reload();
        });

        setTimeout(function() { table.columns.adjust().draw(); }, 150);
        $(window).on('resize', function () { table.columns.adjust(); });
    });
</script>
</body>
</html>