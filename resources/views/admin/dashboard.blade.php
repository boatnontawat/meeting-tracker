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
    
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card bg-primary text-white h-100 shadow-sm border-0 position-relative">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="card-title-custom mb-1 text-uppercase fw-semibold" style="letter-spacing: 0.5px; opacity: 0.9;">
                            <i class="bi bi-people-fill me-2"></i>บุคลากรทั้งหมด
                        </h6>
                        <h2 class="display-5 fw-bold mb-0">{{ number_format($totalUsers) }} <span class="fs-5 fw-normal">คน</span></h2>
                    </div>
                </div>
                <i class="bi bi-people stat-icon-bg"></i>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card bg-success text-white h-100 shadow-sm border-0 position-relative">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="card-title-custom mb-1 text-uppercase fw-semibold" style="letter-spacing: 0.5px; opacity: 0.9;">
                            <i class="bi bi-check-circle-fill me-2"></i>ผ่านเกณฑ์ ({{ $kpiHours }} ชม.)
                        </h6>
                        <h2 class="display-5 fw-bold mb-0">{{ number_format($passedCount) }} <span class="fs-5 fw-normal">คน</span></h2>
                    </div>
                </div>
                <i class="bi bi-check-circle stat-icon-bg"></i>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card bg-warning text-dark h-100 shadow-sm border-0 position-relative">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="card-title-custom mb-1 text-uppercase fw-semibold" style="letter-spacing: 0.5px; opacity: 0.9;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>ไม่ผ่าน (ทำได้ > 50%)
                        </h6>
                        <h2 class="display-5 fw-bold mb-0">{{ number_format($failedButOver50Count) }} <span class="fs-5 fw-normal">คน</span></h2>
                    </div>
                </div>
                <i class="bi bi-exclamation-triangle stat-icon-bg text-dark" style="opacity: 0.1;"></i>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card bg-danger text-white h-100 shadow-sm border-0 position-relative">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="card-title-custom mb-1 text-uppercase fw-semibold" style="letter-spacing: 0.5px; opacity: 0.9;">
                            <i class="bi bi-x-circle-fill me-2"></i>ไม่ผ่าน (ทำได้ < 50%)
                        </h6>
                        <h2 class="display-5 fw-bold mb-0">{{ number_format($failedCount) }} <span class="fs-5 fw-normal">คน</span></h2>
                    </div>
                </div>
                <i class="bi bi-x-circle stat-icon-bg"></i>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>ชั่วโมงการประชุมรวมแบ่งตามแผนก</h5>
                </div>
                <div class="card-body p-4" style="height: 400px;">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart-fill text-success me-2"></i>สัดส่วนประเภทการประชุม</h5>
                </div>
                <div class="card-body p-4 d-flex justify-content-center align-items-center" style="height: 400px;">
                    <div style="width: 100%; max-width: 300px;">
                        <canvas id="doughnutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem; overflow: hidden;">
        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-building-fill text-primary me-2"></i> ผลการดำเนินงานรายแผนก (Department Performance)
            </h5>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fs-6 shadow-sm">
                <i class="bi bi-flag-fill text-success me-1"></i> เป้าหมายเฉลี่ย: {{ $kpiHours }} ชม./คน
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4 py-3 fw-semibold border-0">ชื่อแผนก</th>
                            <th class="text-center py-3 fw-semibold border-0">จำนวนบุคลากร</th>
                            <th class="text-center py-3 fw-semibold border-0">ชั่วโมงรวม</th>
                            <th class="pe-4 py-3 fw-semibold border-0" style="width: 35%;">ค่าเฉลี่ยเปรียบเทียบ KPI</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($departmentOverview as $dept)
                            @php
                                // คำนวณค่าเฉลี่ย และเปอร์เซ็นต์ความสำเร็จเทียบกับ KPI
                                $avgHours = $dept->total_users_in_dept > 0 ? ($dept->total_hours_dept / $dept->total_users_in_dept) : 0;
                                $percent = $kpiHours > 0 ? ($avgHours / $kpiHours) * 100 : 0;
                                $percentBar = $percent > 100 ? 100 : $percent; // ล็อคหลอดไม่ให้เกิน 100%
                                
                                // จัดการสีของแถบสถานะ (แดง < 50%, เหลือง 50-99%, เขียว >= 100%)
                                $pgColor = 'bg-danger';
                                $textColor = 'text-danger';
                                if($percent >= 100) {
                                    $pgColor = 'bg-success';
                                    $textColor = 'text-success';
                                } elseif($percent >= 50) {
                                    $pgColor = 'bg-warning';
                                    $textColor = 'text-warning';
                                }
                            @endphp
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 45px; height: 45px;">
                                            <i class="bi bi-diagram-3-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $dept->department }}</h6>
                                            <small class="text-muted">ข้อมูลอัปเดตล่าสุด</small>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-center py-3">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 fs-6 rounded-pill">
                                        <i class="bi bi-people-fill me-1"></i> {{ number_format($dept->total_users_in_dept) }} คน
                                    </span>
                                </td>

                                <td class="text-center py-3">
                                    <h5 class="mb-0 fw-bold text-dark">{{ number_format($dept->total_hours_dept, 1) }} <span class="text-muted fw-normal fs-6">ชม.</span></h5>
                                </td>

                                <td class="pe-4 py-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold {{ $textColor }} fs-6">{{ number_format($avgHours, 1) }} ชม.</span>
                                        <span class="text-muted small fw-bold">{{ number_format($percent, 0) }}%</span>
                                    </div>
                                    <div class="progress shadow-sm" style="height: 8px; border-radius: 10px; background-color: #f1f5f9;">
                                        <div class="progress-bar {{ $pgColor }} progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $percentBar }}%; border-radius: 10px;" aria-valuenow="{{ $percentBar }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    ยังไม่มีข้อมูลการประชุมในแผนกใดๆ
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- กราฟแท่ง (Bar Chart) ---
        const barDataRaw = {!! json_encode($departmentData ?? []) !!};
        const barLabels = barDataRaw.map(item => item.department);
        const barValues = barDataRaw.map(item => item.sum_hours);

        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: barLabels,
                datasets: [{
                    label: 'ชั่วโมงรวม',
                    data: barValues,
                    backgroundColor: 'rgba(13, 110, 253, 0.85)',
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#e9ecef' } }
                }
            }
        });

        // --- กราฟโดนัท (Doughnut Chart) ---
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
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                }
            }
        });
    });
</script>
@endsection