<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('student_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('References users table, student only');
            
            $table->foreignId('batch_id')
                ->constrained('course_batches')
                ->onDelete('cascade')
                ->comment('References course_batches table');
            
            // Date and Time
            $table->date('attendance_date')->comment('Date of attendance');
            
            // Morning attendance
            $table->time('morning_time_in')->nullable()->comment('Morning time in');
            $table->time('morning_time_out')->nullable()->comment('Morning time out');
            $table->unsignedSmallInteger('morning_minutes_late')
                ->default(0)
                ->comment('Minutes late for morning session');
            
            // Afternoon attendance
            $table->time('afternoon_time_in')->nullable()->comment('Afternoon time in');
            $table->time('afternoon_time_out')->nullable()->comment('Afternoon time out');
            $table->unsignedSmallInteger('afternoon_minutes_late')
                ->default(0)
                ->comment('Minutes late for afternoon session');
            
            // Status and Remarks
            $table->enum('status', ['present', 'absent', 'late', 'excused'])
                ->default('absent')
                ->comment('Attendance status');
            
            $table->text('remarks')->nullable()->comment('Additional notes or remarks');
            
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('attendance_date', 'idx_attendance_date');
            $table->index(['batch_id', 'attendance_date'], 'idx_batch_date');
            $table->index('status', 'idx_status');
            
            // Unique constraint to prevent duplicate attendance records
            $table->unique(
                ['student_id', 'batch_id', 'attendance_date'], 
                'unique_attendance'
            );
        });

        // Add table comment
        DB::statement('ALTER TABLE `attendances` COMMENT = "Stores student attendance records for course batches"');
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
