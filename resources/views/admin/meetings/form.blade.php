@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>{{ isset($meeting) ? '✏️ แก้ไขการประชุม' : '➕ เพิ่มการประชุมใหม่' }}</h2>
        <a href="{{ route('admin.meetings.index') }}" class="btn btn-sm btn-secondary">ย้อนกลับ</a>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 800px;">
        <div class="card-body p-4">
            
            @if ($errors->any())
                <div class="alert alert-danger">
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

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">เลือกเจ้าหน้าที่</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- เลือกเจ้าหน้าที่ --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (old('user_id', $meeting->user_id ?? '')) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->department }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">เรื่องประชุม/อบรม/หลักสูตร</label>
                        <input type="text" name="topic" class="form-control" value="{{ old('topic', $meeting->topic ?? '') }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">วันที่เริ่ม</label>
                        <input type="date" name="start_time" class="form-control" 
                            value="{{ old('start_time', isset($meeting) ? \Carbon\Carbon::parse($meeting->start_time)->format('Y-m-d') : '') }}" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">วันที่สิ้นสุด</label>
                        <input type="date" name="end_time" class="form-control" 
                            value="{{ old('end_time', isset($meeting) ? \Carbon\Carbon::parse($meeting->end_time)->format('Y-m-d') : '') }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">รวมเวลา (ชั่วโมง)</label>
                        <input type="number" name="total_hours" class="form-control" step="0.1" min="0" 
                            value="{{ old('total_hours', $meeting->total_hours ?? '') }}" placeholder="เช่น 6 หรือ 1.5" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">ประเภท</label>
                        <select class="form-select" name="meeting_type" required>
                            <option value="ในโรงพยาบาล" {{ (old('meeting_type', $meeting->meeting_type ?? '')) == 'ในโรงพยาบาล' ? 'selected' : '' }}>ในโรงพยาบาล</option>
                            <option value="นอกโรงพยาบาล" {{ (old('meeting_type', $meeting->meeting_type ?? '')) == 'นอกโรงพยาบาล' ? 'selected' : '' }}>นอกโรงพยาบาล</option>
                            <option value="Online" {{ (old('meeting_type', $meeting->meeting_type ?? '')) == 'Online' ? 'selected' : '' }}>Online</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">หน่วยงานที่จัด</label>
                        <input type="text" name="organizer" class="form-control" value="{{ old('organizer', $meeting->organizer ?? '') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">สถานที่</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $meeting->location ?? '') }}" required>
                    </div>

                    <div class="col-md-12 mb-4">
                        <label class="form-label fw-bold">เดือน-ปี (Year-Month)</label>
                        <input type="month" name="month_year" class="form-control" value="{{ old('month_year', $meeting->month_year ?? '') }}" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">💾 บันทึกข้อมูลการประชุม</button>
            </form>
        </div>
    </div>
</div>
@endsection