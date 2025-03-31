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
            $table->foreignId('batch_id')->constrained('course_batches')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('morning_time_in')->nullable();
            $table->time('morning_time_out')->nullable();
            $table->time('afternoon_time_in')->nullable();
            $table->time('afternoon_time_out')->nullable();
            $table->enum('morning_status', ['present', 'late', 'absent', 'excused'])->default('absent');
            $table->enum('afternoon_status', ['present', 'late', 'absent', 'excused'])->default('absent');
            $table->integer('morning_late_minutes')->default(0);
            $table->integer('afternoon_late_minutes')->default(0);
            $table->timestamps();
        
            $table->unique(['batch_id', 'student_id', 'attendance_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};
