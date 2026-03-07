<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\GlobalSetting; // เรียกใช้ Helper ที่เราสร้าง

class MeetingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'start_time', 'end_time', 'total_hours', 
        'topic', 'meeting_type', 'organizer', 'location', 'month_year'
    ];

    /**
     * สร้าง Scope สำหรับกรองข้อมูลตามช่วงเวลาที่ Admin ตั้งค่าไว้
     */
    public function scopeInActivePeriod($query)
{
    $filter = \App\Helpers\GlobalSetting::getDateFilter();
    // กรองข้อมูลที่ start_time อยู่ในช่วง start และ end ที่ตั้งค่าไว้
    return $query->whereBetween('start_time', [$filter['start'], $filter['end']]);
}

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}