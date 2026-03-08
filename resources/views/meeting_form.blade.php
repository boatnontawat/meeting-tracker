<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกชั่วโมงการประชุม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid container-md mt-4 mb-5">
    
    <div class="mb-3 mx-auto" style="max-width: 800px;">
        <a href="{{ url('/summary') }}" class="btn btn-secondary shadow-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left-circle me-2"></i> กลับหน้าตารางสรุป
        </a>
    </div>

    <div class="card shadow border-0 mx-auto" style="max-width: 800px; border-radius: 12px;">
        <div class="card-header bg-primary text-white text-center py-3" style="border-radius: 12px 12px 0 0;">
            <h5 class="mb-0 fw-bold">แบบฟอร์มบันทึกการประชุม</h5>
        </div>
        <div class="card-body p-3 p-md-4">
            
            @if(session('success'))
                <div class="alert alert-success text-center fw-bold shadow-sm">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('form.store') }}" method="POST">
                @csrf

                <div class="p-3 mb-4 bg-white border rounded shadow-sm">
                    <h6 class="text-primary mb-3"><i class="bi bi-person-fill"></i> ข้อมูลผู้เข้าร่วม</h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted small fw-bold">ชื่อ-นามสกุล (พิมพ์เพื่อค้นหา)</label>
                            <input type="text" class="form-control" name="name" id="nameInput" list="nameList" placeholder="เลือกหรือพิมพ์ชื่อ..." required autocomplete="off">
                            <datalist id="nameList">
                                @foreach($users as $user)
                                    <option value="{{ $user->name }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted small fw-bold">แผนก</label>
                            <input type="text" class="form-control bg-light" name="department" id="deptInput" list="deptList" placeholder="แผนก..." required autocomplete="off">
                            <datalist id="deptList">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted small fw-bold">ตำแหน่ง</label>
                            <input type="text" class="form-control bg-light" name="position" id="posInput" list="posList" placeholder="ตำแหน่ง..." required autocomplete="off">
                            <datalist id="posList">
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->position }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                </div>

                <div class="p-3 mb-4 bg-white border rounded shadow-sm">
                    <h6 class="text-primary mb-3"><i class="bi bi-calendar-check"></i> วันเวลาที่จัดการประชุม</h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold">วันที่เริ่ม</label>
                            <input type="date" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold">วันที่สิ้นสุด</label>
                            <input type="date" name="end_time" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold">รวมเวลา (ชั่วโมง)</label>
                            <input type="number" name="total_hours" class="form-control" step="0.1" min="0" placeholder="เช่น 6 หรือ 1.5" required>
                        </div>
                    </div>
                </div>

                <div class="p-3 mb-4 bg-white border rounded shadow-sm">
                    <h6 class="text-primary mb-3"><i class="bi bi-journal-text"></i> รายละเอียดการประชุม</h6>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">เรื่องประชุม/อบรม/หลักสูตร</label>
                        <input type="text" class="form-control" name="topic" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted small fw-bold">ประเภท</label>
                            <select class="form-select" name="meeting_type" required>
                                <option value="ในโรงพยาบาล">ในโรงพยาบาล</option>
                                <option value="นอกโรงพยาบาล">นอกโรงพยาบาล</option>
                                <option value="Online">Online</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted small fw-bold">หน่วยงานที่จัด</label>
                            <input type="text" class="form-control" name="organizer" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted small fw-bold">สถานที่</label>
                            <input type="text" class="form-control" name="location" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label text-muted small fw-bold">เดือน-ปี (Year-Month)</label>
                        <input type="month" class="form-control" name="month_year" required>
                    </div>
                </div>

                <div class="text-center mt-4 d-grid d-md-block">
                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm">
                        <i class="bi bi-floppy-fill me-1"></i> บันทึกข้อมูลลงระบบ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const usersData = @json($users);

    document.getElementById('nameInput').addEventListener('input', function() {
        const selectedName = this.value;
        if (usersData && Array.isArray(usersData)) {
            const matchedUser = usersData.find(user => user.name === selectedName);
            if (matchedUser) {
                document.getElementById('deptInput').value = matchedUser.department || '';
                document.getElementById('posInput').value = matchedUser.position || '';
            } else {
                document.getElementById('deptInput').value = '';
                document.getElementById('posInput').value = '';
            }
        }
    });
</script>

</body>
</html>