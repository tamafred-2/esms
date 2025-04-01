<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseBatch;
use App\Models\School;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function checkStaffAccess()
    {
        if (!Auth::check() || Auth::user()->usertype !== 'staff') {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Unauthorized access. Staff privileges required.');
        }
    }

    public function showBatches(School $school, Course $course)
    {
        $batches = $course->courseBatches()
            ->orderBy('created_at', 'desc')
            ->with(['enrollments']) // Removed 'instructor' from eager loading
            ->get();
    
        return view('staff.school.batches.show', compact('school', 'course', 'batches'));
    }

    public function dashboard()
    {
        // Check staff access
        $response = $this->checkStaffAccess();
        if ($response) {
            return $response;
        }

        // Get authenticated user with schools relationship
        $user = request()->user()->load('schools');
        
        return view('staff.dashboard', compact('user'));
    }

    public function show(Request $request, School $school)
    {
        if (!$request->user()->schools()->where('school_id', $school->id)->exists()) {
            abort(403, 'Unauthorized access');
        }
    
        $sectors = Sector::with(['courses' => function($query) use ($school) {
            $query->where('school_id', $school->id);
        }])->whereHas('courses', function($query) use ($school) {
            $query->where('school_id', $school->id);
        })->get();
    
        return view('staff.school.show', compact('school', 'sectors'));
    }
    public function showBatchStudents(School $school, Course $course, CourseBatch $batch)
    {
        $batchEnrollments = $batch->enrollments()
            ->with(['user'])  // Eager load the user relationship
            ->get();
    
        return view('staff.school.batches.student.show', compact('school', 'course', 'batch', 'batchEnrollments'));
    }
    
    public function profile()
    {
        // Check staff access
        $response = $this->checkStaffAccess();
        if ($response) {
            return $response;
        }

        return view('staff.profile');
    }
}
