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
            background-color: #f8f9fa; 
            overflow-x: hidden; 
        }
        
        .wrapper { 
            display: flex; 
            width: 100%; 
            min-height: 100vh;
        }

        /* 🎨 สไตล์ Sidebar */
        .sidebar { 
            width: 250px; 
            min-height: 100vh; 
            background-color: #212529; 
            color: white; 
            transition: all 0.3s ease-in-out; 
            z-index: 1040;
        }

        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 15px; display: block; border-radius: 5px; margin-bottom: 5px; transition: 0.2s;}
        .sidebar a:hover, .sidebar a.active { background-color: #0d6efd; color: white; }
        
        /* ปุ่ม Lock */
        .btn-lock { color: #adb5bd; background: none; border: none; font-size: 1.2rem; transition: 0.2s; padding: 0; }
        .btn-lock:hover, .btn-lock.locked { color: #fff; }

        .main-content { 
            flex-grow: 1; 
            padding: 20px; 
            width: calc(100% - 250px);
            transition: all 0.3s ease-in-out; 
        }

        /* ฉากหลังตอนเปิดเมนูบนมือถือ */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1030;
            backdrop-filter: blur(2px);
        }

        /* สำหรับหน้าจอคอมพิวเตอร์และแท็บเล็ตแนวนอน */
        @media (min-width: 768px) {
            .sidebar.hidden { margin-left: -250px; }
            .main-content { width: 100%; }
        }

        /* สำหรับมือถือและแท็บเล็ตแนวตั้ง */
        @media (max-width: 767.98px) {
            .sidebar { 
                position: fixed; 
                height: 100vh; 
                left: 0;
                top: 0;
                transform: translateX(0); /* สไลด์เข้า */
                box-shadow: 4px 0 10px rgba(0,0,0,0.2); 
            }
            .sidebar.hidden {
                transform: translateX(-100%); /* สไลด์ออก */
                margin-left: 0; 
            }
            .main-content { 
                width: 100%; 
                margin-left: 0 !important;
            }
            .sidebar-overlay.active {
                display: block; /* แสดงฉากหลัง */
            }
            .header-toggle-container {
                padding: 15px !important;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="sidebar p-3" id="sidebar">
            <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                <h4 class="text-white mb-0 fs-5"><i class="bi bi-shield-lock me-1"></i> Admin Panel</h4>
                <button class="btn-lock d-none d-md-block" id="lockBtn" title="ปักหมุดเมนู">
                    <i class="bi bi-pin-angle"></i>
                </button>
            </div>
            <hr class="border-secondary">
            <nav class="nav flex-column">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-2"></i> หน้าแรก (Dashboard)</a>
    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-people me-2"></i> จัดการ User</a>
    <a href="{{ route('admin.meetings.index') }}" class="{{ request()->routeIs('admin.meetings.*') ? 'active' : '' }}"><i class="bi bi-calendar-check me-2"></i> จัดการการประชุม</a>
    <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.index', 'admin.reports.master', 'admin.reports.pivot', 'admin.reports.department') ? 'active' : '' }}"><i class="bi bi-file-earmark-bar-graph me-2"></i> รายงาน (Reports)</a>
    
    <a href="{{ route('admin.reports.links') }}" class="{{ request()->routeIs('admin.reports.links') ? 'active' : '' }}"><i class="bi bi-share me-2"></i> แจกลิงก์รายงาน</a>
    
    <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="bi bi-gear me-2"></i> ตั้งค่าระบบ</a>
    <hr class="border-secondary">
    <a href="{{ route('form.summary') }}" class="text-warning"><i class="bi bi-box-arrow-left me-2"></i> กลับหน้ารายละเอียดการประชุม</a>
</nav>
        </div>

        <div class="main-content">
            <div class="header-toggle-container d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
                <button id="toggleBtn" class="btn btn-primary shadow-sm"><i class="bi bi-list fs-5"></i></button>
                <div class="text-muted fw-bold d-flex align-items-center">
                    <i class="bi bi-person-circle fs-4 me-2 text-primary"></i> 
                    <span class="d-none d-sm-inline">ผู้ดูแลระบบ</span>
                </div>
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
            const overlay = document.getElementById('sidebarOverlay');
            
            let isLocked = localStorage.getItem('sidebarLocked') === 'true';

            function isMobile() {
                return window.innerWidth < 768;
            }

            function updateUI() {
                if (isMobile()) {
                    // มือถือ: ซ่อนเมนูเสมอตอนเริ่ม และไม่ใช้ระบบล็อค
                    sidebar.classList.add('hidden');
                    overlay.classList.remove('active');
                } else {
                    // คอมพิวเตอร์: จัดการตามสถานะล็อค
                    if (isLocked) {
                        lockIcon.classList.replace('bi-pin-angle', 'bi-pin-fill');
                        lockBtn.classList.add('locked');
                        sidebar.classList.remove('hidden');
                    } else {
                        lockIcon.classList.replace('bi-pin-fill', 'bi-pin-angle');
                        lockBtn.classList.remove('locked');
                        sidebar.classList.add('hidden');
                    }
                }
            }

            // ตั้งค่าครั้งแรกตอนโหลดหน้า
            updateUI();

            // กดปุ่ม ☰ (Hamburger Menu)
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('hidden');
                
                // จัดการ Overlay เฉพาะบนมือถือ
                if (isMobile()) {
                    if (sidebar.classList.contains('hidden')) {
                        overlay.classList.remove('active');
                    } else {
                        overlay.classList.add('active');
                    }
                }
            });

            // กดที่ฉากหลัง (Overlay) เพื่อปิดเมนูบนมือถือ
            overlay.addEventListener('click', function () {
                sidebar.classList.add('hidden');
                overlay.classList.remove('active');
            });

            // กดปุ่ม Lock (เข็มหมุด) - มีผลแค่บนคอมพิวเตอร์
            lockBtn.addEventListener('click', function () {
                if (!isMobile()) {
                    isLocked = !isLocked; 
                    localStorage.setItem('sidebarLocked', isLocked);
                    updateUI();
                }
            });

            // ปรับ UI อัตโนมัติเวลาหมุนหน้าจอหรือย่อ/ขยายหน้าต่างเบราว์เซอร์
            window.addEventListener('resize', function() {
                if (isMobile()) {
                    sidebar.classList.add('hidden');
                    overlay.classList.remove('active');
                } else {
                    updateUI();
                }
            });
        });
    </script>
</body>
</html>
