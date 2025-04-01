<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchEnrollment extends Model
{
    protected $table = 'batch_enrollments';

    protected $fillable = [
        'batch_id',
        'user_id',
        'registration_status',
        'delivery_mode',
        'provider_type',
        'region',
        'province',
        'congressional_district',
        'municipality',
        'status'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    

    // Relationships
    public function course()
    {
        return $this->hasOneThrough(
            Course::class,
            CourseBatch::class,
            'id',
            'id',
            'batch_id',
            'course_id'
        );
    }
    public function courseBatch()
    {
        return $this->belongsTo(CourseBatch::class, 'batch_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Get course through courseBatch
    public function getCourseAttribute()
    {
        return $this->courseBatch?->course;
    }
}
