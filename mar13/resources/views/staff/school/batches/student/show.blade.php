<x-stafflayout>
    <div class="container-fluid px-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">{{ $course->name ?? 'Course Name' }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Courses</a></li>
                        <li class="breadcrumb-item active">Batch Details</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary">
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
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>Batch Code:</strong> {{ $batch->batch_code ?? 'N/A' }}</li>
                            <li class="mb-2"><strong>Start Date:</strong> {{ $batch->start_date ? $batch->start_date->format('M d, Y') : 'N/A' }}</li>
                            <li class="mb-2"><strong>End Date:</strong> {{ $batch->end_date ? $batch->end_date->format('M d, Y') : 'N/A' }}</li>
                            <li class="mb-2"><strong>Status:</strong> 
                                <span class="badge bg-{{ $batch->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($batch->status ?? 'N/A') }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
    
                <!-- School Information Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">School Information</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>School Name:</strong> {{ $batch->school->name ?? 'N/A' }}</li>
                            <li class="mb-2"><strong>Region:</strong> {{ $batch->region ?? 'N/A' }}</li>
                            <li class="mb-2"><strong>Province:</strong> {{ $batch->province ?? 'N/A' }}</li>
                            <li class="mb-2"><strong>Municipality:</strong> {{ $batch->municipality ?? 'N/A' }}</li>
                        </ul>
                    </div>
                </div>
            </div>
    
            <!-- Right Column - Enrolled Students -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Enrolled Students</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                <i class="bi bi-plus-lg"></i> Add Student
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Registration Status</th>
                                        <th>Delivery Mode</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batchEnrollments as $enrollment)
                                        <tr>
                                            <td>{{ $enrollment->user->name ?? 'N/A' }}</td>
                                            <td>{{ $enrollment->user->email ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $enrollment->registration_status === 'registered' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($enrollment->registration_status ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td>{{ ucfirst($enrollment->delivery_mode ?? 'N/A') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editStudentModal{{ $enrollment->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteStudentModal{{ $enrollment->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No students enrolled yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- Add your form fields here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .table th {
            font-weight: 600;
        }
        .badge {
            font-weight: 500;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Add any JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any plugins or event listeners
        });
    </script>
    @endpush
</x-stafflayout>
