<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'batch_id',
        'student_id',
        'attendance_date',
        'morning_time_in',
        'morning_time_out',
        'afternoon_time_in',
        'afternoon_time_out',
        'status'
    ];


    protected $casts = [
        'attendance_date' => 'date'
    ];

    /**
     * Get the student that owns the attendance record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id')
                    ->where('usertype', 'student');
    }

    /**
     * Get the batch that owns the attendance record.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(BatchEnrollment::class, 'batch_id');
    }

    /**
     * Scope a query to only include attendances for students.
     */
    public function scopeStudents($query)
    {
        return $query->whereHas('student', function($query) {
            $query->where('usertype', 'student');
        });
    }

    /**
     * Check if the student is late for morning session.
     */
    public function isMorningLate(): bool
    {
        return $this->morning_minutes_late > 0;
    }

    /**
     * Check if the student is late for afternoon session.
     */
    public function isAfternoonLate(): bool
    {
        return $this->afternoon_minutes_late > 0;
    }

    /**
     * Get total minutes late for the day.
     */
    public function getTotalMinutesLate(): int
    {
        return $this->morning_minutes_late + $this->afternoon_minutes_late;
    }
}
