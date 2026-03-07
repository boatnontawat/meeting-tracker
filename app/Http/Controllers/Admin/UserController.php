<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // 1. หน้าแสดงรายการ User ทั้งหมด
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    // 2. หน้าฟอร์มเพิ่ม User ใหม่
    public function create()
    {
        return view('admin.users.form');
    }

    // 3. บันทึก User ใหม่ลงฐานข้อมูล
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
            'email' => 'user_' . time() . '@hospital.com', // สุ่มอีเมลให้
            'password' => Hash::make('12345678') // รหัสผ่านตั้งต้น
        ]);

        return redirect()->route('admin.users.index')->with('success', 'เพิ่มผู้ใช้งานเรียบร้อยแล้ว!');
    }

    // 4. หน้าฟอร์มแก้ไขข้อมูล User
    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    // 5. บันทึกการอัปเดตข้อมูล
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

    // 6. ลบ User
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว!');
    }
}