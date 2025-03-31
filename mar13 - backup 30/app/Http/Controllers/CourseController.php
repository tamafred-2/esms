<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BatchEnrollment;
use App\Models\Course;
use App\Models\CourseBatch;
use App\Models\School;
use App\Models\Sector;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with([
            'sector', 
            'school',
            'courseBatches' // Changed from 'batches' to 'courseBatches'
        ])->paginate(10);
        
        $sectors = Sector::all();
        $icon = 'bi bi-book';
        $button = [
            'text' => 'Add New Course',
            'route' => route('admin.courses.create')
        ];
        
        return view('admin.course.index', compact('courses', 'sectors', 'icon', 'button'));
    }

    public function create()
    {
        $schools = School::all();
        $sectors = Sector::all();
        $icon = 'bi bi-book';
        $button = [
            'text' => 'Back to Courses',
            'route' => route('admin.course.index')
        ];
        return view('admin.courses.create', compact('schools', 'sectors', 'icon', 'button'));
    }
    
    public function store(Request $request)
    {
        // Validate all form inputs
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'duration_days' => 'required|integer|min:1',
                'school_id' => 'required|exists:schools,id',
                'sector_id' => 'required|exists:sectors,id',
                'morning_in' => 'required|date_format:H:i',
                'morning_out' => 'required|date_format:H:i|after:morning_in',
                'afternoon_in' => 'required|date_format:H:i|after:morning_out',
                'afternoon_out' => 'required|date_format:H:i|after:afternoon_in',
            ]);
    
            // Check for existing course
            $existingCourse = Course::where('name', $validated['name'])
                ->where('school_id', $validated['school_id'])
                ->first();
    
            if ($existingCourse) {
                return response()->json([
                    'success' => false,
                    'message' => 'A course with this name already exists in this school'
                ], 422);
            }
    
            DB::beginTransaction();
    
            // Create the course with all validated data
            $course = Course::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'duration_days' => $validated['duration_days'],
                'school_id' => $validated['school_id'],
                'sector_id' => $validated['sector_id'],
                'morning_schedule' => [
                    'in' => $validated['morning_in'],
                    'out' => $validated['morning_out']
                ],
                'afternoon_schedule' => [
                    'in' => $validated['afternoon_in'],
                    'out' => $validated['afternoon_out']
                ],
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Course added successfully'
            ]);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Course creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add course: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show(Course $course): View
    {
        $sectors = Sector::all();
        $course->load(['sector', 'school', 'batches']);
        $icon = 'bi bi-book';
        $button = [
            'text' => 'Back to Courses',
            'route' => route('admin.course.index')
        ];
        return view('admin.course.show', compact('course', 'sectors', 'icon', 'button'));
    }

    public function edit(Course $course)
    {
        $schools = School::all();
        $sectors = Sector::all();
        $icon = 'bi bi-book';
        $button = [
            'text' => 'Back to Course',
            'route' => route('admin.course.show', $course)
        ];
        return view('admin.courses.edit', compact('course', 'schools', 'sectors', 'icon', 'button'));
    }

    public function update(Request $request, Course $course)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'duration_days' => 'required|integer|min:1',
                'school_id' => 'required|exists:schools,id',
                'sector_id' => 'required|exists:sectors,id',
                'morning_in' => 'required|date_format:H:i',
                'morning_out' => 'required|date_format:H:i|after:morning_in',
                'afternoon_in' => 'required|date_format:H:i|after:morning_out',
                'afternoon_out' => 'required|date_format:H:i|after:afternoon_in',
            ]);
    
            // Check for existing course with same name in same school (excluding current course)
            $existingCourse = Course::where('name', $validated['name'])
                ->where('school_id', $validated['school_id'])
                ->where('id', '!=', $course->id)
                ->first();
    
            if ($existingCourse) {
                return response()->json([
                    'success' => false,
                    'message' => 'A course with this name already exists in this school'
                ], 422);
            }
    
            DB::beginTransaction();
    
            $course->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'duration_days' => $validated['duration_days'],
                'school_id' => $validated['school_id'],
                'sector_id' => $validated['sector_id'],
                'morning_schedule' => [
                    'in' => $validated['morning_in'],
                    'out' => $validated['morning_out']
                ],
                'afternoon_schedule' => [
                    'in' => $validated['afternoon_in'],
                    'out' => $validated['afternoon_out']
                ],
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Course updated successfully'
            ]);
    
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Course update failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update course: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $course = Course::findOrFail($id);
            
            DB::beginTransaction();
            
            // Check if course has any related data
            if ($course->batches()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete course with existing batches'
                ], 422);
            }
            
            $course->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Course deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Course deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting course'
            ], 500);
        }
    }

    public function showBatches(Course $course)
    {
        $batches = $course->batches()->paginate(10);
        $sectors = Sector::all();
        $icon = 'bi bi-collection';
        $button = [
            'text' => 'Back to Course',
            'route' => route('admin.course.show', $course)
        ];
        return view('admin.courses.batches.index', compact('course', 'batches', 'sectors', 'icon', 'button'));
    }

    public function createBatch(Course $course)
    {
        $sectors = Sector::all();
        $icon = 'bi bi-collection';
        $button = [
            'text' => 'Back to Batches',
            'route' => route('admin.course.batches.index', $course)
        ];
        return view('admin.courses.batches.create', compact('course', 'sectors', 'icon', 'button'));
    }
    
    public function storeBatch(Request $request, $courseId)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'batch_name' => 'required|string|max:255',
                'start_date' => 'required|date|after_or_equal:today',
                'max_students' => 'required|integer|min:1'
            ]);
    
            // Find the course
            $course = Course::findOrFail($courseId);
    
            // Calculate end date based on course duration
            $endDate = Carbon::parse($validated['start_date'])->addDays($course->duration_days);
    
            DB::beginTransaction();
    
            // Create batch
            $batch = $course->batches()->create([
                'batch_name' => $validated['batch_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $endDate,
                'max_students' => $validated['max_students']
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Batch created successfully',
                'batch' => $batch
            ]);
    
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch creation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating batch: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function showBatch(Course $course, CourseBatch $batch)
    {
        $enrollments = $batch->enrollments()->with('user')->get()->map(function($enrollment) {
            return (object)[
                // Basic Information
                'id' => $enrollment->id,
                'lastname' => $enrollment->user->lastname,
                'firstname' => $enrollment->user->firstname,
                'middlename' => $enrollment->user->middlename,
                'email' => $enrollment->user->email,
                'contact_number' => $enrollment->user->contact_number,
                'created_at' => $enrollment->created_at,
                'status' => $enrollment->status,
                
                // Personal Information
                'gender' => $enrollment->user->gender,
                'birthdate' => $enrollment->user->birthdate,
                'civil_status' => $enrollment->user->civil_status,
                'nationality' => $enrollment->user->nationality,
                'classification' => $enrollment->user->classification,
                'age' => $enrollment->user->age, // Added age
                
                // Address Information
                'street_address' => $enrollment->user->street_address,
                'barangay' => $enrollment->user->barangay,
                'municipality' => $enrollment->user->municipality,
                'province' => $enrollment->user->province,
                'district' => $enrollment->user->district,
                
                // Educational Background
                'highest_grade' => $enrollment->user->highest_grade, // Added highest grade
                'course_program' => $enrollment->user->course_program, // Added course program
                
                // Additional Fields from BatchEnrollment
                'registration_status' => $enrollment->registration_status,
                'delivery_mode' => $enrollment->delivery_mode,
                'provider_type' => $enrollment->provider_type,
                'region' => $enrollment->region,
                'provider_province' => $enrollment->province,
                'congressional_district' => $enrollment->congressional_district,
                'provider_municipality' => $enrollment->municipality,
                
                // Add user relationship for access to all user fields
                'user' => $enrollment->user
            ];
        });
        
        // Get the school from the course relationship
        $school = $course->school;
        // Get sectors specific to this school
        $sectors = Sector::where('school_id', $school->id)->get();
        
        $icon = 'bi bi-collection';
        $button = [
            'text' => 'Back to Batches',
            'route' => route('admin.course.batches.index', $course) 
        ];
    
        return view('admin.courses.batches.show', compact(
            'course',
            'batch',
            'enrollments',
            'sectors',
            'icon',
            'button',
            'school'
        ));
    }
    
    public function editBatch(Request $request, Course $course, CourseBatch $batch)
    {
        try {
            $validated = $request->validate([
                'batch_name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'max_students' => 'required|integer|min:' . $batch->enrollments()->count(),
            ]);
    
            // Check if the batch exists and belongs to the course
            if ($batch->course_id !== $course->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch does not belong to this course'
                ], 404);
            }
    
            // Calculate end date based on course duration
            $endDate = Carbon::parse($validated['start_date'])
                ->addDays($course->duration_days - 1);
    
            DB::beginTransaction();
    
            $batch->update([
                'batch_name' => $validated['batch_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $endDate,
                'max_students' => $validated['max_students']
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Batch updated successfully'
            ]);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch update failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update batch'
            ], 500);
        }
    }
    
    public function updateBatch(Request $request, Course $course, CourseBatch $batch)
    {
        try {
            $validated = $request->validate([
                'batch_name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'max_students' => 'required|integer|min:' . $batch->enrollments()->count(),
            ]);
    
            // Calculate end date based on course duration
            $endDate = Carbon::parse($validated['start_date'])
                ->addDays($course->duration_days - 1);
    
            DB::beginTransaction();
    
            $batch->update([
                'batch_name' => $validated['batch_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $endDate,
                'max_students' => $validated['max_students']
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Batch updated successfully'
            ]);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch update failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update batch'
            ], 500);
        }
    }

    public function destroyBatch($courseId, $batchId)
    {
        try {
            $batch = CourseBatch::findOrFail($batchId);
            
            // Check if batch belongs to the course
            if ($batch->course_id != $courseId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch does not belong to this course'
                ], 403);
            }
    
            // Check for enrollments
            if ($batch->enrollments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete batch with existing enrollments'
                ], 422);
            }
    
            DB::beginTransaction();
            
            $batch->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Batch deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting batch'
            ], 500);
        }
    }
    
    
    public function enroll(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            // TVET Provider Profile
            'region' => 'required|string',
            'province' => 'required|string',
            'congressional_district' => 'required|string',
            'municipality' => 'required|string',
            'provider_type' => 'required|string',
            
            // Program Profile
            'registration_status' => 'required|string',
            'delivery_mode' => 'required|string',
            
            // Learner Profile
            'lastname' => 'required|string',
            'firstname' => 'required|string',
            'middlename' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'contact_number' => 'required|string',
            'street_address' => 'required|string',
            'barangay' => 'required|string',
            'municipality' => 'required|string',
            'province' => 'required|string',
            'gender' => 'required|in:Male,Female',
            'birthdate' => 'required|date',
            'civil_status' => 'required|string',
            'nationality' => 'required|string',
            'classification' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Create user with student profile
            $user = User::create([
                'lastname' => $validated['lastname'],
                'firstname' => $validated['firstname'],
                'middlename' => $validated['middlename'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'usertype' => 'Student',
                'contact_number' => $validated['contact_number'],
                'street_address' => $validated['street_address'],
                'barangay' => $validated['barangay'],
                'municipality' => $validated['municipality'],
                'province' => $validated['province'],
                'gender' => $validated['gender'],
                'birthdate' => $validated['birthdate'],
                'civil_status' => $validated['civil_status'],
                'nationality' => $validated['nationality'],
                'classification' => $validated['classification']
            ]);

            // Create enrollment record
            $enrollment = BatchEnrollment::create([
                'batch_id' => $batch->id,
                'user_id' => $user->id,
                'registration_status' => $validated['registration_status'],
                'delivery_mode' => $validated['delivery_mode'],
                'provider_type' => $validated['provider_type'],
                'region' => $validated['region'],
                'province' => $validated['province'],
                'congressional_district' => $validated['congressional_district'],
                'municipality' => $validated['municipality'],
                'status' => 'Active'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student enrolled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Enrollment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to enroll student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBatchStats(CourseBatch $batch)
    {
        $stats = [
            'total_enrollments' => $batch->enrollments()->count(),
            'active_enrollments' => $batch->enrollments()
                ->where('status', 'enrolled')
                ->count(),
            'completed_enrollments' => $batch->enrollments()
                ->where('status', 'completed')
                ->count(),
            'dropped_enrollments' => $batch->enrollments()
                ->where('status', 'dropped')
                ->count(),
            'available_slots' => $batch->max_students - $batch->enrollments()->count()
        ];

        return response()->json($stats);
    }
    public function enrollStudent(Request $request, Course $course, CourseBatch $batch)
    {
        try {
            $validated = $request->validate([
                'lastname' => 'required|string|max:255',
                'firstname' => 'required|string|max:255',
                'middlename' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email',
                'contact_number' => 'required|string|max:20',
                'password' => 'required|min:8|confirmed',
                'gender' => 'required|in:Male,Female',
                'birthdate' => 'required|date',
                'street_address' => 'required|string',
                'barangay' => 'required|string',
                'municipality' => 'required|string',
                'province' => 'required|string',
                'district' => 'required|string',
                'civil_status' => 'required|string',
                'nationality' => 'required|string',
                'classification' => 'required|string',
                'highest_grade' => 'required|string',
                'course_program' => 'required|string',
                // TVET Provider Profile fields
                'registration_status' => 'required|string',
                'delivery_mode' => 'required|string',
                'provider_type' => 'required|string',
                'region' => 'required|string',
                'congressional_district' => 'required|string',
            ]);
    
            DB::beginTransaction();
    
            // Create new user
            $user = User::create([
                'lastname' => $validated['lastname'],
                'firstname' => $validated['firstname'],
                'middlename' => $validated['middlename'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'contact_number' => $validated['contact_number'],
                'gender' => $validated['gender'],
                'birthdate' => $validated['birthdate'],
                'street_address' => $validated['street_address'],
                'barangay' => $validated['barangay'],
                'municipality' => $validated['municipality'],
                'province' => $validated['province'],
                'district' => $validated['district'],
                'civil_status' => $validated['civil_status'],
                'nationality' => $validated['nationality'],
                'classification' => $validated['classification'],
                'highest_grade' => $validated['highest_grade'],
                'course_program' => $validated['course_program'],
                'usertype' => 'Student'
            ]);
    
            // Create enrollment record
            $enrollment = BatchEnrollment::create([
                'user_id' => $user->id,
                'batch_id' => $batch->id,
                'status' => 'enrolled',
                'enrolled_at' => now(),
                'registration_status' => $validated['registration_status'],
                'delivery_mode' => $validated['delivery_mode'],
                'provider_type' => $validated['provider_type'],
                'region' => $validated['region'],
                'province' => $validated['province'],
                'congressional_district' => $validated['congressional_district'],
                'municipality' => $validated['municipality']
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Student enrolled successfully',
                'data' => [
                    'user' => $user,
                    'enrollment' => $enrollment
                ]
            ]);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
    
        } catch (\Exception $e) {
            DB::rollBack(); // Add this line to ensure rollback on any exception
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
}
