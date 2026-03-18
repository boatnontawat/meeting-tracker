<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุปการอบรม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark shadow-sm py-3">
        <div class="container-fluid px-4 d-flex justify-content-between">
            <span class="navbar-brand mb-0 h4 fw-bold">
                <i class="bi bi-bar-chart-fill text-primary me-2"></i>ระบบรายงานผลการอบรม
            </span>
            <span class="text-white-50 small"><i class="bi bi-person-check-fill me-1"></i> สำหรับหัวหน้าหน่วยงาน</span>
        </div>
    </nav>

    <main class="py-2">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>