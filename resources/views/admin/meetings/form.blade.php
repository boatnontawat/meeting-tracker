@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3">
        <h3 class="fw-bold mb-0 text-primary">
            <i class="bi {{ isset($meeting) ? 'bi-pencil-square' : 'bi-plus-circle' }} me-2"></i>
            {{ isset($meeting) ? 'แก้ไขการประชุม' : 'เพิ่มการประชุมใหม่' }}
        </h3>
        <a href="{{ route('admin.meetings.index') }}" class="btn btn-secondary shadow-sm w-100 w-sm-auto">
            <i class="bi bi-arrow-left-circle me-1"></i> ย้อนกลับ
        </a>
    </div>

    <div class="card shadow-sm border-0 mx-auto" style="max-width: 900px; border-radius: 1rem;">
        <div class="card-body p-4 p-md-5">
            
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm rounded">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ isset($meeting) ? route('admin.meetings.update', $meeting->id) : route('admin.meetings.store') }}" method="POST">
                @csrf
                @if(isset($meeting))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-muted small">เลือกเจ้าหน้าที่ <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- เลือกเจ้าหน้าที่ --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (old('user_id', $meeting->user_id ?? '')) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->department }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-muted small">เรื่องประชุม/อบรม/หลักสูตร <span class="text-danger">*</span></label>
                        <input type="text" name="topic" class="form-control" value="{{ old('topic', $meeting->topic ?? '') }}" required>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mb-2">
                        <label class="form-label fw-bold text-muted small">วันที่เริ่ม <span class="text-danger">*</span></label>
                        <input type="date" name="start_time" class="form-control" 
                            value="{{ old('start_time', isset($meeting) ? \Carbon\Carbon::parse($meeting->start_time)->format('Y-m-d') : '') }}" required>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-4 mb-2">
                        <label class="form-label fw-bold text-muted small">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                        <input type="date" name="end_time" class="form-control" 
                            value="{{ old('end_time', isset($meeting) ? \Carbon\Carbon::parse($meeting->end_time)->format('Y-m-d') : '') }}" required>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mb-2">
                        <label class="form-label fw-bold text-muted small">รวมเวลา (ชั่วโมง) <span class="text-danger">*</span></label>
                        <input type="number" name="total_hours" class="form-control" step="0.1" min="0" 
                            value="{{ old('total_hours', $meeting->total_hours ?? '') }}" placeholder="เช่น 6 หรือ 1.5" required>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mb-2">
                        <label class="form-label fw-bold text-muted small">ประเภท <span class="text-danger">*</span></label>
                        <select class="form-select" name="meeting_type" required>
                            <option value="ในโรงพยาบาล" {{ (old('meeting_type', $meeting->meeting_type ?? '')) == 'ในโรงพยาบาล' ? 'selected' : '' }}>ในโรงพยาบาล</option>
                            <option value="นอกโรงพยาบาล" {{ (old('meeting_type', $meeting->meeting_type ?? '')) == 'นอกโรงพยาบาล' ? 'selected' : '' }}>นอกโรงพยาบาล</option>
                            <option value="Online" {{ (old('meeting_type', $meeting->meeting_type ?? '')) == 'Online' ? 'selected' : '' }}>Online</option>
                        </select>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-4 mb-2">
                        <label class="form-label fw-bold text-muted small">หน่วยงานที่จัด <span class="text-danger">*</span></label>
                        <input type="text" name="organizer" class="form-control" value="{{ old('organizer', $meeting->organizer ?? '') }}" required>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-4 mb-2">
                        <label class="form-label fw-bold text-muted small">สถานที่ <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $meeting->location ?? '') }}" required>
                    </div>

                    <div class="col-12 mb-4">
                        <label class="form-label fw-bold text-muted small">เดือน-ปี (Year-Month) <span class="text-danger">*</span></label>
                        <input type="month" name="month_year" class="form-control" value="{{ old('month_year', $meeting->month_year ?? '') }}" required>
                    </div>
                </div>

                <div class="d-grid mt-2">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                        <i class="bi bi-floppy-fill me-2"></i> บันทึกข้อมูลการประชุม
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection