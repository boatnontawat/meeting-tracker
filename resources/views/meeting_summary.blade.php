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
        
        .topic-cell { white-space: normal !important; min-width: 250px; max-width: 350px; }
        .wrap-cell { white-space: normal !important; min-width: 150px; max-width: 250px; }
        
        div.dt-buttons .btn { margin: 0 5px; }
        .table-danger { background-color: #f8d7da !important; }
        
        .card { border-radius: 1rem; overflow: hidden; }
        .container-fluid { max-width: 100%; }
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
        <div>
            <h2 class="fw-bold text-primary mb-1">📊 ตารางสรุปการเข้าประชุมของเจ้าหน้าที่</h2>
            <p class="text-muted mb-0">
                <i class="bi bi-calendar3"></i> ช่วงเวลาประเมิน: 
                <span class="badge bg-primary px-3 shadow-sm">
                    {{ \App\Models\Setting::where('key', 'filter_start_month')->value('value') ?? 'N/A' }} ถึง {{ \App\Models\Setting::where('key', 'filter_end_month')->value('value') ?? 'N/A' }}
                </span>
            </p>
        </div>
        <div>
            <a href="{{ route('form.create') }}" class="btn btn-success btn-lg shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> บันทึกข้อมูลเพิ่ม
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 bg-white">
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
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small"><i class="bi bi-person-check"></i> สถานะการทำงาน</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>ปฏิบัติงาน</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>ลาออก</option>
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel"></i> กรองข้อมูล</button>
                    <a href="{{ request()->url() }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i> ล้างค่า</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-2 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 table-nowrap w-100" id="meetingTable">
                    <thead class="table-dark text-center">
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
                        @foreach($records as $index => $record)
                        <tr class="{{ $record->total_hours == 0 ? 'table-danger' : '' }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $record->user->name ?? '-' }}</td>
                            <td>{{ $record->user->department ?? '-' }}</td>
                            <td>{{ $record->user->position ?? '-' }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($record->start_time)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($record->end_time)->format('d/m/Y') }}</td>
                            <td class="text-center text-danger fw-bold fs-6">{{ $record->total_hours }}</td>
                            <td class="topic-cell">{{ $record->topic }}</td>
                            <td>{{ $record->meeting_type }}</td>
                            <td class="wrap-cell">{{ $record->organizer }}</td>
                            <td class="wrap-cell">{{ $record->location }}</td>
                            <td class="text-center">
                                @if(isset($record->user) && $record->user->status == 'active')
                                    <span class="badge bg-success">ปฏิบัติงาน</span>
                                @else
                                    <span class="badge bg-secondary">ลาออก</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $record->month_year }}</td>
                        </tr>
                        @endforeach
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
            "dom": "<'row mb-3 align-items-center'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-center'B><'col-sm-12 col-md-4'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel-fill"></i> Export Excel',
                    className: 'btn btn-success btn-sm shadow-sm rounded-pill px-3',
                    title: 'ตารางสรุปการประชุมเจ้าหน้าที่',
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> พิมพ์ / PDF',
                    className: 'btn btn-danger btn-sm shadow-sm rounded-pill px-3',
                    title: 'ตารางสรุปการประชุมเจ้าหน้าที่',
                    exportOptions: { columns: ':visible' }
                }
            ],
            "language": {
                "lengthMenu": "แสดง _MENU_ รายการ",
                "search": "🔍 ค้นหา:",
                // 🔴 DataTables จะแสดงข้อความตรงนี้แทนเมื่อกรองแล้วไม่เจอข้อมูล
                "zeroRecords": "ไม่พบข้อมูลที่ตรงตามเงื่อนไขระบุ", 
                "info": "แสดงรายการที่ _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                "paginate": { "first": "หน้าแรก", "last": "หน้าสุดท้าย", "next": "ถัดไป", "previous": "ก่อนหน้า" }
            }
        });

        setTimeout(function() {
            table.columns.adjust().draw();
        }, 150);
    });
</script>
</body>
</html>