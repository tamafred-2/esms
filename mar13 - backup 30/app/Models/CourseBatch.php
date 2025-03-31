<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CourseBatch extends Model
{
    use HasFactory;

    protected $table = 'course_batches';

    protected $fillable = [
        'course_id',
        'batch_name',
        'start_date',
        'end_date',
        'max_students',
        'morning_time_in',
        'morning_time_out',
        'afternoon_time_in',
        'afternoon_time_out',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'morning_time_in' => 'datetime',
        'morning_time_out' => 'datetime',
        'afternoon_time_in' => 'datetime',
        'afternoon_time_out' => 'datetime'
    ];

    protected $appends = [
        'enrollment_count',
        'is_full',
        'available_slots',
        'morning_schedule',
        'afternoon_schedule',
        'full_schedule'
    ];

    // Relationships
    public function enrollments()
    {
        return $this->hasMany(BatchEnrollment::class, 'batch_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'batch_id');
    }

    // Enhanced schedule attributes
    public function getMorningScheduleAttribute()
    {
        try {
            if (!$this->morning_time_in || !$this->morning_time_out) {
                return 'No morning schedule set';
            }
            return Carbon::parse($this->morning_time_in)->format('h:i A') . ' - ' . 
                   Carbon::parse($this->morning_time_out)->format('h:i A');
        } catch (\Exception $e) {
            Log::error('Error getting morning schedule: ' . $e->getMessage());
            return 'Schedule error';
        }
    }

    public function getAfternoonScheduleAttribute()
    {
        try {
            if (!$this->afternoon_time_in || !$this->afternoon_time_out) {
                return 'No afternoon schedule set';
            }
            return Carbon::parse($this->afternoon_time_in)->format('h:i A') . ' - ' . 
                   Carbon::parse($this->afternoon_time_out)->format('h:i A');
        } catch (\Exception $e) {
            Log::error('Error getting afternoon schedule: ' . $e->getMessage());
            return 'Schedule error';
        }
    }

    // Helper method to get current enrollment count
    public function getEnrollmentCountAttribute()
    {
        try {
            return $this->enrollments()
                ->where('status', '!=', 'cancelled')
                ->count();
        } catch (\Exception $e) {
            Log::error('Error getting enrollment count: ' . $e->getMessage());
            return 0;
        }
    }

    // Helper method to check if batch is full
    public function getIsFullAttribute()
    {
        try {
            return $this->enrollment_count >= $this->max_students;
        } catch (\Exception $e) {
            Log::error('Error checking if batch is full: ' . $e->getMessage());
            return false;
        }
    }

    // Schedule validation methods
    public function isValidMorningTime($time)
    {
        try {
            if (!$this->morning_time_in || !$this->morning_time_out) {
                return false;
            }
            $checkTime = Carbon::parse($time);
            $startTime = Carbon::parse($this->morning_time_in);
            $endTime = Carbon::parse($this->morning_time_out);
            
            return $checkTime->between($startTime, $endTime);
        } catch (\Exception $e) {
            Log::error('Error validating morning time: ' . $e->getMessage());
            return false;
        }
    }

    public function isValidAfternoonTime($time)
    {
        try {
            if (!$this->afternoon_time_in || !$this->afternoon_time_out) {
                return false;
            }
            $checkTime = Carbon::parse($time);
            $startTime = Carbon::parse($this->afternoon_time_in);
            $endTime = Carbon::parse($this->afternoon_time_out);
            
            return $checkTime->between($startTime, $endTime);
        } catch (\Exception $e) {
            Log::error('Error validating afternoon time: ' . $e->getMessage());
            return false;
        }
    }

    // Helper method to get full schedule
    public function getFullScheduleAttribute()
    {
        try {
            $schedule = [];
            if ($this->morning_time_in && $this->morning_time_out) {
                $schedule['morning'] = $this->morning_schedule;
            }
            if ($this->afternoon_time_in && $this->afternoon_time_out) {
                $schedule['afternoon'] = $this->afternoon_schedule;
            }
            return $schedule;
        } catch (\Exception $e) {
            Log::error('Error getting full schedule: ' . $e->getMessage());
            return [];
        }
    }

    // Scope for active batches
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper method to get available slots
    public function getAvailableSlotsAttribute()
    {
        try {
            $available = $this->max_students - $this->enrollment_count;
            return max(0, $available);
        } catch (\Exception $e) {
            Log::error('Error getting available slots: ' . $e->getMessage());
            return 0;
        }
    }

    // Additional scopes
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('end_date', '<', now());
    }
}
