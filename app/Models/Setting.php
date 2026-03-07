<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    
    // อนุญาตให้บันทึกข้อมูล 2 ฟิลด์นี้ได้
    protected $fillable = ['key', 'value'];
}