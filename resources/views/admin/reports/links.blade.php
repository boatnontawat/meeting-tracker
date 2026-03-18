@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4 mb-5 mt-4">
    <h2 class="text-primary fw-bold mb-1">🔗 ลิงก์สำหรับแชร์รายงานให้หัวหน้าแผนก</h2>
    <p class="text-muted">คัดลอกลิงก์เหล่านี้ส่งให้หัวหน้าแผนกแต่ละแผนก พวกเขาจะดูได้เฉพาะข้อมูลแผนกตัวเองโดยไม่ต้องเข้าสู่ระบบ</p>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="20%">ชื่อแผนก</th>
                        <th>ลิงก์ (Signed URL)</th>
                        <th width="10%">คัดลอก</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($links as $dept => $link)
                    <tr>
                        <td class="fw-bold fs-5 text-primary">{{ $dept }}</td>
                        <td>
                            <input type="text" class="form-control bg-light text-muted" id="link-{{ $loop->index }}" value="{{ $link }}" readonly>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('link-{{ $loop->index }}')">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(inputId) {
        var copyText = document.getElementById(inputId);
        copyText.select();
        copyText.setSelectionRange(0, 99999); 
        navigator.clipboard.writeText(copyText.value);
        alert("คัดลอกลิงก์เรียบร้อยแล้ว!");
    }
</script>
@endsection