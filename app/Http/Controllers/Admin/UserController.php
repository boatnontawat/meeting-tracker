<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // 🌟 ถ้าระบบเรียกข้อมูลผ่าน AJAX (สำหรับ DataTables)
        if ($request->ajax()) {
            $users = User::select('id', 'name', 'department', 'position', 'status')
                        ->orderBy('created_at', 'desc')
                        ->get();
            
            $data = [];
            foreach ($users as $user) {
                $statusBadge = $user->status == 'active' 
                    ? '<span class="badge bg-success px-2 py-1">ปฏิบัติงาน</span>' 
                    : '<span class="badge bg-secondary px-2 py-1">พ้นสภาพ/ลาออก</span>';

                // สร้างปุ่ม Action
                $editUrl = route('admin.users.edit', $user->id);
                $deleteUrl = route('admin.users.destroy', $user->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                $actionBtns = '
                    <div class="d-flex justify-content-center gap-1">
                        <a href="'.$editUrl.'" class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-square"></i> แก้ไข</a>
                        <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?\');">
                            '.$csrf.$method.'
                            <button type="submit" class="btn btn-sm btn-danger shadow-sm"><i class="bi bi-trash"></i> ลบ</button>
                        </form>
                    </div>';

                $data[] = [
                    "DT_RowClass" => "align-middle",
                    "id" => $user->id,
                    "name" => '<div class="text-start fw-bold">' . $user->name . '</div>',
                    "department" => $user->department,
                    "position" => $user->position,
                    "status" => $statusBadge,
                    "action" => $actionBtns
                ];
            }
            return response()->json(['data' => $data]);
        }

        return view('admin.users.index');
    }

    public function create() { return view('admin.users.form'); }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        User::create([
            'name' => $request->name,
            'department' => $request->department,
            'position' => $request->position,
            'status' => $request->status,
            'email' => 'user_' . time() . '@hospital.com',
            'password' => Hash::make('12345678')
        ]);

        return redirect()->route('admin.users.index')->with('success', 'เพิ่มผู้ใช้งานเรียบร้อยแล้ว!');
    }

    public function edit(User $user) { return view('admin.users.form', compact('user')); }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $user->update([
            'name' => $request->name,
            'department' => $request->department,
            'position' => $request->position,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'อัปเดตข้อมูลผู้ใช้งานเรียบร้อยแล้ว!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว!');
    }
}
