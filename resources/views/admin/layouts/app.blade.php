<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Meeting Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            background-color: #f8f9fa; 
            overflow-x: hidden; /* ป้องกันแถบเลื่อนแนวนอนตอนเมนูสไลด์ */
        }
        
        .wrapper { 
            display: flex; 
            width: 100%; 
            align-items: stretch; 
        }

        /* 🎨 สไตล์ Sidebar ใหม่ */
        .sidebar { 
            min-width: 250px; 
            max-width: 250px; 
            min-height: 100vh; 
            background-color: #212529; 
            color: white; 
            transition: all 0.3s ease-in-out; 
            z-index: 1000;
        }
        
        /* คลาสสำหรับซ่อน Sidebar */
        .sidebar.hidden {
            margin-left: -250px;
        }

        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 15px; display: block; border-radius: 5px; margin-bottom: 5px; transition: 0.2s;}
        .sidebar a:hover, .sidebar a.active { background-color: #0d6efd; color: white; }
        
        /* ปุ่ม Lock */
        .btn-lock { color: #adb5bd; background: none; border: none; font-size: 1.2rem; transition: 0.2s; padding: 0; }
        .btn-lock:hover, .btn-lock.locked { color: #fff; }

        .main-content { 
            flex-grow: 1; 
            padding: 20px; 
            transition: all 0.3s ease-in-out; 
            width: 100%;
        }

        /* สำหรับมือถือ */
        @media (max-width: 768px) {
            .sidebar { position: absolute; height: 100%; box-shadow: 4px 0 10px rgba(0,0,0,0.2); }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="sidebar p-3" id="sidebar">
            <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                <h4 class="text-white mb-0"><i class="bi bi-shield-lock"></i> Admin Panel</h4>
                <button class="btn-lock" id="lockBtn" title="ปักหมุดเมนู">
                    <i class="bi bi-pin-angle"></i>
                </button>
            </div>
            <hr class="border-secondary">
            <nav class="nav flex-column">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-2"></i> หน้าแรก (Dashboard)</a>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-people me-2"></i> จัดการ User</a>
                <a href="{{ route('admin.meetings.index') }}" class="{{ request()->routeIs('admin.meetings.*') ? 'active' : '' }}"><i class="bi bi-calendar-check me-2"></i> จัดการการประชุม</a>
                <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-bar-graph me-2"></i> รายงาน (Reports)</a>
                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="bi bi-gear me-2"></i> ตั้งค่าระบบ</a>
                <hr class="border-secondary">
                <a href="/" class="text-warning"><i class="bi bi-box-arrow-left me-2"></i> กลับหน้าฟอร์มปกติ</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
                <button id="toggleBtn" class="btn btn-primary"><i class="bi bi-list fs-5"></i></button>
                <div class="text-muted fw-bold"><i class="bi bi-person-circle me-1"></i> ผู้ดูแลระบบ</div>
            </div>

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggleBtn');
            const lockBtn = document.getElementById('lockBtn');
            const lockIcon = lockBtn.querySelector('i');
            
            // ตรวจสอบค่า Lock จาก LocalStorage
            let isLocked = localStorage.getItem('sidebarLocked') === 'true';

            // ฟังก์ชันอัปเดต UI 
            function updateLockUI() {
                if (isLocked) {
                    lockIcon.classList.replace('bi-pin-angle', 'bi-pin-fill');
                    lockBtn.classList.add('locked');
                    sidebar.classList.remove('hidden'); // ถ้าล็อคไว้ ห้ามซ่อน
                } else {
                    lockIcon.classList.replace('bi-pin-fill', 'bi-pin-angle');
                    lockBtn.classList.remove('locked');
                }
            }

            // ตั้งค่าสถานะตอนโหลดหน้าเว็บ
            if (window.innerWidth > 768) {
                if (!isLocked) {
                    sidebar.classList.add('hidden'); // ถ้าไม่ได้ล็อค ให้ซ่อนอัตโนมัติเป็นค่าเริ่มต้น
                }
            } else {
                sidebar.classList.add('hidden'); // มือถือให้ซ่อนเสมอตอนเริ่ม
            }
            updateLockUI();

            // Event กดปุ่ม Hamburger เพื่อแสดง/ซ่อน
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('hidden');
            });

            // Event กดปุ่ม Lock (ไอคอนเข็มหมุด)
            lockBtn.addEventListener('click', function () {
                isLocked = !isLocked; 
                localStorage.setItem('sidebarLocked', isLocked); // บันทึกค่าลงเบราว์เซอร์
                updateLockUI();
            });
        });
    </script>
</body>
</html>