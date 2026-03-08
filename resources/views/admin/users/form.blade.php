@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3">
        <h3 class="fw-bold mb-0 text-dark">
            {{ isset($user) ? '✏️ แก้ไขข้อมูลผู้ใช้งาน' : '➕ เพิ่มผู้ใช้งานใหม่' }}
        </h3>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary shadow-sm w-100 w-sm-auto">
            <i class="bi bi-arrow-left-circle me-1"></i> ย้อนกลับ
        </a>
    </div>

    <div class="card shadow-sm border-0 mx-auto" style="max-width: 600px; border-radius: 1rem;">
        <div class="card-body p-3 p-md-4 p-lg-5">
            
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm rounded">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}" method="POST">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required placeholder="ระบุชื่อ-นามสกุล">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">แผนก <span class="text-danger">*</span></label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $user->department ?? '') }}" required placeholder="ระบุแผนก">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">ตำแหน่ง <span class="text-danger">*</span></label>
                    <input type="text" name="position" class="form-control" value="{{ old('position', $user->position ?? '') }}" required placeholder="ระบุตำแหน่ง">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-muted small">สถานะการทำงาน <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', $user->status ?? '') == 'active' ? 'selected' : '' }}>✅ ปฏิบัติงาน</option>
                        <option value="inactive" {{ old('status', $user->status ?? '') == 'inactive' ? 'selected' : '' }}>❌ พ้นสภาพ/ลาออก</option>
                    </select>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                        <i class="bi bi-floppy-fill me-2"></i> บันทึกข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection