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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            $table->foreignId('batch_id')
                ->constrained('course_batches')
                ->onDelete('cascade');
            
            $table->date('attendance_date');
            
            // Morning attendance
            $table->time('morning_time_in')->nullable();
            $table->time('morning_time_out')->nullable();
            $table->unsignedInteger('morning_minutes_late')->default(0);
            
            // Afternoon attendance
            $table->time('afternoon_time_in')->nullable();
            $table->time('afternoon_time_out')->nullable();
            $table->unsignedInteger('afternoon_minutes_late')->default(0);
            
            // Add this line
            $table->string('status')->nullable(); // Add the status column
            
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('attendance_date');
            $table->index(['batch_id', 'attendance_date']);
            
            $table->unique(['student_id', 'batch_id', 'attendance_date'], 'unique_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
