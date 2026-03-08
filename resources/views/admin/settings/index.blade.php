@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <h2 class="fw-bold mb-0 text-dark">
            <i class="bi bi-gear-fill text-primary me-2"></i> ตั้งค่าระบบ (System Settings)
        </h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <strong>สำเร็จ!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-md-10 col-lg-8 col-xl-6"> 
            <div class="card shadow-sm border-0" style="border-radius: 1rem; overflow: hidden;">
                <div class="card-header bg-dark text-white fw-bold py-3 fs-5">
                    <i class="bi bi-sliders me-1"></i> กำหนดเงื่อนไขการแสดงผล
                </div>
                <div class="card-body p-3 p-md-4 p-lg-5">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-muted small">📅 เริ่มต้นรอบการคำนวณ <span class="text-danger">*</span></label>
                                <input type="month" name="filter_start_month" class="form-control form-control-lg bg-light" value="{{ $start_month }}" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-muted small">📅 สิ้นสุดรอบการคำนวณ <span class="text-danger">*</span></label>
                                <input type="month" name="filter_end_month" class="form-control form-control-lg bg-light" value="{{ $end_month }}" required>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="alert alert-light border border-danger border-start-5 text-danger py-2 mb-0 shadow-sm" style="border-left-width: 4px !important;">
                                    <small><i class="bi bi-info-circle-fill me-1"></i> * ข้อมูลนอกเหนือจากช่วงเดือนที่กำหนดจะถูกซ่อนจากการคำนวณและกราฟ</small>
                                </div>
                            </div>
                        </div>

                        <hr class="text-muted my-4">

                        <div class="mb-5">
                            <label class="form-label fw-bold text-muted small">🎯 จำนวนชั่วโมงที่ต้องอบรม (KPI) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg shadow-sm">
                                <input type="number" name="kpi_hours" class="form-control bg-light" value="{{ $kpi_hours }}" required min="1" step="0.5" placeholder="ระบุจำนวนชั่วโมง">
                                <span class="input-group-text bg-primary text-white fw-bold border-primary">ชั่วโมง</span>
                            </div>
                        </div>

                        <div class="d-grid mt-2">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                                <i class="bi bi-save-fill me-2"></i> บันทึกการตั้งค่าระบบ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection