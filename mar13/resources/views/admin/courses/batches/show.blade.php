<x-adminlayout>
<meta name="course-id" content="{{ $course->id }}">
<meta name="batch-id" content="{{ $batch->id }}">
@push('scripts')
    <script>
        window.courseSchedules = {
            morning: {
                in: "{{ $schedules['morning']['in'] }}",
                out: "{{ $schedules['morning']['out'] }}"
            },
            afternoon: {
                in: "{{ $schedules['afternoon']['in'] }}",
                out: "{{ $schedules['afternoon']['out'] }}"
            }
        };
    </script>

    {{-- Then your main JavaScript code --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Your existing JavaScript code here
        });
    </script>
@endpush

    <div class="container-fluid px-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">{{ $course->name }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.school.show', ['school' => $school->id]) }}">Courses</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.course.batches.index', ['course' => $course->id]) }}">Batches</a></li>
                                                
                        <li class="breadcrumb-item active">Batch #{{ $batch->id }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.course.batches.index', $course) }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Batches
            </a>
        </div>

        <!-- Content Grid -->
        <div class="row">
            <!-- Left Column - Batch Details -->
            <div class="col-md-4">
                <!-- Batch Information Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Batch Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Training Period</label>
                            <p class="mb-1">
                                <i class="bi bi-calendar3"></i> 
                                {{ $batch->start_date->format('M d, Y') }} - {{ $batch->end_date->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Student Capacity</label>
                            <p class="mb-1">
                                <i class="bi bi-people"></i>
                                {{ $enrollments->count() }}/{{ $batch->max_students }} Students
                            </p>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" 
                                    @style([
                                        'width' => ($enrollments->count() / $batch->max_students) * 100 . '%'
                                    ])
                                    aria-valuenow="{{ $enrollments->count() }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="{{ $batch->max_students }}">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-1">
                                @if($batch->start_date->isFuture())
                                    <span class="badge bg-info">Upcoming</span>
                                @elseif($batch->end_date->isPast())
                                    <span class="badge bg-secondary">Completed</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- School Information Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">School Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">School Name</label>
                            <p class="mb-1">{{ $school->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Region</label>
                            <p class="mb-1">{{ $school->region }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Province</label>
                            <p class="mb-1">{{ $school->province }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Municipality</label>
                            <p class="mb-1">{{ $school->municipality }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Enrolled Students -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Enrolled Students</h5>
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#viewAttendanceModal">
                            <i class="bi bi-calendar-check"></i> View Attendance
                        </button>
                        <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                            <i class="bi bi-calendar-check"></i> Attendance
                        </button>
                        </div>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#enrollStudentModal">
                            <i class="bi bi-plus-lg"></i> Add Student
                        </button>
                    </div>
                    <div class="card-body">
                        @if($enrollments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="enrollmentsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Contact Number</th>
                                            <th>Enrollment Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($enrollments as $enrollment)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <div class="fw-semibold">
                                                                {{ $enrollment->lastname }}, {{ $enrollment->firstname }} 
                                                                {{ $enrollment->middlename ? substr($enrollment->middlename, 0, 1) . '.' : '' }}
                                                            </div>
                                                            <div class="small text-muted">{{ $enrollment->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $enrollment->contact_number }}</td>
                                                <td>{{ $enrollment->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <span class="badge bg-success">Active</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info" title="View" data-bs-toggle="modal" data-bs-target="#viewStudentModal{{ $enrollment->id }}">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- View Student Modal -->
                                            <div class="modal fade" id="viewStudentModal{{ $enrollment->id }}" tabindex="-1" aria-labelledby="viewStudentModalLabel{{ $enrollment->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="viewStudentModalLabel{{ $enrollment->id }}">Student Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Learner Profile -->
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <h6 class="fw-bold text-primary mb-3">Learner Profile</h6>
                                                                    
                                                                    <!-- Personal Information -->
                                                                    <div class="row mb-4">
                                                                        <div class="col-md-6">
                                                                            <p><strong>Full Name:</strong><br>
                                                                                {{ $enrollment->user->lastname }}, {{ $enrollment->user->firstname }} 
                                                                                {{ $enrollment->user->middlename ? $enrollment->user->middlename : 'N/A' }}
                                                                            </p>
                                                                            <p><strong>Email:</strong><br> {{ $enrollment->user->email }}</p>
                                                                            <p><strong>Contact Number:</strong><br> {{ $enrollment->user->contact_number ?? 'N/A' }}</p>
                                                                            <p><strong>Gender:</strong><br> {{ $enrollment->user->gender ?? 'N/A' }}</p>
                                                                            <p><strong>Birthdate:</strong><br> {{ isset($enrollment->user->birthdate) ? date('M d, Y', strtotime($enrollment->user->birthdate)) : 'N/A' }}</p>
                                                                            <p><strong>Civil Status:</strong><br> {{ $enrollment->user->civil_status ?? 'N/A' }}</p>
                                                                            <p><strong>Classification:</strong><br> {{ $enrollment->user->classification ?? 'N/A' }}</p>
                                                                            <p><strong>Nationality:</strong><br> {{ $enrollment->user->nationality ?? 'N/A' }}</p>
                                                                        </div>
                                            
                                                                        <!-- Complete Address -->
                                                                        <div class="col-md-6">
                                                                            <h6 class="text-muted mb-3">Complete Address</h6>
                                                                            <p><strong>Street Address:</strong><br> {{ $enrollment->user->street_address ?? 'N/A' }}</p>
                                                                            <p><strong>Barangay:</strong><br> {{ $enrollment->user->barangay ?? 'N/A' }}</p>
                                                                            <p><strong>Municipality/City:</strong><br> {{ $enrollment->user->municipality ?? 'N/A' }}</p>
                                                                            <p><strong>District:</strong><br> {{ $enrollment->user->district ?? 'N/A' }}</p>
                                                                            <p><strong>Province:</strong><br> {{ $enrollment->user->province ?? 'N/A' }}</p>
                                                                        </div>
                                                                    </div>
                                            
                                                                    <!-- Educational Background -->
                                                                    <div class="row mb-4">
                                                                        <div class="col-12">
                                                                            <h6 class="text-muted mb-3">Educational Background</h6>
                                                                            <p><strong>Highest Grade Completed:</strong><br> {{ $enrollment->user->highest_grade ?? 'N/A' }}</p>
                                                                            <p><strong>Course/Program:</strong><br> {{ $enrollment->user->course_program ?? 'N/A' }}</p>
                                                                        </div>
                                                                    </div>
                                            
                                                                    <!-- TVET Provider Profile -->
                                                                    <div class="row mb-4">
                                                                        <div class="col-12">
                                                                            <h6 class="fw-bold text-primary mb-3">TVET Provider Profile</h6>
                                                                            <div class="row">
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Region:</strong><br> {{ $enrollment->region }}</p>
                                                                                    <p><strong>Province:</strong><br> {{ $enrollment->province }}</p>
                                                                                    <p><strong>Congressional District:</strong><br> {{ $enrollment->congressional_district }}</p>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Municipality:</strong><br> {{ $enrollment->municipality }}</p>
                                                                                    <p><strong>Type of Provider:</strong><br> {{ $enrollment->provider_type }}</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                            
                                                                    <!-- Program Profile -->
                                                                    <div class="row">
                                                                        <div class="col-12">
                                                                            <h6 class="fw-bold text-primary mb-3">Program Profile</h6>
                                                                            <div class="row">
                                                                                <div class="col-md-6">
                                                                                    <p><strong>TVET Program Registration Status:</strong><br> {{ $enrollment->registration_status }}</p>
                                                                                    <p><strong>Delivery Mode:</strong><br> {{ $enrollment->delivery_mode }}</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-people display-1 text-muted"></i>
                                <p class="text-muted mt-3">No students enrolled in this batch yet.</p>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#enrollStudentModal">
                                    <i class="bi bi-plus-lg"></i> Enroll First Student
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="enrollStudentModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enroll New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.course.batches.enroll', ['course' => $course->id, 'batch' => $batch->id]) }}" method="POST" id="enrollmentForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <!-- Learner Profile Section -->
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3">Learner Profile</h6>
                                <div class="row g-3">
                                    <!-- Personal Information -->
                                    <div class="col-md-4">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="lastname" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="firstname" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" name="middlename">
                                    </div>
                    
                                    <!-- Account Credentials -->
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" name="contact_number" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" id="password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
                                    </div>
                    
                                    <!-- Complete Address -->
                                    <div class="col-12">
                                        <h6 class="text-muted mt-3 mb-2">Complete Address</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Street Number & Street Address</label>
                                        <input type="text" class="form-control" name="street_address" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Barangay</label>
                                        <input type="text" class="form-control" name="barangay" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Municipality/City</label>
                                        <input type="text" class="form-control" name="municipality" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">District</label>
                                        <input type="text" class="form-control" name="district" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Province</label>
                                        <input type="text" class="form-control" name="province" required>
                                    </div>
                    
                                    <!-- Personal Details -->
                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <select class="form-select" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Birthdate</label>
                                        <input type="date" class="form-control" name="birthdate" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Age</label>
                                        <input type="number" class="form-control" name="age" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Civil Status</label>
                                        <select class="form-select" name="civil_status" required>
                                            <option value="">Select Civil Status</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="Widowed">Widowed</option>
                                            <option value="Separated">Separated</option>
                                            <option value="Divorced">Divorced</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Classification</label>
                                        <input type="text" class="form-control" name="classification" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nationality</label>
                                        <input type="text" class="form-control" name="nationality" required>
                                    </div>
                    
                                    <!-- Educational Background -->
                                    <div class="col-12">
                                        <h6 class="text-muted mt-3 mb-2">Educational Background</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Highest Grade Completed</label>
                                        <select class="form-select" name="highest_grade" required>
                                            <option value="">Select Highest Education</option>
                                            <option value="Elementary Undergraduate">Elementary Undergraduate</option>
                                            <option value="Elementary Graduate">Elementary Graduate</option>
                                            <option value="High School Undergraduate">High School Undergraduate</option>
                                            <option value="High School Graduate">High School Graduate</option>
                                            <option value="Senior High School Undergraduate">Senior High School Undergraduate</option>
                                            <option value="Senior High School Graduate">Senior High School Graduate</option>
                                            <option value="College Undergraduate">College Undergraduate</option>
                                            <option value="College Graduate">College Graduate</option>
                                            <option value="Post Graduate">Post Graduate</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Course/Program (if applicable)</label>
                                        <input type="text" class="form-control" name="course_program" placeholder="Optional">
                                    </div>
                                </div>
                            </div>
                    
                            <!-- TVET Provider Profile Section -->
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold text-primary mb-3">TVET Provider Profile</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Region</label>
                                        <input type="text" class="form-control" name="region" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Province</label>
                                        <input type="text" class="form-control" name="provider_province" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Congressional District</label>
                                        <input type="text" class="form-control" name="congressional_district" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Municipality</label>
                                        <input type="text" class="form-control" name="provider_municipality" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Type of Provider</label>
                                        <input type="text" class="form-control" name="provider_type" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">School Name</label>
                                        <input type="text" class="form-control" name="school_name" value="{{ $school->name }}" readonly>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- Program Profile Section -->
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold text-primary mb-3">Program Profile</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Sector</label>
                                        <input type="text" class="form-control" name="sector" value="{{ $course->sector->name }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">TVET Program Registration Status</label>
                                        <input type="text" class="form-control" name="registration_status" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Qualification/Program Title</label>
                                        <input type="text" class="form-control" name="program_title" value="{{ $course->name }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Delivery Mode</label>
                                        <input type="text" class="form-control" name="delivery_mode" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Enroll Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('styles')
    <style>
        .table thead th {
            background-color: #f8f9fa;
            vertical-align: middle;
        }
        
        .border-end {
            border-right: 2px solid #dee2e6 !important;
        }
        
        .table tbody tr:hover {
            background-color: rgba(0,0,0,.075);
        }
        
        .badge {
            font-size: 0.875rem;
            padding: 0.5em 0.75em;
        }
        
        .minutes-late {
            font-weight: bold;
            color: #dc3545;
        }
    </style>
    @endpush

    <!-- View Attendance Modal -->
    <div class="modal fade" id="viewAttendanceModal" tabindex="-1" aria-labelledby="viewAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 70%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAttendanceModalLabel">Attendance Records</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Date Selection -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Select Date</label>
                            <select class="form-select" id="attendanceDateSelect">
                                <option value="">Choose a date...</option>
                                @foreach($attendanceDates ?? [] as $date)
                                    <option value="{{ $date }}">{{ date('F d, Y', strtotime($date)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Attendance Records Table -->
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th colspan="2" class="text-center border-end">Morning ({{ $schedules['morning']['in'] }} - {{ $schedules['morning']['out'] }})</th>
                                    <th colspan="2" class="text-center">Afternoon ({{ $schedules['afternoon']['in'] }} - {{ $schedules['afternoon']['out'] }})</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th class="text-center">Time In</th>
                                    <th class="text-center border-end">Time Out</th>
                                    <th class="text-center">Time In</th>
                                    <th class="text-center">Time Out</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Late Minutes</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceTableBody">
                                <!-- Data will be populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAttendanceModalLabel">Edit Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAttendanceForm">
                        <input type="hidden" id="editStudentId" name="student_id">
                        <input type="hidden" id="editDate" name="attendance_date">
                        
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2">Morning Session</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Time In</label>
                                    <input type="time" 
                                        class="form-control" 
                                        id="editMorningTimeIn" 
                                        name="morning_time_in">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Time Out</label>
                                    <input type="time" 
                                        class="form-control" 
                                        id="editMorningTimeOut" 
                                        name="morning_time_out">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2">Afternoon Session</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Time In</label>
                                    <input type="time" 
                                        class="form-control" 
                                        id="editAfternoonTimeIn" 
                                        name="afternoon_time_in">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Time Out</label>
                                    <input type="time" 
                                        class="form-control" 
                                        id="editAfternoonTimeOut" 
                                        name="afternoon_time_out">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="alert alert-info">
                                <small>
                                    <i class="bi bi-info-circle me-1"></i>
                                    Leave fields empty to mark as absent for that session.
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveAttendanceChanges">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
        
        
    <!-- Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 70%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceModalLabel">Attendance Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="attendanceForm" action="{{ route('admin.course.batches.attendance.store', ['course' => $course->id, 'batch' => $batch->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" 
                                    name="attendance_date" 
                                    class="form-control" 
                                    required 
                                    min="{{ $batch->start_date }}" 
                                    max="{{ min($batch->end_date, date('Y-m-d')) }}"
                                    value="{{ date('Y-m-d') }}"
                                    id="attendanceDate">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th colspan="2" class="text-center border-end">Morning ({{ $schedules['morning']['in'] }} - {{ $schedules['morning']['out'] }})</th>
                                        <th colspan="2" class="text-center">Afternoon ({{ $schedules['afternoon']['in'] }} - {{ $schedules['afternoon']['out'] }})</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th class="text-center">Time In</th>
                                        <th class="text-center border-end">Time Out</th>
                                        <th class="text-center">Time In</th>
                                        <th class="text-center">Time Out</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Late Minutes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($batch->enrollments->sortBy(function($enrollment) {
                                    return $enrollment->user->lastname . ', ' . $enrollment->user->firstname;
                                }) as $enrollment)
                                    <tr>
                                        <td class="align-middle">
                                            {{ $enrollment->user->lastname }}, {{ $enrollment->user->firstname }}
                                            @if($enrollment->user->middlename)
                                                {{ $enrollment->user->middlename[0] }}.
                                            @endif
                                        </td>
                                        <td>
                                            <input type="time" 
                                                class="form-control form-control-sm time-input"
                                                name="students[{{ $enrollment->user_id }}][morning_time_in]"
                                                data-student-id="{{ $enrollment->user_id }}">
                                        </td>
                                        <td class="border-end">
                                            <input type="time" 
                                                class="form-control form-control-sm"
                                                name="students[{{ $enrollment->user_id }}][morning_time_out]"
                                                readonly>
                                        </td>
                                        <td>
                                            <input type="time" 
                                                class="form-control form-control-sm time-input"
                                                name="students[{{ $enrollment->user_id }}][afternoon_time_in]"
                                                data-student-id="{{ $enrollment->user_id }}">
                                        </td>
                                        <td>
                                            <input type="time" 
                                                class="form-control form-control-sm"
                                                name="students[{{ $enrollment->user_id }}][afternoon_time_out]"
                                                readonly>
                                        </td>
                                        <td style="min-width: 180px;">
                                            <input type="text" 
                                                class="form-control form-control-sm status-display text-center"
                                                readonly
                                                value="ABSENT">
                                            <!-- Hidden status and late minutes fields -->
                                            <input type="hidden" 
                                                name="students[{{ $enrollment->user_id }}][morning_status]" 
                                                value="absent">
                                            <input type="hidden" 
                                                name="students[{{ $enrollment->user_id }}][afternoon_status]" 
                                                value="absent">
                                            <input type="hidden" 
                                                name="students[{{ $enrollment->user_id }}][morning_late_minutes]" 
                                                value="0">
                                            <input type="hidden" 
                                                name="students[{{ $enrollment->user_id }}][afternoon_late_minutes]" 
                                                value="0">
                                        </td>
                                        <td class="late-minutes-cell">
                                            <!-- Late minutes will be displayed here -->
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="attendanceForm" class="btn btn-primary">Save Attendance</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Modal Styles */
        #editAttendanceModal .modal-content {
            border-radius: 0.5rem;
        }
    
        #editAttendanceModal .modal-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
    
        /* Time Input Styles */
        .time-input {
            width: 130px;
            padding: 0.375rem 0.75rem;
        }
    
        input[type="time"]:read-only {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
    
        .time-input.is-invalid {
            border-color: #dc3545;
        }
    
        /* Session Headers */
        .text-muted.mb-2 {
            font-size: 0.9rem;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem !important;
        }
    
        /* Form Layout */
        .row.g-3 {
            margin-bottom: 1rem;
        }
    
        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d;
        }
    
        /* Validation Styles */
        .is-invalid {
            border-color: #dc3545;
        }
    
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
    
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
    @endpush
    
    @push('styles')
    <style>
        .section-header {
            background: linear-gradient(to right, #0d6efd, #0dcaf0);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        .info-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .info-card .card-header {
            background: white;
            border-bottom: 2px solid #e9ecef;
            padding: 15px 20px;
        }
        .info-card .card-body {
            padding: 20px;
        }
        .info-card p {
            margin-bottom: 0.8rem;
        }
        .info-card strong {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
    @endpush

    @push('styles')
    <style>
        /* Time input styling */
        .time-input {
            width: 100px;
        }
        
        /* Minutes late input styling */
        .minutes-late {
            width: 80px;
            background-color: #f8f9fa;
        }
        
        /* Table styling */
        .table thead th {
            text-align: center;
            vertical-align: middle;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .border-end {
            border-right: 2px solid #dee2e6;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .time-input, .minutes-late {
                width: 100%;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
    @endpush
    
    @push('styles')
    <style>
            /* Section Headers */
        .fw-bold.text-primary.mb-3 {
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 1.5rem !important;
        }

        /* Subsection Headers */
        .text-muted.mt-3.mb-2 {
            font-size: 0.9rem;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.5rem;
        }

        /* Section Spacing */
        .col-12.mt-4 {
            margin-top: 2rem !important;
        }

        /* Form Group Spacing */
        .row.g-3 {
            margin-bottom: 1rem;
        }

        /* Required Field Indicator */
        .form-label:not([for$="middlename"]):not([for="course_program"])::after {
            content: " *";
            color: #dc3545;
        }

        /* Readonly Fields */
        .form-control[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        /* Card Styling */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }

        /* Progress Bar */
        .progress {
            background-color: #e9ecef;
            border-radius: 0.5rem;
        }

        .progress-bar {
            background-color: #0d6efd;
            border-radius: 0.5rem;
        }

        /* Table Styling */
        .table thead th {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: none;
            background-color: #f8f9fa;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        /* Avatar Styling */
        .avatar-initial {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Badge Styling */
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
        }

        /* Button Group */
        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }

        .btn-group .btn i {
            font-size: 0.875rem;
        }

        /* Empty State */
        .text-center.py-5 i {
            opacity: 0.5;
        }

        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: 0;
        }

        .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: #0d6efd;
        }

        /* Modal Styling */
        .modal-xl {
            max-width: 1140px;
        }

        .nav-tabs .nav-link {
            color: #6c757d;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            font-weight: 500;
        }

        /* Form Styling */
        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d;
        }

        .form-control:disabled,
        .form-control[readonly] {
            background-color: #f8f9fa;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            .table {
                font-size: 0.875rem;
            }

            .avatar-initial {
                width: 32px;
                height: 32px;
            }

            .modal-xl {
                margin: 0.5rem;
            }
        }

            /* Add custom scrollbar styling */
    .tab-content::-webkit-scrollbar {
        width: 8px;
    }

    .tab-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .tab-content::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .tab-content::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Ensure form elements are properly spaced */
    .form-label {
        margin-bottom: 0.3rem;
    }

    .form-control, .form-select {
        margin-bottom: 0.5rem;
    }
    .table-sm input[type="time"] {
    width: 130px;
    padding: 2px 5px;
    }

    .table-sm select {
        width: 100px;
    }

    .time-input:invalid {
        border-color: #dc3545;
    }

    .time-input:disabled {
        background-color: #e9ecef;
    }
    </style>
    @endpush




@push('styles')
<style>
    /* Style for date input */
    input[type="date"] {
        position: relative;
    }

    /* Disable dates outside the allowed range */
    input[type="date"]:invalid {
        border-color: #dc3545;
    }

    /* Add some hover effect */
    input[type="date"]:hover {
        background-color: #f8f9fa;
    }

    /* Style when focused */
    input[type="date"]:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endpush



@push('styles')
<style>
    /* Add these to your existing styles */
    .text-success {
        color: #198754 !important;
        font-weight: 100;
    }
    
    .text-warning {
        color: #ffc107 !important;
        font-weight: 100;
    }
    
    .text-danger {
        color: #dc3545 !important;
        font-weight: 100;
    }
    
    .text-secondary {
        color: #6c757d !important;
        font-weight: 100;
    }
</style>
@endpush
@push('styles')
<style>
    /* Edit modal validation styles */
    .is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    .is-invalid ~ .invalid-feedback {
        display: block;
    }
</style>
@endpush
@push('styles')
<style>
    .is-invalid {
        border-color: #dc3545;
    }
    
    input[type="time"]:read-only {
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    .time-input.is-invalid {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
</style>
@endpush

@push('styles')
<style>
    /* ... your existing styles ... */

    .text-warning {
        color: #ffc107 !important;
    }

    input[type="time"].text-warning {
        color: #ffc107;
        font-weight: bold;
    }

    .late-minutes {
        font-weight: bold;
    }
</style>
@endpush

    
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize constants
        const attendanceDateSelect = document.getElementById('attendanceDateSelect');
        const attendanceTableBody = document.getElementById('attendanceTableBody');
        const courseId = document.querySelector('meta[name="course-id"]').content;
        const batchId = document.querySelector('meta[name="batch-id"]').content;
        const editModal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
    
        // Initialize schedules
        const schedules = {
            morning: {
                in: "{{ $schedules['morning']['in'] }}",
                out: "{{ $schedules['morning']['out'] }}"
            },
            afternoon: {
                in: "{{ $schedules['afternoon']['in'] }}",
                out: "{{ $schedules['afternoon']['out'] }}"
            }
        };
        // Helper functions for validation and error handling
        function validateTime(timeIn, session) {
            if (!timeIn) return { isValid: true, message: '' };
    
            const timeInHours = parseInt(timeIn.split(':')[0]);
            const timeInMinutes = parseInt(timeIn.split(':')[1]);
            const totalMinutes = (timeInHours * 60) + timeInMinutes;
    
            if (session === 'morning') {
                // Morning validation (before 12:00 PM)
                if (timeInHours >= 12) {
                    return {
                        isValid: false,
                        message: 'Morning time must be before 12:00 PM'
                    };
                }
                
                const [outHours, outMinutes] = schedules.morning.out.split(':');
                const scheduleOutMinutes = (parseInt(outHours) * 60) + parseInt(outMinutes);
                
                if (totalMinutes >= scheduleOutMinutes) {
                    return {
                        isValid: false,
                        message: 'Time in cannot be later than or equal to time out'
                    };
                }
            } else {
                // Afternoon validation (after or at 12:00 PM)
                if (timeInHours < 12) {
                    return {
                        isValid: false,
                        message: 'Afternoon time must be after 12:00 PM'
                    };
                }
                
                const [outHours, outMinutes] = schedules.afternoon.out.split(':');
                const scheduleOutMinutes = (parseInt(outHours) * 60) + parseInt(outMinutes);
                
                if (totalMinutes >= scheduleOutMinutes) {
                    return {
                        isValid: false,
                        message: 'Time in cannot be later than or equal to time out'
                    };
                }
            }
    
            return { isValid: true, message: '' };
        }
        
        // Function to create attendance row HTML
        function createAttendanceRow(record) {
            // Function to get status with text color
            function getStatusWithColor(status) {
                let colorClass;
                switch (status?.toLowerCase()) {
                    case 'present':
                        colorClass = 'text-success';
                        break;
                    case 'late':
                        colorClass = 'text-warning';
                        break;
                    case 'absent':
                        colorClass = 'text-danger';
                        break;
                    default:
                        colorClass = 'text-secondary';
                }
                return `<span class="${colorClass}">${status ? status.toUpperCase() : 'N/A'}</span>`;
            }
    
            return `
                <tr data-student-id="${record.student_id}">
                    <td class="align-middle">
                        ${record.student_name}
                    </td>
                    <td>
                        <input type="time" 
                            class="form-control form-control-sm time-input"
                            value="${record.morning_time_in || ''}"
                            readonly>
                    </td>
                    <td class="border-end">
                        <input type="time" 
                            class="form-control form-control-sm"
                            value="${record.morning_time_out || ''}"
                            readonly>
                    </td>
                    <td>
                        <input type="time" 
                            class="form-control form-control-sm time-input"
                            value="${record.afternoon_time_in || ''}"
                            readonly>
                    </td>
                    <td>
                        <input type="time" 
                            class="form-control form-control-sm"
                            value="${record.afternoon_time_out || ''}"
                            readonly>
                    </td>
                    <td class="text-center align-middle">
                        <div class="d-flex justify-content-center align-items-center">
                            <span class="small fw-bold">AM:</span> 
                            <span class="ms-1">${getStatusWithColor(record.morning_status)}</span>
                            <span class="mx-1">|</span>
                            <span class="small fw-bold">PM:</span> 
                            <span class="ms-1">${getStatusWithColor(record.afternoon_status)}</span>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <div class="d-flex justify-content-center align-items-center">
                            <span class="small fw-bold">AM:</span> 
                            <span class="ms-1">${record.morning_late_minutes || '0'}</span>
                            <span class="mx-1">|</span>
                            <span class="small fw-bold">PM:</span> 
                            <span class="ms-1">${record.afternoon_late_minutes || '0'}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <button type="button" 
                            class="btn btn-sm btn-primary edit-attendance" 
                            data-student-id="${record.student_id}"
                            data-student-name="${record.student_name}"
                            data-morning-in="${record.morning_time_in || ''}"
                            data-morning-out="${record.morning_time_out || ''}"
                            data-afternoon-in="${record.afternoon_time_in || ''}"
                            data-afternoon-out="${record.afternoon_time_out || ''}">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                    </td>
                </tr>
            `;
        }
    

        function showError(input, message) {
            removeError(input);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            input.parentNode.appendChild(errorDiv);
            input.classList.add('is-invalid');
        }
    
        function removeError(input) {
            const existingError = input.nextElementSibling;
            if (existingError && existingError.classList.contains('invalid-feedback')) {
                existingError.remove();
            }
            input.classList.remove('is-invalid');
        }
        // Edit button click handler
        document.addEventListener('click', function(e) {
            const editButton = e.target.closest('.edit-attendance');
            if (editButton) {
                e.preventDefault();
                
                // Get data from button attributes
                const studentId = editButton.dataset.studentId;
                const studentName = editButton.dataset.studentName;
                const morningIn = editButton.dataset.morningIn;
                const afternoonIn = editButton.dataset.afternoonIn;
                
                // Set form values
                document.getElementById('editStudentId').value = studentId;
                document.getElementById('editDate').value = attendanceDateSelect.value;
                
                // Get time input elements
                const morningTimeIn = document.getElementById('editMorningTimeIn');
                const morningTimeOut = document.getElementById('editMorningTimeOut');
                const afternoonTimeIn = document.getElementById('editAfternoonTimeIn');
                const afternoonTimeOut = document.getElementById('editAfternoonTimeOut');
                
                // Set initial values
                morningTimeIn.value = morningIn;
                afternoonTimeIn.value = afternoonIn;
    
                // Add event listeners for morning time in
                morningTimeIn.addEventListener('change', function() {
                    const validation = validateTime(this.value, 'morning');
                    if (validation.isValid) {
                        removeError(this);
                        morningTimeOut.value = schedules.morning.out;
                    } else {
                        showError(this, validation.message);
                        this.value = ''; // Clear invalid input
                        morningTimeOut.value = schedules.morning.out; // Keep scheduled time out
                    }
                });
    
                // Add event listeners for afternoon time in
                afternoonTimeIn.addEventListener('change', function() {
                    const validation = validateTime(this.value, 'afternoon');
                    if (validation.isValid) {
                        removeError(this);
                        afternoonTimeOut.value = schedules.afternoon.out;
                    } else {
                        showError(this, validation.message);
                        this.value = ''; // Clear invalid input
                        afternoonTimeOut.value = schedules.afternoon.out; // Keep scheduled time out
                    }
                });
    
                // Set initial time out values if time in exists and is valid
                if (morningIn) {
                    const validation = validateTime(morningIn, 'morning');
                    if (validation.isValid) {
                        morningTimeOut.value = schedules.morning.out;
                    }
                }
                if (afternoonIn) {
                    const validation = validateTime(afternoonIn, 'afternoon');
                    if (validation.isValid) {
                        afternoonTimeOut.value = schedules.afternoon.out;
                    }
                }
    
                // Make time out fields readonly
                morningTimeOut.readOnly = true;
                afternoonTimeOut.readOnly = true;
                
                // Update modal title
                document.getElementById('editAttendanceModalLabel').textContent = 
                    `Edit Attendance - ${studentName}`;
                
                editModal.show();
            }
        });
        // Handle form submission
        document.getElementById('saveAttendanceChanges').addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('editAttendanceForm');
            const formData = new FormData(form);
            const button = this;
    
            // Disable button and show loading state
            button.disabled = true;
            const originalText = button.innerHTML;
            button.innerHTML = `

                Save Changes
            `;
    
            // Send update request
            fetch(`/admin/course/${courseId}/batches/${batchId}/attendance/update`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Stop loading state immediately
                button.disabled = false;
                button.innerHTML = originalText;
    
                if (data.success) {
                    // Close modal immediately
                    editModal.hide();
                    
                    // Show success message
                    return Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Attendance record updated successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error(data.message || 'Failed to update attendance');
                }
            })
            .then(() => {
                // Refresh table after success message closes
                return refreshAttendanceTable();
            })
            .catch(error => {
                // Ensure button is restored on error
                button.disabled = false;
                button.innerHTML = originalText;
    
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Failed to update attendance record'
                });
            });
        });
        // Reset form when modal is hidden
        document.getElementById('editAttendanceModal').addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('editAttendanceForm');
            form.reset();
            
            // Remove any error messages
            const errorMessages = form.querySelectorAll('.invalid-feedback');
            errorMessages.forEach(error => error.remove());
            
            // Remove invalid classes from inputs
            const invalidInputs = form.querySelectorAll('.is-invalid');
            invalidInputs.forEach(input => input.classList.remove('is-invalid'));
    
            // Clear time out fields
            document.getElementById('editMorningTimeOut').value = '';
            document.getElementById('editAfternoonTimeOut').value = '';
        });
    
        // Prevent modal from closing when clicking outside
        document.getElementById('editAttendanceModal').addEventListener('show.bs.modal', function (event) {
            event.stopPropagation();
        });

        // Function to calculate late minutes and status
        function calculateAttendanceStatus(timeIn, scheduledTime) {
            if (!timeIn || !scheduledTime) return { status: 'absent', lateMinutes: 0 };
        
            const timeInDate = new Date(`2000-01-01T${timeIn}`);
            const scheduledDate = new Date(`2000-01-01T${scheduledTime}`);
            
            // Calculate difference in minutes
            const diffInMinutes = Math.floor((timeInDate - scheduledDate) / (1000 * 60));
            
            // If time in is before or equal to scheduled time
            if (diffInMinutes <= 0) {
                return { status: 'present', lateMinutes: 0 };
            }
            // If time in is more than 15 minutes late
            else if (diffInMinutes > 15) {
                return { status: 'late', lateMinutes: diffInMinutes };
            }
            // If time in is within 15 minutes grace period
            else {
                // Still present but show late minutes
                return { status: 'present', lateMinutes: diffInMinutes };
            }
        }
        
        // Modified createAttendanceRow function
        function createAttendanceRow(record) {
            // Function to get status with text color
            function getStatusWithColor(status) {
                let colorClass;
                switch (status?.toLowerCase()) {
                    case 'present':
                        colorClass = 'text-success';
                        break;
                    case 'late':
                        colorClass = 'text-warning';
                        break;
                    case 'absent':
                        colorClass = 'text-danger';
                        break;
                    default:
                        colorClass = 'text-secondary';
                }
                return `<span class="${colorClass}">${status ? status.toUpperCase() : 'N/A'}</span>`;
            }
        
            // Calculate morning status
            const morningAttendance = calculateAttendanceStatus(
                record.morning_time_in, 
                schedules.morning.in
            );
        
            // Calculate afternoon status
            const afternoonAttendance = calculateAttendanceStatus(
                record.afternoon_time_in, 
                schedules.afternoon.in
            );
        
            // Calculate total late minutes
            const totalLateMinutes = morningAttendance.lateMinutes + afternoonAttendance.lateMinutes;
        
            return `
                <tr data-student-id="${record.student_id}">
                    <td class="align-middle">
                        ${record.student_name}
                    </td>
                    <td>
                        <input type="time" 
                            class="form-control form-control-sm time-input ${morningAttendance.lateMinutes > 0 ? 'text-warning' : ''}"
                            value="${record.morning_time_in || ''}"
                            readonly>
                    </td>
                    <td class="border-end">
                        <input type="time" 
                            class="form-control form-control-sm"
                            value="${record.morning_time_out || ''}"
                            readonly>
                    </td>
                    <td>
                        <input type="time" 
                            class="form-control form-control-sm time-input ${afternoonAttendance.lateMinutes > 0 ? 'text-warning' : ''}"
                            value="${record.afternoon_time_in || ''}"
                            readonly>
                    </td>
                    <td>
                        <input type="time" 
                            class="form-control form-control-sm"
                            value="${record.afternoon_time_out || ''}"
                            readonly>
                    </td>
                    <td class="text-center align-middle">
                        <div class="d-flex justify-content-center align-items-center">
                            <span class="small fw-bold">AM:</span> 
                            <span class="ms-1">${getStatusWithColor(morningAttendance.status)}</span>
                            <span class="mx-1">|</span>
                            <span class="small fw-bold">PM:</span> 
                            <span class="ms-1">${getStatusWithColor(afternoonAttendance.status)}</span>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <div class="d-flex justify-content-center align-items-center">
                            <span class="small fw-bold">AM:</span> 
                            <span class="ms-1 ${morningAttendance.lateMinutes > 0 ? 'text-warning' : ''}">${morningAttendance.lateMinutes}</span>
                            <span class="mx-1">|</span>
                            <span class="small fw-bold">PM:</span> 
                            <span class="ms-1 ${afternoonAttendance.lateMinutes > 0 ? 'text-warning' : ''}">${afternoonAttendance.lateMinutes}</span>
                            <span class="mx-1">|</span>
                            <span class="small fw-bold">Total:</span> 
                            <span class="ms-1 ${totalLateMinutes > 0 ? 'text-warning fw-bold' : ''}">${totalLateMinutes}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <button type="button" 
                            class="btn btn-sm btn-primary edit-attendance" 
                            data-student-id="${record.student_id}"
                            data-student-name="${record.student_name}"
                            data-morning-in="${record.morning_time_in || ''}"
                            data-morning-out="${record.morning_time_out || ''}"
                            data-afternoon-in="${record.afternoon_time_in || ''}"
                            data-afternoon-out="${record.afternoon_time_out || ''}">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                    </td>
                </tr>
            `;
        }
        
        // Function to refresh attendance table
        function refreshAttendanceTable() {
            if (!attendanceDateSelect.value) return Promise.resolve();
    
            // Show loading state
            attendanceTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </td>
                </tr>
            `;
    
            return fetch(`/admin/course/${courseId}/batches/${batchId}/attendance/${attendanceDateSelect.value}/records`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (Array.isArray(data)) {
                    if (data.length === 0) {
                        attendanceTableBody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center">
                                    No attendance records found for this date.
                                </td>
                            </tr>
                        `;
                        return;
                    }
    
                    attendanceTableBody.innerHTML = data
                        .sort((a, b) => a.student_name.localeCompare(b.student_name))
                        .map(record => createAttendanceRow(record))
                        .join('');
                } else {
                    throw new Error('Invalid data format received');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                attendanceTableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-danger">
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Failed to load attendance records: ${error.message}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
    
        // Initialize with current date if available
        if (attendanceDateSelect.value) {
            refreshAttendanceTable();
        }
    
        // Add date change listener
        attendanceDateSelect.addEventListener('change', refreshAttendanceTable);
    });
    </script>
    @endpush
    
    @push('styles')
    <style>
        .is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
    
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
    
        input[type="time"]:read-only {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
    
        .time-input.is-invalid {
            border-color: #dc3545;
        }
    </style>
    @endpush
    

    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the date input element
            const dateInput = document.getElementById('attendanceDate');
            
            // Get batch training period
            const batchStartDate = new Date("{{ $batch->start_date }}");
            const batchEndDate = new Date("{{ $batch->end_date }}");
            
            // Get today's date and reset time to midnight for proper comparison
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Format dates for input min/max
            dateInput.min = formatDate(batchStartDate);
            dateInput.max = formatDate(batchEndDate);
            
            // Set default value to today if within range, otherwise set to start date
            if (today >= batchStartDate && today <= batchEndDate) {
                dateInput.value = formatDate(today);
            } else {
                dateInput.value = formatDate(batchStartDate);
            }
            
            // Add event listener to validate date selection
            dateInput.addEventListener('change', function(e) {
                const selectedDate = new Date(this.value);
                selectedDate.setHours(0, 0, 0, 0); // Reset time for proper comparison
                
                // Check if selected date is a weekend
                const isWeekend = selectedDate.getDay() === 0 || selectedDate.getDay() === 6;
                
                // Check if date is within range and not in future
                const isInRange = selectedDate >= batchStartDate && selectedDate <= batchEndDate;
                const isFutureDate = selectedDate > today;
                
                if (isFutureDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date',
                        text: 'Cannot select future dates for attendance.',
                    });
                } else if (!isInRange) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date',
                        text: 'Please select a date within the training period.',
                    });
                    // Reset to default value
                    if (today >= batchStartDate && today <= batchEndDate) {
                        this.value = formatDate(today);
                    } else {
                        this.value = formatDate(batchStartDate);
                    }
                } else if (isWeekend) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Weekend Selected',
                        text: 'The selected date falls on a weekend. Do you want to continue?',
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'No'
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            // Reset to default value
                            if (today >= batchStartDate && today <= batchEndDate) {
                                this.value = formatDate(today);
                            } else {
                                this.value = formatDate(batchStartDate);
                            }
                        }
                    });
                }
            });
            
            // Helper function to format date as YYYY-MM-DD
            function formatDate(date) {
                const d = new Date(date);
                let month = '' + (d.getMonth() + 1);
                let day = '' + d.getDate();
                const year = d.getFullYear();
                
                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;
                
                return [year, month, day].join('-');
            }
            
            // Add this to your existing attendance form submit handler
            const attendanceForm = document.getElementById('attendanceForm');
            attendanceForm.addEventListener('submit', function(e) {
                const selectedDate = new Date(dateInput.value);
                selectedDate.setHours(0, 0, 0, 0);
                const isInRange = selectedDate >= batchStartDate && selectedDate <= batchEndDate;
                const isFutureDate = selectedDate > today;
                
                if (isFutureDate) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date',
                        text: 'Cannot select future dates for attendance.',
                    });
                } else if (!isInRange) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date',
                        text: 'Please select a date within the training period.',
                    });
                }
            });
        });
        </script>
    @endpush
    
    




    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-calculate age based on birthdate
        const birthdateInput = document.querySelector('input[name="birthdate"]');
        const ageInput = document.querySelector('input[name="age"]');
        
        if (birthdateInput && ageInput) {
            birthdateInput.addEventListener('change', function() {
                const birthdate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthdate.getFullYear();
                const monthDiff = today.getMonth() - birthdate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }
                
                ageInput.value = age;
            });
        }
    
        // Form validation and submission
        const form = document.querySelector('form');
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirmation');
        const navTabs = document.querySelectorAll('.nav-tabs .nav-link');
    
        form.addEventListener('submit', function(e) {
            e.preventDefault();
    
            // Check all required fields
            const requiredFields = form.querySelectorAll('[required]');
            let emptyFields = [];
            let firstEmptyTab = null;
    
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    emptyFields.push(field);
                    
                    // Find which tab contains this empty field
                    const tabPane = field.closest('.tab-pane');
                    if (tabPane && !firstEmptyTab) {
                        firstEmptyTab = tabPane.id;
                    }
    
                    // Add visual feedback
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
    
            // Check password confirmation
            if (password.value !== passwordConfirm.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'The passwords you entered do not match. Please try again.',
                });
                return;
            }
    
            // If there are empty fields
            if (emptyFields.length > 0) {
                // Switch to the tab containing the first empty field
                if (firstEmptyTab) {
                    const targetTab = document.querySelector(`[href="#${firstEmptyTab}"]`);
                    const tab = new bootstrap.Tab(targetTab);
                    tab.show();
                }
    
                Swal.fire({
                    icon: 'error',
                    title: 'Incomplete Form',
                    text: 'Please fill in all required fields before submitting.',
                });
                return;
            }
    
            // If all validations pass, show loading state and submit
            Swal.fire({
                title: 'Enrolling Student...',
                text: 'Please wait',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
    
            // Submit the form
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Student enrolled successfully',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to enroll student');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Something went wrong while enrolling the student',
                    confirmButtonColor: '#3085d6'
                });
            });
        });
    
        // Add real-time validation feedback
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required')) {
                    if (!this.value.trim()) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                }
            });
        });
    });
    </script>
    @endpush

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-calculate age based on birthdate
        const birthdateInput = document.querySelector('input[name="birthdate"]');
        const ageInput = document.querySelector('input[name="age"]');
        
        if (birthdateInput && ageInput) {
            birthdateInput.addEventListener('change', function() {
                const birthdate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthdate.getFullYear();
                const monthDiff = today.getMonth() - birthdate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }
                
                ageInput.value = age;
            });
        }
        document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const token = document.querySelector('meta[name="csrf-token"]').content;
        
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('enrollStudentModal'));
                    modal.hide();
        
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Student enrolled successfully',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to enroll student');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Something went wrong while enrolling the student',
                    confirmButtonColor: '#3085d6'
                });
            });
        });

    });
    </script>
    @endpush
    
    @push('styles')
    <style>
        /* Styling for invalid fields */
        .is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
    
        .is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }
    
        /* Required field indicator */
        .form-label::after {
            content: " *";
            color: #dc3545;
        }
    
        .form-label:not([for$="middlename"])::after {
            content: " *";
            color: #dc3545;
        }
    </style>
    @endpush
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-calculate age based on birthdate
            const birthdateInput = document.querySelector('input[name="birthdate"]');
            const ageInput = document.querySelector('input[name="age"]');
            
            if (birthdateInput && ageInput) {
                birthdateInput.addEventListener('change', function() {
                    const birthdate = new Date(this.value);
                    const today = new Date();
                    let age = today.getFullYear() - birthdate.getFullYear();
                    const monthDiff = today.getMonth() - birthdate.getMonth();
                    
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                        age--;
                    }
                    
                    ageInput.value = age;
                });
            }

            // Convert middle name to initial
            const middleNameInput = document.querySelector('input[name="middlename"]');
            if (middleNameInput) {
                middleNameInput.addEventListener('blur', function() {
                    if (this.value) {
                        this.value = this.value.charAt(0).toUpperCase() + '.';
                    }
                });
            }

            // Initialize tooltips
            const tooltips = document.querySelectorAll('[title]');
            tooltips.forEach(tooltip => {
                new bootstrap.Tooltip(tooltip);
            });

            // Initialize DataTable
            if ($.fn.DataTable) {
                $('#enrollmentsTable').DataTable({
                    "pageLength": 8, // Default page length
                    "lengthMenu": [[8, 10, 15, 20], [8, 10, 15, 20]], // Pagination options
                    "ordering": true,
                    "info": true,
                    "responsive": true,
                    "language": {
                        "emptyTable": "No students enrolled",
                        "lengthMenu": "Show _MENU_ entries", // Customize the length menu text
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries", // Customize the info text
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        }
                    },
                    "dom": '<"top"lf>rt<"bottom"ip><"clear">', // Controls the layout
                    "stateSave": true, // Saves the state of the table (including pagination length)
                    "drawCallback": function(settings) {
                        // Optional: Add any custom styling after table draw
                        $('.dataTables_length select').addClass('form-select form-select-sm');
                        $('.dataTables_filter input').addClass('form-control form-control-sm');
                    }
                });
            }
        });
    </script>
    @endpush
    
    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const schedules = window.courseSchedules;
            
            // Make all status inputs readonly
            document.querySelectorAll('select[name$="[status]"]').forEach(select => {
                const statusInput = document.createElement('input');
                statusInput.type = 'text';
                statusInput.name = select.name;
                statusInput.className = 'form-control form-control-sm status-input';
                statusInput.readOnly = true;
                statusInput.value = 'ABSENT'; // Default value
                select.parentNode.replaceChild(statusInput, select);
            });
    
            // Make all time out inputs readonly
            document.querySelectorAll('input[name*="time_out"]').forEach(input => {
                input.readOnly = true;
                input.style.backgroundColor = '#e9ecef';
            });
    
            // Function to handle time in changes
            function handleTimeInChange(timeInInput, timeOutInput, session) {
                if (timeInInput.value) {
                    const validation = validateTimeInput(timeInInput.value, session);
                    
                    if (validation.isValid) {
                        // Always set the scheduled time out regardless of validation
                        timeOutInput.value = schedules[session].out;
                        
                        // Validate time in is not greater than time out
                        const timeInDate = new Date(`2000-01-01T${timeInInput.value}`);
                        const timeOutDate = new Date(`2000-01-01T${schedules[session].out}`);
                        
                        if (timeInDate >= timeOutDate) {
                            timeInInput.classList.add('is-invalid');
                            showError(timeInInput, 'Time in cannot be greater than or equal to time out');
                            // Don't return here - keep the time out value displayed
                        } else {
                            timeInInput.classList.remove('is-invalid');
                            removeError(timeInInput);
                        }
                    } else {
                        timeInInput.classList.add('is-invalid');
                        timeOutInput.value = schedules[session].out; // Always show scheduled time out
                        showError(timeInInput, validation.message);
                    }
                } else {
                    timeOutInput.value = ''; // Only clear time out if time in is empty
                    timeInInput.classList.remove('is-invalid');
                    removeError(timeInInput);
                }
            }
    
            // Add event listeners for time in fields
            document.querySelectorAll('input[name*="morning_time_in"]').forEach(input => {
                input.addEventListener('change', function() {
                    handleTimeInChange(this, 'morning');
                });
            });
    
            document.querySelectorAll('input[name*="afternoon_time_in"]').forEach(input => {
                input.addEventListener('change', function() {
                    handleTimeInChange(this, 'afternoon');
                });
            });
    
            const form = document.getElementById('attendanceForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Attendance has been recorded successfully',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('attendanceModal'));
                            modal.hide();
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Something went wrong!'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong!'
                    });
                });
            });
    
            function updateStatus(row) {
                const studentId = row.querySelector('.time-input').dataset.studentId;
                const displayInput = row.querySelector('.status-display');
                const morningStatusInput = row.querySelector(`input[name="students[${studentId}][morning_status]"]`);
                const afternoonStatusInput = row.querySelector(`input[name="students[${studentId}][afternoon_status]"]`);
                const morningTimeIn = row.querySelector(`input[name="students[${studentId}][morning_time_in]"]`);
                const afternoonTimeIn = row.querySelector(`input[name="students[${studentId}][afternoon_time_in]"]`);
            
                // Add hidden inputs for late minutes if they don't exist
                let morningLateInput = row.querySelector(`input[name="students[${studentId}][morning_late_minutes]"]`);
                let afternoonLateInput = row.querySelector(`input[name="students[${studentId}][afternoon_late_minutes]"]`);
                
                if (!morningLateInput) {
                    morningLateInput = document.createElement('input');
                    morningLateInput.type = 'hidden';
                    morningLateInput.name = `students[${studentId}][morning_late_minutes]`;
                    row.appendChild(morningLateInput);
                }
                
                if (!afternoonLateInput) {
                    afternoonLateInput = document.createElement('input');
                    afternoonLateInput.type = 'hidden';
                    afternoonLateInput.name = `students[${studentId}][afternoon_late_minutes]`;
                    row.appendChild(afternoonLateInput);
                }
            
                clearLateMinutes(row);
            
                let morningStatus = 'ABSENT';
                let afternoonStatus = 'ABSENT';
                let morningLateMinutes = 0;
                let afternoonLateMinutes = 0;
            
                if (morningTimeIn.value) {
                    morningLateMinutes = calculateLateness(morningTimeIn.value, schedules.morning.in);
                    morningStatus = morningLateMinutes > 15 ? 'LATE' : 'PRESENT';
                    morningStatusInput.value = morningLateMinutes > 15 ? 'late' : 'present';
                    morningLateInput.value = morningLateMinutes;
                }
            
                if (afternoonTimeIn.value) {
                    afternoonLateMinutes = calculateLateness(afternoonTimeIn.value, schedules.afternoon.in);
                    afternoonStatus = afternoonLateMinutes > 15 ? 'LATE' : 'PRESENT';
                    afternoonStatusInput.value = afternoonLateMinutes > 15 ? 'late' : 'present';
                    afternoonLateInput.value = afternoonLateMinutes;
                }
            
                // Update display and show late minutes
                const displayStatus = `AM: ${morningStatus} | PM: ${afternoonStatus}`;
                displayInput.value = displayStatus;
            
                if (morningStatus === 'LATE' || afternoonStatus === 'LATE') {
                    displayInput.className = 'form-control form-control-sm status-display text-danger';
                } else if (morningStatus === 'PRESENT' || afternoonStatus === 'PRESENT') {
                    displayInput.className = 'form-control form-control-sm status-display text-success';
                } else {
                    displayInput.className = 'form-control form-control-sm status-display text-secondary';
                }
            
                // Show late minutes if any
                if (morningLateMinutes > 0) {
                    showLateMinutes(row, morningLateMinutes, 'morning', morningStatus === 'LATE');
                }
                if (afternoonLateMinutes > 0) {
                    showLateMinutes(row, afternoonLateMinutes, 'afternoon', afternoonStatus === 'LATE');
                }
            }
                            function isValidTimeFormat(time) {
                    const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
                    return timeRegex.test(time);
                }

                function isTimeInRange(time, session) {
                    if (!time || !isValidTimeFormat(time)) return false;
                    
                    const [hours] = time.split(':').map(Number);
                    
                    if (session === 'morning') {
                        // Morning session: 12:00 AM to 11:59 AM
                        return hours < 12;
                    } else if (session === 'afternoon') {
                        // Afternoon session: 12:00 PM to 11:59 PM
                        return hours >= 12;
                    }
                    return false;
                }

                function convertTo24Hour(timeStr) {
                    const [hours, minutes] = timeStr.split(':').map(Number);
                    return (hours * 60) + minutes;
                }

                // Modified handleTimeInChange function
                function handleTimeInChange(timeInInput, timeOutInput, session) {
                    if (timeInInput.value) {
                        const validation = validateTimeInput(timeInInput.value, session);
                        
                        if (validation.isValid) {
                            const timeOut = calculateTimeOut(timeInInput.value, session);
                            
                            // Validate time in is not greater than time out
                            const timeInDate = new Date(`2000-01-01T${timeInInput.value}`);
                            const timeOutDate = new Date(`2000-01-01T${timeOut}`);
                            
                            if (timeInDate >= timeOutDate) {
                                timeInInput.classList.add('is-invalid');
                                timeOutInput.value = '';
                                showError(timeInInput, 'Time in cannot be greater than or equal to time out');
                                return;
                            }
                            
                            timeOutInput.value = timeOut;
                            timeInInput.classList.remove('is-invalid');
                            removeError(timeInInput);
                        } else {
                            timeInInput.classList.add('is-invalid');
                            timeOutInput.value = '';
                            showError(timeInInput, validation.message);
                        }
                    } else {
                        timeOutInput.value = '';
                        timeInInput.classList.remove('is-invalid');
                        removeError(timeInInput);
                    }
                }
            
    
            function calculateLateness(actualTime, scheduledTime) {
                if (!actualTime || !scheduledTime) return 0;
                
                const [actualHours, actualMinutes] = actualTime.split(':').map(Number);
                const [schedHours, schedMinutes] = scheduledTime.split(':').map(Number);
                
                const actualTotalMinutes = (actualHours * 60) + actualMinutes;
                const schedTotalMinutes = (schedHours * 60) + schedMinutes;
                
                return Math.max(0, actualTotalMinutes - schedTotalMinutes);
            }
    
            function showLateMinutes(row, minutes, session, isLate) {
                let lateDisplayContainer = row.querySelector('.late-minutes-cell');
                let lateDisplay = lateDisplayContainer.querySelector(`.late-minutes-${session}`);
                
                if (!lateDisplay) {
                    lateDisplay = document.createElement('small');
                    lateDisplay.className = `late-minutes-${session} late-minutes ms-2 d-block`;
                    lateDisplayContainer.appendChild(lateDisplay);
                }
    
                if (isLate) {
                    lateDisplay.className = `late-minutes-${session} late-minutes ms-2 d-block text-danger`;
                    lateDisplay.textContent = `${session}: ${minutes} min late (Marked Late)`;
                } else {
                    lateDisplay.className = `late-minutes-${session} late-minutes ms-2 d-block text-warning`;
                    lateDisplay.textContent = `${session}: ${minutes} min late (Within Grace Period)`;
                }
            }
    
            function clearLateMinutes(row) {
                const lateDisplayCell = row.querySelector('.late-minutes-cell');
                lateDisplayCell.innerHTML = '';
            }
    
            // Initialize existing rows
            document.querySelectorAll('tr').forEach(row => {
                if (row.querySelector('input[name*="time_in"]')) {
                    updateStatus(row);
                }
            });
    
            // Debug: Log the schedules object
            console.log('Course Schedules:', schedules);
        });
        </script>
        @endpush
    
    
    @push('styles')
        <style>
            .text-warning {
                color: #ffc107 !important;
            }
            .text-danger {
                color: #dc3545 !important;
            }
            .text-success {
                color: #198754 !important;
            }
            .text-secondary {
                color: #6c757d !important;
            }
            .late-minutes {
                font-size: 0.875rem;
                font-weight: 500;
            }
            .late-minutes-cell {
                min-width: 200px;
            }
            .d-block {
                display: block;
            }
            .status-input {
                font-weight: 500 !important;
                text-align: center !important;
                background-color: #f8f9fa !important;
            }
            input[readonly] {
                cursor: default;
            }
        </style>
    @endpush
    
</x-adminlayout>
