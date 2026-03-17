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
    
    <div class=\"mb-3 mx-auto\" style=\"max-width: 800px;\">
        <a href="{{ url('/summary') }}" class="btn btn-secondary shadow-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left-circle me-2"></i> กลับหน้าตารางสรุป
        </a>
    </div>

    <div class="card shadow border-0 mx-auto" style="max-width: 800px; border-radius: 1rem;">
        <div class="card-header bg-primary text-white p-4" style="border-radius: 1rem 1rem 0 0;">
            <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i> บันทึกข้อมูลการประชุม (User / Staff)</h4>
            <p class="mb-0 mt-1 opacity-75 small">กรุณากรอกข้อมูลให้ครบถ้วนเพื่อผลประโยชน์ในการนับชั่วโมงของท่าน</p>
        </div>
        
        <div class="card-body p-4 p-md-5 bg-white">
            
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm rounded">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ url('/store') }}" method="POST">
                @csrf
                
                <h5 class="fw-bold text-primary mb-3 border-bottom pb-2">1. ข้อมูลผู้เข้าร่วม</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-dark">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                        <input class="form-control" list="datalistOptions" id="nameInput" name="name" placeholder="พิมพ์เพื่อค้นหาชื่อ..." autocomplete="off" required>
                        <datalist id="datalistOptions">
                            @foreach ($users as $user)
                                <option value="{{ $user->name }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">แผนก</label>
                        <input type="text" class="form-control bg-light" id="deptInput" readonly placeholder="ดึงข้อมูลอัตโนมัติ">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">ตำแหน่ง</label>
                        <input type="text" class="form-control bg-light" id="posInput" readonly placeholder="ดึงข้อมูลอัตโนมัติ">
                    </div>
                </div>

                <h5 class="fw-bold text-primary mb-3 border-bottom pb-2">2. รายละเอียดการประชุม</h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-dark">หัวข้อการประชุม / อบรม <span class="text-danger">*</span></label>
                        <input class="form-control" list="topicOptions" name="topic" placeholder="ระบุหัวข้อ (พิมพ์เพื่อค้นหาประวัติเดิมได้)" autocomplete="off" required>
                        <datalist id="topicOptions">
                            @foreach ($topics as $t)
                                <option value="{{ $t->topic }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark">ประเภทการประชุม <span class="text-danger">*</span></label>
                        <select class="form-select" name="meeting_type" required>
                            <option value="" selected disabled>-- เลือกประเภท --</option>
                            <option value="ประชุมภายในแผนก">ประชุมภายในแผนก</option>
                            <option value="ประชุมระดับองค์กร">ประชุมระดับองค์กร (Townhall)</option>
                            <option value="อบรมทักษะทางวิชาชีพ">อบรมทักษะทางวิชาชีพ</option>
                            <option value="อบรมด้าน Soft Skills">อบรมด้าน Soft Skills</option>
                            <option value="ประชุมกับหน่วยงานภายนอก">ประชุมกับหน่วยงานภายนอก</option>
                            <option value="อื่นๆ">อื่นๆ</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark">เดือน/ปี (ที่นับชั่วโมง) <span class="text-danger">*</span></label>
                        <input type="month" class="form-control" name="month_year" value="{{ date('Y-m') }}" required>
                        <small class="text-muted d-block mt-1">ใช้สำหรับการสรุปรายงานประจำเดือน</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark">ผู้จัด (Organizer) <span class="text-danger">*</span></label>
                        <input class="form-control" list="organizerOptions" name="organizer" placeholder="ระบุผู้จัด" autocomplete="off" required>
                        <datalist id="organizerOptions">
                            @foreach ($organizers as $o)
                                <option value="{{ $o->organizer }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold text-dark">สถานที่ประชุม <span class="text-danger">*</span></label>
                        <input class="form-control" list="locationOptions" name="location" placeholder="ระบุสถานที่" autocomplete="off" required>
                        <datalist id="locationOptions">
                            @foreach ($locations as $l)
                                <option value="{{ $l->location }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <h5 class="fw-bold text-primary mb-3 border-bottom pb-2">3. เวลาที่เข้าร่วม</h5>
                <div class="row g-3 bg-primary bg-opacity-10 p-3 rounded mb-4 border border-primary border-opacity-25">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark"><i class="bi bi-calendar-check me-2 text-primary"></i>วันที่ และ เวลาเริ่ม <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control shadow-sm" name="start_time" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-dark fw-semibold"><i class="bi bi-calendar-x me-2 text-secondary"></i>วันที่ และ เวลาสิ้นสุด (ถ้ามี)</label>
                        <input type="datetime-local" class="form-control shadow-sm" name="end_time">
                        <small class="text-muted d-block mt-1">ปล่อยว่างได้หากไม่ทราบเวลาสิ้นสุดแน่ชัด</small>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark"><i class="bi bi-stopwatch text-primary me-2"></i>จำนวนชั่วโมงที่เข้าร่วม <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm" id="totalHoursSelect" name="total_hours" required>
                                <option value="" selected disabled>-- เลือกจำนวนชั่วโมง --</option>
                                <option value="0.5">0.5 ชั่วโมง (30 นาที)</option>
                                <option value="1">1 ชั่วโมง</option>
                                <option value="1.5">1.5 ชั่วโมง (1 ชั่วโมง 30 นาที)</option>
                                <option value="2">2 ชั่วโมง</option>
                                <option value="2.5">2.5 ชั่วโมง</option>
                                <option value="3">3 ชั่วโมง</option>
                                <option value="3.5">3.5 ชั่วโมง</option>
                                <option value="4">4 ชั่วโมง</option>
                                <option value="4.5">4.5 ชั่วโมง</option>
                                <option value="5">5 ชั่วโมง</option>
                                <option value="5.5">5.5 ชั่วโมง</option>
                                <option value="6">6 ชั่วโมง</option>
                                <option value="custom" class="fw-bold text-primary">-- ระบุเอง (มากกว่า 6 ชั่วโมง) --</option>
                            </select>

                            <input type="number" 
                                   class="form-control mt-2 d-none shadow-sm" 
                                   id="customHoursInput" 
                                   name="custom_hours" 
                                   placeholder="ระบุจำนวนชั่วโมง (ทศนิยมได้ เช่น 6.5, 7, 8.5)"
                                   step="0.5"
                                   min="6.5">
                            <small class="text-muted d-none mt-1" id="customHoursHelp">ระบุตัวเลข (เช่น 6.5, 7)</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-2">
                    <button type="submit" class="btn btn-primary btn-lg shadow rounded-pill text-uppercase fw-bold">
                        <i class="bi bi-floppy-fill me-2"></i> บันทึกข้อมูลลงระบบ
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

    // 🌟 ระบบซ่อน/แสดงช่องกรอกชั่วโมงเมื่อเลือก "ระบุเอง"
    document.getElementById('totalHoursSelect').addEventListener('change', function() {
        const customInput = document.getElementById('customHoursInput');
        const customHelp = document.getElementById('customHoursHelp');
        
        if (this.value === 'custom') {
            customInput.classList.remove('d-none'); // แสดงช่องกรอก
            customHelp.classList.remove('d-none');
            customInput.setAttribute('required', 'required'); // บังคับให้ต้องกรอก
        } else {
            customInput.classList.add('d-none'); // ซ่อนช่อง
            customHelp.classList.add('d-none');
            customInput.removeAttribute('required');
            customInput.value = ''; // ล้างค่าทิ้ง
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>