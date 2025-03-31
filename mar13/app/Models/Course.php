<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Course extends Model
{
    use HasFactory, SoftDeletes;
    protected $withCount = ['courseBatches'];
    protected $fillable = [
        'name',
        'description',
        'duration_days',
        'school_id',
        'sector_id',
        'morning_schedule',
        'afternoon_schedule',
        'staff_id'
    ];

    protected $casts = [
        'morning_schedule' => 'array',
        'afternoon_schedule' => 'array'
    ];

    public function courseBatches()
    {
        return $this->hasMany(CourseBatch::class);
    }

    // Alias method to maintain compatibility
    public function batches()
    {
        return $this->courseBatches();
    }

    // Other relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withTimestamps()
            ->withPivot('status', 'enrolled_at');
    }

    // Batch-related methods
    public function activeBatches()
    {
        return $this->courseBatches()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function upcomingBatches()
    {
        return $this->courseBatches()
            ->where('start_date', '>', now());
    }

    public function completedBatches()
    {
        return $this->courseBatches()
            ->where('end_date', '<', now());
    }

    // Attribute accessors
    public function getTotalEnrolledStudentsAttribute()
    {
        try {
            return $this->courseBatches()
                ->withCount('enrollments')
                ->get()
                ->sum('enrollments_count');
        } catch (\Exception $e) {
            Log::error('Error in getTotalEnrolledStudentsAttribute: ' . $e->getMessage());
            return 0;
        }
    }

    public function getAvailableSlotsAttribute()
    {
        try {
            $activeBatches = $this->courseBatches()
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();

            $totalSlots = $activeBatches->sum('max_students');
            $totalEnrolled = $activeBatches->sum(function($batch) {
                return $batch->enrollments()->count();
            });

            return max(0, $totalSlots - $totalEnrolled);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableSlotsAttribute: ' . $e->getMessage());
            return 0;
        }
    }

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->whereHas('courseBatches', function($q) {
            $q->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
        });
    }

    public function scopeBySchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeBySector($query, $sectorId)
    {
        return $query->where('sector_id', $sectorId);
    }

    // Helper methods
    public function isActive()
    {
        return $this->courseBatches()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->exists();
    }

    public function hasAvailableSlots()
    {
        return $this->available_slots > 0;
    }

    public function canEnroll()
    {
        return $this->isActive() && $this->hasAvailableSlots();
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::deleting(function($course) {
            // Delete related batches when course is deleted
            $course->courseBatches()->delete();
        });
    }
}
