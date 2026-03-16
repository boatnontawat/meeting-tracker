@extends('admin.layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .stat-card {
        border-radius: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .stat-icon-bg {
        position: absolute;
        right: -10%;
        bottom: -15%;
        font-size: 8rem;
        opacity: 0.15;
        transform: rotate(-15deg);
        line-height: 1;
    }
    .card-title-custom {
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        opacity: 0.9;
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="w-100">
            <h2 class="fw-bold text-dark mb-2">📊 ภาพรวมระบบ (Dashboard)</h2>
            <p class="text-muted mb-0 small">
                <i class="bi bi-funnel-fill text-primary"></i> ข้อมูลที่แสดงผลอยู่ในช่วง: 
                <span class="badge bg-primary rounded-pill px-3 fs-6 shadow-sm d-inline-block mt-1 mt-sm-0">
                    {{ \App\Models\Setting::where('key', 'filter_start_month')->value('value') ?? 'N/A' }}
                </span>
                <i class="bi bi-arrow-right text-muted mx-1 d-none d-sm-inline"></i>
                <span class="badge bg-primary rounded-pill px-3 fs-6 shadow-sm d-inline-block mt-1 mt-sm-0">
                    {{ \App\Models\Setting::where('key', 'filter_end_month')->value('value') ?? 'N/A' }}
                </span>
            </p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card stat-card bg-primary text-white border-0 shadow-sm h-100 position-relative">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center position-relative z-1">
                        <div>
                            <p class="card-title-custom mb-2">ผู้ใช้งานทั้งหมด</p>
                            <h2 class="mb-0 fw-bold display-5">{{ number_format($totalUsers) }} <span class="fs-5 fw-normal">คน</span></h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-people-fill fs-2"></i>
                        </div>
                    </div>
                </div>
                <i class="bi bi-people-fill stat-icon-bg"></i>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card stat-card bg-success text-white border-0 shadow-sm h-100 position-relative">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center position-relative z-1">
                        <div>
                            <p class="card-title-custom mb-2">บันทึกการประชุม</p>
                            <h2 class="mb-0 fw-bold display-5">{{ number_format($totalMeetings) }} <span class="fs-5 fw-normal">รายการ</span></h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-journal-check fs-2"></i>
                        </div>
                    </div>
                </div>
                <i class="bi bi-journal-text stat-icon-bg"></i>
            </div>
        </div>

        <div class="col-12 col-md-12 col-xl-4">
            <div class="card stat-card bg-warning text-dark border-0 shadow-sm h-100 position-relative">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center position-relative z-1">
                        <div>
                            <p class="card-title-custom mb-2 text-dark">ชั่วโมงรวมทั้งหมด</p>
                            <h2 class="mb-0 fw-bold display-5">{{ number_format($totalHours, 1) }} <span class="fs-5 fw-normal">ชม.</span></h2>
                        </div>
                        <div class="bg-dark bg-opacity-10 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-clock-history fs-2"></i>
                        </div>
                    </div>
                </div>
                <i class="bi bi-stopwatch-fill stat-icon-bg opacity-10"></i>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-building text-primary me-2"></i> ชั่วโมงรวม แยกตามหน่วยงาน</h5>
                </div>
                <div class="card-body p-4">
                    <div style="position: relative; height: 400px; width: 100%;"> 
                        <canvas id="deptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart-fill text-success me-2"></i> สัดส่วนประเภทการประชุม</h5>
                </div>
                <div class="card-body p-4 d-flex justify-content-center align-items-center">
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="doughnutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-person-badge text-info me-2"></i> ชั่วโมงรวม แยกตามวิชาชีพ (ตำแหน่ง)</h5>
                </div>
                <div class="card-body p-4">
                    <div style="position: relative; height: 400px; width: 100%;"> 
                        <canvas id="posChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        
        // 🌟 1. กราฟชั่วโมงแยกตามหน่วยงาน (Horizontal Bar Chart)
        const deptDataRaw = {!! json_encode($departmentData ?? []) !!};
        const deptLabels = deptDataRaw.map(item => item.department);
        const deptValues = deptDataRaw.map(item => item.sum_hours);

        const ctxDept = document.getElementById('deptChart').getContext('2d');
        new Chart(ctxDept, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: ' ชั่วโมงรวม (ชม.)',
                    data: deptValues,
                    backgroundColor: 'rgba(13, 110, 253, 0.7)', // สีน้ำเงิน Primary
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y', // กำหนดเป็นแนวนอน
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#212529', padding: 12,
                        titleFont: { size: 14 }, bodyFont: { size: 14, weight: 'bold' }
                    }
                },
                scales: {
                    x: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#e9ecef' } },
                    y: { grid: { display: false } }
                }
            }
        });

        // 🌟 2. กราฟชั่วโมงแยกตามวิชาชีพ/ตำแหน่ง (Horizontal Bar Chart)
        const posDataRaw = {!! json_encode($positionData ?? []) !!};
        const posLabels = posDataRaw.map(item => item.position);
        const posValues = posDataRaw.map(item => item.sum_hours);

        const ctxPos = document.getElementById('posChart').getContext('2d');
        new Chart(ctxPos, {
            type: 'bar',
            data: {
                labels: posLabels,
                datasets: [{
                    label: ' ชั่วโมงรวม (ชม.)',
                    data: posValues,
                    backgroundColor: 'rgba(23, 162, 184, 0.7)', // สีฟ้า Info
                    borderColor: '#17a2b8',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y', // กำหนดเป็นแนวนอน
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#212529', padding: 12,
                        titleFont: { size: 14 }, bodyFont: { size: 14, weight: 'bold' }
                    }
                },
                scales: {
                    x: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#e9ecef' } },
                    y: { grid: { display: false } }
                }
            }
        });

        // 🌟 3. กราฟโดนัท สัดส่วนประเภทการประชุม (เหมือนเดิม)
        const typeDataRaw = {!! json_encode($typeData ?? []) !!};
        const typeLabels = typeDataRaw.map(item => item.meeting_type);
        const typeValues = typeDataRaw.map(item => item.count);

        const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeValues,
                    backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#dc3545', '#6f42c1', '#fd7e14'],
                    borderWidth: 2,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, usePointStyle: true, font: {size: 11} }
                    }
                }
            }
        });

    });
</script>
@endsection