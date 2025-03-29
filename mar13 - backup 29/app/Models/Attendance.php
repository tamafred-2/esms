<?php

namespace App\Models;

use App\Models\BatchEnrollment;
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
        'remarks'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'morning_time_in' => 'datetime',
        'morning_time_out' => 'datetime',
        'afternoon_time_in' => 'datetime',
        'afternoon_time_out' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(BatchEnrollment::class);
    }
}
