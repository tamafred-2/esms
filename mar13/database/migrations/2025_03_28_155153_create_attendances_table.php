<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('morning_time_in')->nullable();
            $table->time('morning_time_out')->nullable();
            $table->integer('morning_minutes_late')->default(0);
            $table->time('afternoon_time_in')->nullable();
            $table->time('afternoon_time_out')->nullable();
            $table->integer('afternoon_minutes_late')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Prevent duplicate attendance records
            $table->unique(['student_id', 'batch_id', 'attendance_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};
