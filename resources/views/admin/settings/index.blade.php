@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>⚙️ ตั้งค่าระบบ (System Settings)</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <strong>สำเร็จ!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8 col-lg-6"> <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold py-3">
                    <i class="bi bi-sliders"></i> กำหนดเงื่อนไขการแสดงผล
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-bold">📅 เริ่มต้นรอบการคำนวณ</label>
                                <input type="month" name="filter_start_month" class="form-control form-control-lg" value="{{ $start_month }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">📅 สิ้นสุดรอบการคำนวณ</label>
                                <input type="month" name="filter_end_month" class="form-control form-control-lg" value="{{ $end_month }}" required>
                            </div>
                            <div class="col-12 mt-3">
                                <div class="alert alert-light border border-danger border-start-5 text-danger py-2 mb-0" style="border-left-width: 4px !important;">
                                    <small><i class="bi bi-info-circle-fill"></i> * ข้อมูลนอกเหนือจากช่วงเดือนที่กำหนดจะถูกซ่อนจากการคำนวณและกราฟ</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-4 mt-4">
                            <label class="form-label fw-bold">🎯 จำนวนชั่วโมงที่ต้องอบรม (KPI)</label>
                            <div class="input-group input-group-lg">
                                <input type="number" name="kpi_hours" class="form-control" value="{{ $kpi_hours }}" required min="1" step="0.5">
                                <span class="input-group-text bg-light text-muted">ชั่วโมง</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                            <i class="bi bi-save me-1"></i> บันทึกการตั้งค่าระบบ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection