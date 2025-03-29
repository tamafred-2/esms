<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'batch_id',
        'attendance_date',
        'morning_time_in',
        'morning_time_out',
        'morning_minutes_late',
        'afternoon_time_in',
        'afternoon_time_out',
        'afternoon_minutes_late',
        'status',
        'remarks'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'morning_time_in' => 'datetime',
        'morning_time_out' => 'datetime',
        'afternoon_time_in' => 'datetime',
        'afternoon_time_out' => 'datetime',
        'morning_minutes_late' => 'integer',
        'afternoon_minutes_late' => 'integer'
    ];

    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_EXCUSED = 'excused';

    public static $statuses = [
        self::STATUS_PRESENT => 'Present',
        self::STATUS_ABSENT => 'Absent',
        self::STATUS_LATE => 'Late',
        self::STATUS_EXCUSED => 'Excused'
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
