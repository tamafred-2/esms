<?php

namespace App\Http\Controllers;

use App\Models\BatchEnrollment;
use App\Models\School;
use App\Models\User;
use App\Models\Event;
use App\Models\Course;
use App\Models\Sector;
use App\Models\Staff;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DashboardController extends BaseController
{
    public function dailytime()
    {
        // Get all course batches with relationships
        $batches = DB::table('course_batches')
            ->join('courses', 'course_batches.course_id', '=', 'courses.id')
            ->join('schools', 'courses.school_id', '=', 'schools.id')  // Add this join
            ->select(
                'course_batches.id',
                'course_batches.batch_name',
                'course_batches.max_students',
                'course_batches.status',
                'course_batches.created_at',
                'course_batches.start_date',
                'course_batches.end_date',
                'courses.name as course_name',
                'schools.name as school_name'  // Add this field
            )
            ->orderBy('course_batches.created_at', 'desc');
    
        // Rest of your controller code remains the same
        if (request('course')) {
            $batches->where('course_batches.course_id', request('course'));
        }
    
        $courses = DB::table('courses')->orderBy('name')->get();
    
        $enrolledCounts = DB::table('batch_enrollments')
            ->select('batch_id', DB::raw('count(*) as enrolled_count'))
            ->groupBy('batch_id')
            ->pluck('enrolled_count', 'batch_id')
            ->toArray();
    
        return view('admin.dailytime', [
            'batches' => $batches->paginate(10),
            'courses' => $courses,
            'courseFilter' => request('course'),
            'enrolledCounts' => $enrolledCounts
        ]);
    }

    public function storeEvent(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'date' => 'required|date',
                'time' => 'required',
                'description' => 'nullable|string|max:1000',
            ]);
            $event = Event::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Event created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            if (Auth::user()->usertype === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            Auth::logout();
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
    
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Invalid credentials.'])
                ->withInput($request->only('email'));
        }
    
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Redirect based on user type
            if ($user->usertype === 'staff') {
                return redirect()->intended(route('staff.dashboard'))
                    ->with('success', 'Welcome, ' . $user->firstname . ' ' . $user->lastname);
            } elseif ($user->usertype === 'admin') {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Welcome, ' . $user->firstname . ' ' . $user->lastname);
            }
            
            // If usertype is neither staff nor admin
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Invalid user type.')
                ->withInput($request->only('email'));
        }
    
        return redirect()->route('login')
            ->withErrors(['email' => 'Invalid credentials.'])
            ->withInput($request->only('email'));
    }
    
    
    public function __construct()
    {
        $this->middleware('guest')->only(['showLoginForm', 'login']);
        $this->middleware(['auth', 'admin'])->only('admin');
    }
    public function getStudentStatistics(Request $request)
    {
        try {
            $query = User::whereHas('schools', function($q) {
                $q->where('school_user.role', 'student');
            });

            if ($request->has('course_id')) {
                $query->where('course_id', $request->course_id);
            }

            $statistics = [
                'competent' => (clone $query)->where('status', 'competent')->count(),
                'incompetent' => (clone $query)->where('status', 'incompetent')->count(),
                'dropped' => (clone $query)->where('status', 'dropped')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Statistics Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics'
            ], 500);
        }
    }

    public function admin()
    {
        $icon = '<i class="bi bi-speedometer2 me-2"></i> Dashboard';
        $button = '';
    
        // Paginate schools with a unique parameter name 'school-page'
        $schools = School::withCount([
            'students' => function($query) {
                $query->where('school_user.role', 'student');
            }
        ])
        ->orderBy('name', 'asc')
        ->paginate(4, ['*'], 'school-page'); // Add custom page name
    
        // Paginate events with a unique parameter name 'event-page'
        $events = Event::query()
            ->where('date', '>=', now()->startOfDay())
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->paginate(3, ['*'], 'event-page'); // Add custom page name
    
        // Get counts for statistics
        $statistics = [
            'total_users' => User::count(),
            'total_schools' => School::count(),
            'total_events' => Event::count(),
            'total_courses' => Course::count(),
        ];
    
        return view('admin.dashboard', compact('icon', 'button', 'events', 'schools', 'statistics'));
    }
    
    private function getEventDistribution()
    {
        return Event::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }
    private function getCoursePopularity()
    {
        return Course::withCount('users')
            ->orderByDesc('users_count')
            ->take(5)
            ->get()
            ->pluck('users_count', 'name')
            ->toArray();
    }
    private function getMonthlyUserRegistrations()
    {
        return User::select(
            DB::raw('COUNT(*) as count'),
            DB::raw('MONTH(created_at) as month')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
    }

    public function createUser()
    {
        $icon = '<i class="bi bi-person-plus-fill me-2"></i> Create User';
        $button = '';
        $schools = School::orderBy('name')->get(); // Add this line to get schools
        return view('admin.createuser', compact('icon', 'button', 'schools'));
    }
    
    public function viewUsers(Request $request)
    {
        $query = User::query();

        // Handle search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('firstname', 'LIKE', "%{$searchTerm}%")
                ->orWhere('lastname', 'LIKE', "%{$searchTerm}%")
                ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                ->orWhere('contact_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Handle usertype filter
        if ($request->has('usertype') && $request->usertype != '') {
            $query->where('usertype', $request->usertype);
        }

        // Handle sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate(5)->withQueryString();

        // If it's an AJAX request for user details
        if ($request->ajax()) {
            if ($request->has('userId')) {
                $user = User::findOrFail($request->userId);
                return response()->json($user);
            }
        }

        return view('admin.viewuser', [
            'users' => $users,
            'icon' => '<i class="bi bi-people-fill me-2"></i> View Users',
            'button' => '<button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createUserModal"><i class="bi bi-person-plus-fill me-2"></i>Create User</button>',
            'selected_usertype' => $request->usertype,
            'search_term' => $request->search,
            'sort_field' => $sortField,
            'sort_direction' => $sortDirection
        ]);
    }
    public function showUser(User $user)
    {
        return response()->json($user);
    }
    
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'usertype' => 'required|in:admin,staff,student',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'contact_number' => 'required|string|max:20',
            'street_address' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
        ]);
    
        try {
            $user->update($validated);
            return redirect()->route('admin.users')->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update user');
        }
    }
    public function deleteUser(User $user)
    {
        try {
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        }
    }
    
    public function userlogs()
    {
        $icon = '<i class="bi bi-clock-history me-2"></i> Activity Logs';
        $button = '';
        return view('admin.userlogs', compact('icon', 'button'));
    }
    public function userreports()
    {
        $icon = '<i class="bi bi-card-checklist me-2"></i> Reports';
        $button = '<button type="button" class="btn btn-custom" style="color: white;"><i class="bi bi-download me-2"></i>Export Report</button>';
        return view('admin.reports', compact('icon', 'button'));
    }
    public function profile()
    {
        $icon = '<i class="bi bi-person me-2"></i> Profile';
        $button = '';
        return view('admin.profile', compact('icon', 'button'));
    }
    public function editProfile()
    {
        $icon = '<i class="bi bi-person-gear me-2"></i> Edit Profile';
        $button = '';
        return view('admin.profile.edit', compact('icon', 'button'));
    }
    public function updateProfile(Request $request)
    {
        // Your update logic here
        return redirect()->route('admin.profile.index')->with('success', 'Profile updated successfully');
    }


    // School Management Methods
    public function storeSchool(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'street_number' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'contact_number' => 'required|regex:/^[0-9]+$/|min:7|max:15',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
    
        try {
            $data = $request->except('logo_path');
            
            if ($request->hasFile('logo_path')) {
                $path = $request->file('logo_path')->store('school-logos', 'public');
                $data['logo_path'] = $path;
            }
    
            $school = School::create($data);
    
            return response()->json([
                'success' => true,
                'message' => 'School added successfully!',
                'school' => $school
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add school: ' . $e->getMessage()
            ], 500);
        }
    }
    
    

    public function createSchool()
    {
        $icon = '<i class="bi bi-building-add me-2"></i> Add School';
        $button = '';
        return view('admin.school.create', compact('icon', 'button'));
    }

    public function editSchool(School $school)
    {
        $icon = '<i class="bi bi-building me-2"></i> Edit School';
        $button = '';
        
        return view('admin.school.edit', compact('school', 'icon', 'button'));
    }

    public function updateSchool(Request $request, School $school)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'street_number' => 'required|string|max:255',
                'barangay' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'province' => 'required|string|max:255',
                'contact_number' => 'required|regex:/^[0-9]+$/|min:7|max:15',
                'logo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            // Remove logo_path from validated data if no new file is uploaded
            if (!$request->hasFile('logo_path')) {
                unset($validated['logo_path']);
            }

            // Handle logo upload if a new file is provided
            if ($request->hasFile('logo_path')) {
                // Delete old logo if it exists
                if ($school->logo_path && Storage::disk('public')->exists($school->logo_path)) {
                    Storage::disk('public')->delete($school->logo_path);
                }
                
                // Store new logo
                $validated['logo_path'] = $request->file('logo_path')->store('school-logos', 'public');
            }

            // Update school with validated data
            $school->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'School updated successfully!',
                'school' => $school->fresh() // Return fresh instance of the school
            ]);

        } catch (\Exception $e) {
            Log::error('School update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update school: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, School $school)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'contact_number' => 'required|string',
                'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            DB::beginTransaction();

            if ($request->hasFile('logo_path')) {
                // Delete old logo if exists
                if ($school->logo_path) {
                    $oldLogoPath = public_path($school->logo_path);
                    if (file_exists($oldLogoPath)) {
                        unlink($oldLogoPath);
                    }
                }

                $file = $request->file('logo_path');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                
                // Move the file directly to public/images directory
                $file->move(public_path('images'), $filename);
                
                // Update the path
                $school->logo_path = 'images/' . $filename;
            }

            $school->name = $validated['name'];
            $school->address = $validated['address'];
            $school->contact_number = $validated['contact_number'];
            $school->save();

            DB::commit();

            return redirect()
                ->route('admin.school')
                ->with('success', 'School updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('School update failed: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update school. Please try again.');
        }
    }

    public function destroySchool($id)
    {
        try {
            $school = School::findOrFail($id);
            
            // Delete the school logo if it exists
            if ($school->logo_path && Storage::disk('public')->exists($school->logo_path)) {
                Storage::disk('public')->delete($school->logo_path);
            }
            
            $school->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'School deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete school: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroySector(Sector $sector)
    {
        try {
            // Log attempt
            Log::info('Attempting to delete sector: ' . $sector->id);

            // Check if sector has any courses
            $courseCount = Course::where('sector_id', $sector->id)->count();
            
            if ($courseCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete sector that has courses.'
                ], 403);
            }

            // Delete the sector
            $sector->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sector deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Sector deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the sector: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroyBatch(Course $course, Batch $batch)
    {
        try {
            // Check if the batch belongs to the course
            if ($batch->course_id !== $course->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This batch does not belong to the specified course.'
                ], 403);
            }

            // Check for enrollments using the Enrollment model directly
            $enrollmentCount = BatchEnrollment::where('batch_id', $batch->id)->count();
            
            if ($enrollmentCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete batch with enrolled students.'
                ], 403);
            }

            // Delete the batch
            $batch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Batch deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Batch deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the batch.'
            ], 500);
        }
    }
    public function getUserDetails(User $user)
    {
        try {
            Log::info('Attempting to fetch user details', ['user_id' => $user->id]);
            
            $userData = User::findOrFail($user->id);
            
            Log::info('User details fetched successfully', ['user' => $userData]);
            
            return response()->json([
                'success' => true,
                'data' => $userData
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching user details', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user details: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function showSchool(School $school)
    {
        // Load courses with their batch counts
        $school->load(['courses' => function($query) {
            $query->withCount('courseBatches');
        }]);
        
        $icon = '<i class="bi bi-building me-2"></i> School Details';
        $button = '<a href="' . route('admin.dashboard') . '" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back to School
        </a>';
        
        // Get available staff (users with staff usertype who aren't already assigned to this school)
        $availableStaff = User::where('usertype', 'staff')
            ->whereDoesntHave('schools', function($query) use ($school) {
                $query->where('school_id', $school->id);
            })
            ->orderBy('firstname')
            ->get();
        
        return view('showschool', compact(
            'school',
            'availableStaff',
            'icon',
            'button'
        ));
    }
    
    
    public function students(School $school, Request $request)
    {
        $query = $school->students();
    
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
    
        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
    
        // Sorting
        switch ($request->sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name':
                $query->orderBy('name');
                break;
            default:
                $query->latest();
        }
    
        $students = $query->paginate(10);
    
        return view('admin.school.students', [
            'school' => $school,
            'students' => $students,
            'icon' => 'bi bi-people',
            'button' => [
                'text' => 'Back to School',
                'route' => route('showschool', $school->id)
            ]
        ]);
    }
    
    public function registerUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'usertype' => ['required', 'in:admin,staff'],
                'firstname' => ['required', 'string', 'max:255'],
                'lastname' => ['required', 'string', 'max:255'],
                'middlename' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'contact_number' => ['required', 'string', 'max:20'],
                'street_address' => ['required', 'string', 'max:255'],
                'barangay' => ['required', 'string', 'max:255'],
                'municipality' => ['required', 'string', 'max:255'],
                'province' => ['required', 'string', 'max:255'],
            ]);
    
            $user = User::create([
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'],
                'middlename' => $validated['middlename'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'contact_number' => $validated['contact_number'],
                'street_address' => $validated['street_address'],
                'barangay' => $validated['barangay'],
                'municipality' => $validated['municipality'],
                'province' => $validated['province'],
                'usertype' => $validated['usertype'],
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'User created successfully!'
            ]);
    
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
    
        } catch (\Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user. Please try again.'
            ], 500);
        }
    }
    
    
    public function updateStaffPosition(Request $request, School $school, Staff $staff)
    {
        $validated = $request->validate([
            'position' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'qualifications' => 'nullable|string',
            'responsibilities' => 'nullable|string'
        ]);
    
        try {
            $staff->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Staff position updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating staff position.'
            ], 422);
        }
    }

    public function removeStaff(School $school, $staffId)
    {
        try {
            // Check if staff exists and is assigned to the school
            $staff = $school->users()->where('user_id', $staffId)->first();
            
            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff is not assigned to this school'
                ]);
            }
    
            // Remove the staff from the school
            $school->users()->detach($staffId);
    
            return response()->json([
                'success' => true,
                'message' => 'Staff removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Staff removal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove staff'
            ]);
        }
    }
    

    public function assignStaff(Request $request, School $school)
    {
        try {
            $validated = $request->validate([
                'staff_id' => 'required|exists:users,id'
            ]);

            $user = User::find($validated['staff_id']);
            
            // Verify the user is a staff member
            if ($user->usertype !== 'staff') {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user is not a staff member'
                ]);
            }

            // Check if staff is already assigned
            if ($school->users()->where('user_id', $validated['staff_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff member is already assigned to this school'
                ]);
            }

            // Assign staff to school
            $school->users()->attach($validated['staff_id']);

            return response()->json([
                'success' => true,
                'message' => 'Staff assigned successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Staff assignment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign staff: ' . $e->getMessage()
            ]);
        }
    }

    public function updateStudent(Request $request, User $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender' => 'required|in:Male,Female',
            'contact_number' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->id,
            'address' => 'required|string'
        ]);
    
        $student->update([
            'firstname' => $validated['first_name'],
            'lastname' => $validated['last_name'],
            'middlename' => $validated['middle_name'],
            'gender' => $validated['gender'],
            'contact_number' => $validated['contact_number'],
            'email' => $validated['email'],
            'address' => $validated['address']
        ]);
    
        return redirect()->back()->with('success', 'Student information updated successfully');
    }
    
    public function deleteStudent(User $student)
    {
        // Remove student from batch but don't delete the user
        BatchEnrollment::where('user_id', $student->id)->delete();
    
        return redirect()->back()->with('success', 'Student removed from batch successfully');
    }
    

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->with('success', 'Successfully logged out.');
    }
}
