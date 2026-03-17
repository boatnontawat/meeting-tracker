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
        .wrap-cell { white-space: normal !important; min-width: 150px; }
        
        .card-header-gradient { background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; }
        div.dt-buttons .btn { margin-bottom: 5px; }

        @media (max-width: 768px) {
            .btn-action { width: 100%; margin-bottom: 5px; }
            .filter-row { flex-direction: column; }
            .dataTables_wrapper .row { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="container-fluid container-xl mt-4 mb-5">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <h3 class="fw-bold mb-0 text-dark">
            <i class="bi bi-journal-check text-primary me-2"></i> ตารางประวัติการประชุม
        </h3>
        <div class="d-flex gap-2 w-100 w-md-auto">
            <a href="{{ url('/') }}" class="btn btn-primary shadow-sm flex-fill btn-action rounded-pill">
                <i class="bi bi-plus-circle-fill me-1"></i> บันทึกชั่วโมงใหม่
            </a>
            <a href="{{ url('/admin/login') }}" class="btn btn-dark shadow-sm flex-fill btn-action rounded-pill">
                <i class="bi bi-shield-lock-fill me-1"></i> เข้าสู่ระบบแอดมิน
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem;">
        <div class="card-body p-3 p-md-4">
            <h5 class="fw-bold mb-3 text-secondary"><i class="bi bi-funnel-fill me-2"></i> ค้นหาแบบละเอียด</h5>
            <form id="filterForm" class="row g-2 g-md-3 filter-row">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold">เลือกแผนก</label>
                    <select id="department" class="form-select shadow-sm">
                        <option value="">-- ทุกแผนก --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold">เลือกตำแหน่ง</label>
                    <select id="position" class="form-select shadow-sm">
                        <option value="">-- ทุกตำแหน่ง --</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos }}">{{ $pos }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold">สถานะบุคลากร</label>
                    <select id="status" class="form-select shadow-sm">
                        <option value="active" selected>✅ ปฏิบัติงานอยู่ (Active)</option>
                        <option value="inactive">❌ ลาออก/พ้นสภาพ (Inactive)</option>
                        <option value="">-- ทั้งหมด --</option>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-md-end gap-2 mt-3">
                    <button type="submit" class="btn btn-primary shadow-sm rounded-pill px-4 w-100 w-md-auto">
                        <i class="bi bi-search me-1"></i> กรองข้อมูล
                    </button>
                    <button type="button" id="btnReset" class="btn btn-outline-secondary shadow-sm rounded-pill px-4 w-100 w-md-auto">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> ล้างค่า
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0" style="border-radius: 1rem; overflow: hidden;">
        <div class="card-header card-header-gradient p-3 p-md-4">
            <h5 class="mb-0 fw-bold"><i class="bi bi-table me-2"></i> รายการประชุมที่บันทึกแล้ว</h5>
        </div>
        
        <div class="card-body p-3 p-md-4 bg-white">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle w-100" id="meetingTable">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="py-3">ชื่อ-นามสกุล</th>
                            <th scope="col" class="py-3">แผนก</th>
                            <th scope="col" class="py-3">ตำแหน่ง</th>
                            <th scope="col" class="py-3 text-center">หัวข้อการประชุม</th>
                            <th scope="col" class="py-3 text-center">เริ่มวันที่</th>
                            <th scope="col" class="py-3 text-center">สิ้นสุดวันที่</th>
                            <th scope="col" class="py-3 text-center">ชั่วโมง</th>
                            <th scope="col" class="py-3 text-center">วันที่บันทึก</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#meetingTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ url('/api/meetings') }}",
                data: function (d) {
                    d.department = $('#department').val();
                    d.position = $('#position').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                { data: 'user.name', name: 'user.name', className: 'fw-bold text-dark wrap-cell' },
                { data: 'user.department', name: 'user.department' },
                { data: 'user.position', name: 'user.position', className: 'wrap-cell' },
                { data: 'topic', name: 'topic', className: 'topic-cell text-start' },
                
                // 🌟 ปรับวันที่ให้เป็น ค.ศ. (เรียงแบบ วัน/เดือน/ปี)
                { 
                    data: 'start_time', 
                    name: 'start_time',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            let dateObj = new Date(data);
                            let day = String(dateObj.getDate()).padStart(2, '0');
                            let month = String(dateObj.getMonth() + 1).padStart(2, '0');
                            let year = dateObj.getFullYear(); // ใช้ ค.ศ.
                            let hours = String(dateObj.getHours()).padStart(2, '0');
                            let minutes = String(dateObj.getMinutes()).padStart(2, '0');
                            return `<span class="badge bg-light text-dark border px-2 py-1">${day}/${month}/${year} ${hours}:${minutes}</span>`;
                        }
                        return data;
                    }
                },
                { 
                    data: 'end_time', 
                    name: 'end_time',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            let dateObj = new Date(data);
                            let day = String(dateObj.getDate()).padStart(2, '0');
                            let month = String(dateObj.getMonth() + 1).padStart(2, '0');
                            let year = dateObj.getFullYear(); // ใช้ ค.ศ.
                            let hours = String(dateObj.getHours()).padStart(2, '0');
                            let minutes = String(dateObj.getMinutes()).padStart(2, '0');
                            return `<span class="badge bg-light text-secondary border px-2 py-1">${day}/${month}/${year} ${hours}:${minutes}</span>`;
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },
                
                // 🌟 แก้ไข: แสดงชั่วโมงเป็นทศนิยมและเติมคำว่า "ชม."
                { 
                    data: 'total_hours', 
                    name: 'total_hours', 
                    className: 'text-center fw-bold text-primary',
                    render: function(data, type, row) {
                        if(type === 'display') {
                            let hours = parseFloat(data);
                            if(isNaN(hours)) return data; 
                            return hours % 1 === 0 ? hours + ' ชม.' : hours.toFixed(1) + ' ชม.';
                        }
                        return data;
                    }
                },
                
                { 
                    data: 'created_at', 
                    name: 'created_at',
                    className: 'text-center small text-muted',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            let dateObj = new Date(data);
                            let day = String(dateObj.getDate()).padStart(2, '0');
                            let month = String(dateObj.getMonth() + 1).padStart(2, '0');
                            let year = dateObj.getFullYear(); // ใช้ ค.ศ.
                            return `${day}/${month}/${year}`;
                        }
                        return data;
                    }
                }
            ],
            order: [[7, 'desc']], // เรียงตามวันที่บันทึกล่าสุด

            "dom": "<'row mb-3 align-items-center'<'col-12 col-md-6 mb-2 mb-md-0 d-flex justify-content-center justify-content-md-start'B><'col-12 col-md-6 d-flex justify-content-center justify-content-md-end'f>>" +
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

    });
</script>

</body>
</html>