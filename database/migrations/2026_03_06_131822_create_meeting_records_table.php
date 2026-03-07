<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_records', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
            $table->dateTime('start_time'); 
            $table->dateTime('end_time');   
            $table->decimal('total_hours', 5, 2)->nullable(); 
            $table->string('topic');        
            $table->string('meeting_type'); 
            $table->string('organizer');    
            $table->string('location');     
            $table->string('month_year');   
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_records');
    }
};