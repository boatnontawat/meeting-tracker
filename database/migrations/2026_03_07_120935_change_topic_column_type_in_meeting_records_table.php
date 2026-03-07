<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('meeting_records', function (Blueprint $table) {
        // เปลี่ยนจาก string เป็น text เพื่อให้เก็บข้อความยาวๆ ได้
        $table->text('topic')->change();
    });
}

public function down()
{
    Schema::table('meeting_records', function (Blueprint $table) {
        $table->string('topic', 255)->change();
    });
}
};
