<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Auth;


// Root route
Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->usertype === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif (Auth::user()->usertype === 'staff') {
            return redirect()->route('staff.dashboard');
        }
        Auth::logout();
    }
    return redirect('/login');
});


// Login routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [DashboardController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [DashboardController::class, 'login'])->name('login.post');
});

// Register Routes
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register.user');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');


Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard Routes
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    // Admin Routes Group
    Route::prefix('admin')->name('admin.')->group(function () {
        // User Management Routes
        Route::delete('/users/{user}/delete', [DashboardController::class, 'deleteUser'])->name('deleteuser');
        Route::get('/users/{user}/details', [DashboardController::class, 'getUserDetails'])->name('users.details');
        Route::post('/register-user', [DashboardController::class, 'registerUser'])->name('register-user');
        Route::get('/admin/users', [DashboardController::class, 'viewUsers'])->name('users');
        Route::put('/admin/users/{user}/update', [DashboardController::class, 'updateUser'])->name('updateuser');
        Route::delete('/admin/users/{user}/delete', [DashboardController::class, 'deleteUser'])->name('deleteuser');
        Route::get('/user-logs', [DashboardController::class, 'userlogs'])->name('userlogs');
        Route::get('/user-reports', [DashboardController::class, 'userreports'])->name('reports');
        Route::get('/dailytime', [DashboardController::class, 'dailytime'])->name('dailytime');
        Route::get('/schools/{school}', [CourseController::class, 'show'])->name('show');
        // Students 
        Route::put('/students/{student}', [DashboardController::class, 'updateStudent'])->name('students.update');
        Route::delete('/students/{student}', [DashboardController::class, 'deleteStudent'])->name('students.destroy');
        // Sector Routes
        Route::prefix('sectors')->name('sectors.')->group(function () {
            Route::get('/', [SectorController::class, 'index'])->name('index');
            Route::get('/create', [SectorController::class, 'create'])->name('create');
            Route::post('/', [SectorController::class, 'store'])->name('store');
            Route::get('/{sector}', [SectorController::class, 'show'])->name('show');
            Route::get('/{sector}/edit', [SectorController::class, 'edit'])->name('edit');
            Route::put('/{sector}', [SectorController::class, 'update'])->name('update');
            Route::delete('/{sector}', [DashboardController::class, 'destroySector'])->name('destroy');
        });

        // Events Routes
        Route::prefix('events')->name('events.')->group(function () {
            Route::get('/', [DashboardController::class, 'events'])->name('index');
            Route::post('/', [DashboardController::class, 'storeEvent'])->name('store');
            Route::get('/create', [DashboardController::class, 'createEvent'])->name('create');
            Route::get('/{event}/edit', [DashboardController::class, 'editEvent'])->name('edit');
            Route::put('/{event}', [EventController::class, 'updateEvent'])->name('update');
            Route::delete('/{event}', [EventController::class, 'destroyEvent'])->name('destroy');
        });

        // Profile Routes
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [DashboardController::class, 'profile'])->name('index');
            Route::get('/edit', [DashboardController::class, 'editProfile'])->name('edit');
            Route::post('/update', [DashboardController::class, 'updateProfile'])->name('update');
        });

        // School Management
        Route::prefix('school')->name('school.')->group(function () {
            Route::get('/create', [DashboardController::class, 'createSchool'])->name('create');
            Route::post('/', [DashboardController::class, 'storeSchool'])->name('store');
            Route::get('/{school}/edit', [DashboardController::class, 'editSchool'])->name('edit');
            Route::put('/{school}', [DashboardController::class, 'updateSchool'])->name('update');
            Route::delete('/{school}', [DashboardController::class, 'destroySchool'])->name('destroy');
            Route::get('/show/{school}', [DashboardController::class, 'showSchool'])->name('show');
            Route::get('/{school}/students', [DashboardController::class, 'students'])->name('students');

            // Staff management routes
            Route::post('/{school}/assign-staff', [DashboardController::class, 'assignStaff'])
                ->name('assign-staff');
            Route::delete('/{school}/remove-staff/{staffId}', [DashboardController::class, 'removeStaff'])
                ->name('remove-staff');
        });
        // Course Routes
        Route::prefix('course')->name('course.')->group(function () {
            Route::get('/', [CourseController::class, 'index'])->name('index');
            Route::get('/create', [CourseController::class, 'create'])->name('create');
            Route::post('/', [CourseController::class, 'store'])->name('store');
            Route::get('/{course}', [CourseController::class, 'show'])->name('show');
            Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('edit');
            Route::put('/{course}', [CourseController::class, 'update'])->name('update');
            Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');

            // Batch Routes
            Route::prefix('{course}/batches')->name('batches.')->group(function () {
                Route::get('/', [CourseController::class, 'showBatches'])->name('index');
                Route::get('/create', [CourseController::class, 'createBatch'])->name('create');
                Route::post('/', [CourseController::class, 'storeBatch'])->name('store');
                Route::get('/{batch}', [CourseController::class, 'showBatch'])->name('show');
                Route::get('/{batch}/edit', [CourseController::class, 'editBatch'])->name('edit');
                Route::put('/{batch}', [CourseController::class, 'updateBatch'])->name('update');
                Route::delete('/{batch}', [CourseController::class, 'destroyBatch'])->name('destroy');
                Route::post('/{batch}/enroll', [CourseController::class, 'enrollStudent'])->name('enroll');
            
                // Attendance Routes
                Route::prefix('{batch}/attendance')->name('attendance.')->group(function () {
                    Route::post('/', [AttendanceController::class, 'store'])->name('store');
                    Route::get('/', [AttendanceController::class, 'show'])->name('show');
                    Route::put('/{date}', [AttendanceController::class, 'update'])->name('update');
                    Route::get('/{date}/records', [AttendanceController::class, 'getAttendanceByDate'])->name('get-by-date');
                    Route::post('/update', [AttendanceController::class, 'updateAttendance'])->name('update-record');
                });
            });
        });
    });
});


Route::middleware(['auth'])->group(function () {
    Route::get('/staff/dashboard', [StaffController::class, 'dashboard'])->name('staff.dashboard');
    Route::get('/staff/profile', [StaffController::class, 'profile'])->name('staff.profile');
    Route::get('/staff/school/{school}', [StaffController::class, 'show'])->name('staff.school.show');
    Route::get('/staff/school/{school}/course/{course}/batches', [StaffController::class, 'showBatches'])
    ->name('staff.school.batches.show');
    Route::get('/staff/schools/{school}/courses/{course}/batches/{batch}/students', [StaffController::class, 'showBatchStudents'])
    ->name('staff.school.batches.student.show');
    // ... other staff routes
});


Route::post('/logout', [DashboardController::class, 'logout'])->name('logout');

// Fallback route
Route::fallback(function () {
    if (Auth::check()) {
        switch (Auth::user()->usertype) {
            case 'admin':
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Page not found.');
            case 'staff':
                return redirect()->route('staff.dashboard')
                    ->with('error', 'Page not found.');
            default:
                return redirect()->route('login')
                    ->with('error', 'Page not found.');
        }
    }
    return redirect()->route('login')
        ->with('error', 'Page not found.');
});
