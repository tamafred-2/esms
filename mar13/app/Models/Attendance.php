<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'morning_status',
        'afternoon_status',
        'morning_late_minutes',
        'afternoon_late_minutes'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'morning_late_minutes' => 'integer',
        'afternoon_late_minutes' => 'integer',
        'morning_time_in' => 'datetime',
        'morning_time_out' => 'datetime',
        'afternoon_time_in' => 'datetime',
        'afternoon_time_out' => 'datetime'
    ];

    // Remove the duplicate user relationship since student already exists
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id')
            ->select(['id', 'firstname', 'lastname', 'middlename']); // Optimize by selecting only needed fields
    }

    public function batch()
    {
        return $this->belongsTo(CourseBatch::class, 'batch_id');
    }

    // Helper methods to make code more readable
    public function getFullStudentNameAttribute()
    {
        if (!$this->student) {
            return 'Unknown Student';
        }

        return $this->student->lastname . ', ' . 
               $this->student->firstname . ' ' . 
               ($this->student->middlename ? substr($this->student->middlename, 0, 1) . '.' : '');
    }

    public function getMorningStatusAttribute($value)
    {
        return $value ?? 'absent';
    }

    public function getAfternoonStatusAttribute($value)
    {
        return $value ?? 'absent';
    }
}
