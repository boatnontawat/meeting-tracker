<?php

namespace App\Helpers;

use App\Models\Setting;
use Carbon\Carbon;

class GlobalSetting {
    /**
     * ดึงช่วงวันที่เริ่มต้นและสิ้นสุดจากฐานข้อมูล
     */
    public static function getDateFilter() {
        // ดึงค่าจาก table settings ถ้าไม่มีให้ใช้ค่า Default
        $start = Setting::where('key', 'filter_start_month')->value('value') ?? '2025-10';
        $end = Setting::where('key', 'filter_end_month')->value('value') ?? '2026-10';

        return [
            // Carbon::parse จะเปลี่ยน "2025-10" เป็นวันที่ 1 ของเดือนนั้น
            'start' => Carbon::parse($start)->startOfMonth(),
            // endOfMonth() จะทำให้ครอบคลุมถึงวันสุดท้ายของเดือนที่เลือก (เช่น 31 ต.ค.)
            'end' => Carbon::parse($end)->endOfMonth(),
        ];
    }
}