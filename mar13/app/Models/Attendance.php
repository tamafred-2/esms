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
        'afternoon_late_minutes' => 'integer'
    ];

    public function batch()
    {
        return $this->belongsTo(CourseBatch::class, 'batch_id');
    }
    // Update the relationship name if needed
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
