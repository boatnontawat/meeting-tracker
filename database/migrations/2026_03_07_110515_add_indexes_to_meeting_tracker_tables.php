<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. เพิ่ม Index ให้ตาราง users
        Schema::table('users', function (Blueprint $table) {
            $table->index('status');
            $table->index('department');
            $table->index('position');
        });

        // 2. เพิ่ม Index ให้ตาราง meeting_records
        Schema::table('meeting_records', function (Blueprint $table) {
            // ถ้า user_id เป็น Foreign Key อยู่แล้วอาจจะมี Index อยู่แล้ว 
            // แต่ถ้ายังไม่มีให้เพิ่มบรรทัดนี้ด้วยครับ
            $table->index('user_id'); 
            
            $table->index('month_year');
            $table->index('meeting_type');
            $table->index('start_time'); // เผื่อไว้ใช้สำหรับ Scope วันที่
        });

        // 3. เพิ่ม Index ให้ตาราง settings
        Schema::table('settings', function (Blueprint $table) {
            // เช็คว่าถ้า 'key' ยังไม่ได้ตั้งเป็น Unique ก็ให้สร้าง Index ปกติ
            $table->index('key'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['department']);
            $table->dropIndex(['position']);
        });

        Schema::table('meeting_records', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['month_year']);
            $table->dropIndex(['meeting_type']);
            $table->dropIndex(['start_time']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex(['key']);
        });
    }
};