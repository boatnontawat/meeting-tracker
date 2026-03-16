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
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold text-muted small"><i class="bi bi-person-fill"></i> ผู้เข้าร่วมประชุม <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select select2" required>
                            <option value="" disabled selected>-- เลือกผู้เข้าร่วม --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (old('user_id', $meeting->user_id ?? '')) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->department ?? 'ไม่ระบุแผนก' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label class="form-label fw-bold text-muted small">วันที่เริ่ม <span class="text-danger">*</span></label>
                        <input type="date" name="start_time" class="form-control" value="{{ old('start_time', isset($meeting) ? \Carbon\Carbon::parse($meeting->start_time)->format('Y-m-d') : '') }}" required>
                    </div>
                    
                    <div class="col-12 col-md-4 mb-2">
                        <label class="form-label fw-bold text-muted small">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                        <input type="date" name="end_time" class="form-control" value="{{ old('end_time', isset($meeting) ? \Carbon\Carbon::parse($meeting->end_time)->format('Y-m-d') : '') }}" required>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <label class="form-label fw-bold text-muted small">รวมเวลา (ชั่วโมง) <span class="text-danger">*</span></label>
                        @php
                            $savedHours = old('total_hours', $meeting->total_hours ?? '');
                            $isCustom = !in_array((string)$savedHours, ['', '0.5', '1', '2', '3', '4', '5']);
                        @endphp
                        <select name="total_hours" id="totalHoursSelect" class="form-select" required>
                            <option value="" disabled {{ $savedHours == '' ? 'selected' : '' }}>เลือกชั่วโมง...</option>
                            <option value="0.5" {{ $savedHours == '0.5' ? 'selected' : '' }}>30 นาที</option>
                            <option value="1" {{ $savedHours == '1' ? 'selected' : '' }}>1 ชั่วโมง</option>
                            <option value="2" {{ $savedHours == '2' ? 'selected' : '' }}>2 ชั่วโมง</option>
                            <option value="3" {{ $savedHours == '3' ? 'selected' : '' }}>3 ชั่วโมง</option>
                            <option value="4" {{ $savedHours == '4' ? 'selected' : '' }}>4 ชั่วโมง</option>
                            <option value="5" {{ $savedHours == '5' ? 'selected' : '' }}>5 ชั่วโมง</option>
                            <option value="custom" {{ $isCustom && $savedHours != '' ? 'selected' : '' }}>มากกว่า 5 ชั่วโมง (ระบุเอง)</option>
                        </select>
                        <input type="number" name="custom_hours" id="customHoursInput" class="form-control mt-2 {{ $isCustom && $savedHours != '' ? '' : 'd-none' }}" step="0.1" min="0" placeholder="ระบุชั่วโมง (เช่น 6.5)" value="{{ $isCustom ? $savedHours : '' }}">
                    </div>

                    <div class="col-12 mb-2 mt-4">
                        <label class="form-label fw-bold text-muted small"><i class="bi bi-journal-text"></i> เรื่องประชุม/อบรม/หลักสูตร <span class="text-danger">*</span></label>
                        <input type="text" name="topic" list="topicList" class="form-control" value="{{ old('topic', $meeting->topic ?? '') }}" placeholder="เลือกจากรายการ หรือพิมพ์ใหม่..." required autocomplete="off">
                        <datalist id="topicList">
                            @foreach($topics as $t)
                                <option value="{{ $t->topic }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mb-2">
                        <label class="form-label fw-bold text-muted small">ประเภท <span class="text-danger">*</span></label>
                        <select name="meeting_type" class="form-select" required>
                            <option value="ในโรงพยาบาล" {{ (old('meeting_type', $meeting->meeting_type ?? '')) == 'ในโรงพยาบาล' ? 'selected' : '' }}>ในโรงพยาบาล</option>
                            <option value="นอกโรงพยาบาล" {{ (old('meeting_type', $meeting->meeting_type ?? '')) == 'นอกโรงพยาบาล' ? 'selected' : '' }}>นอกโรงพยาบาล</option>
                            <option value="Online" {{ (old('meeting_type', $meeting->meeting_type ?? '')) == 'Online' ? 'selected' : '' }}>Online</option>
                        </select>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-4 mb-2">
                        <label class="form-label fw-bold text-muted small">หน่วยงานที่จัด <span class="text-danger">*</span></label>
                        <input type="text" name="organizer" list="organizerList" class="form-control" value="{{ old('organizer', $meeting->organizer ?? '') }}" placeholder="ระบุหน่วยงาน..." required autocomplete="off">
                        <datalist id="organizerList">
                            @foreach($organizers as $org)
                                <option value="{{ $org->organizer }}">
                            @endforeach
                        </datalist>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-4 mb-2">
                        <label class="form-label fw-bold text-muted small">สถานที่ <span class="text-danger">*</span></label>
                        <input type="text" name="location" list="locationList" class="form-control" value="{{ old('location', $meeting->location ?? '') }}" placeholder="ระบุสถานที่..." required autocomplete="off">
                        <datalist id="locationList">
                            @foreach($locations as $loc)
                                <option value="{{ $loc->location }}">
                            @endforeach
                        </datalist>
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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: "-- เลือกผู้เข้าร่วม --"
        });
        
        // 🌟 ระบบซ่อน/แสดงช่องกรอกชั่วโมงเมื่อเลือก "ระบุเอง"
        $('#totalHoursSelect').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#customHoursInput').removeClass('d-none').prop('required', true);
            } else {
                $('#customHoursInput').addClass('d-none').prop('required', false).val('');
            }
        });
    });
</script>
@endsection