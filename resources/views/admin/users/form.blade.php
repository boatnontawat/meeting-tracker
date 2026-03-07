@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>{{ isset($user) ? '✏️ แก้ไขข้อมูลผู้ใช้งาน' : '➕ เพิ่มผู้ใช้งานใหม่' }}</h2>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">ย้อนกลับ</a>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 600px;">
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

            <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}" method="POST">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label fw-bold">ชื่อ-นามสกุล</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">แผนก</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $user->department ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ตำแหน่ง</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position', $user->position ?? '') }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">สถานะ</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', $user->status ?? '') == 'active' ? 'selected' : '' }}>ปฏิบัติงาน</option>
                        <option value="inactive" {{ old('status', $user->status ?? '') == 'inactive' ? 'selected' : '' }}>พ้นสภาพ/ลาออก</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100">💾 บันทึกข้อมูล</button>
            </form>
        </div>
    </div>
</div>
@endsection