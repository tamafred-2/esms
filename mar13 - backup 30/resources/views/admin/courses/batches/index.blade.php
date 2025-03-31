<x-adminlayout>
    <div class="container">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">{{ $course->name }} - Batches</h4>
                <p class="text-muted mb-0">
                    <i class="bi bi-people me-1"></i>Manage course batches and enrollments
                </p>
            </div>
            <button type="button" class="btn btn-primary d-flex align-items-center" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addBatchModal">
                <i class="bi bi-plus-lg me-2"></i>Add New Batch
            </button>
        </div>

        @if($batches->count() > 0)
            <div class="row g-4">
                @foreach($batches as $batch)
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm batch-card">
                            <div class="card-body">
                                <!-- Batch Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3 batch-item" data-course-id="{{ $course->id }}">
                                    <div>
                                         <h5 class="card-title mb-1">{{ $batch->batch_name }}</h5>
                                         @php
                                             $now = \Carbon\Carbon::now();
                                             $status = 'pending';
                                             $statusClass = 'bg-warning';
                                             $statusIcon = 'bi-clock';
                                             
                                             if ($now->between($batch->start_date, $batch->end_date)) {
                                                 $status = 'ongoing';
                                                 $statusClass = 'bg-success';
                                                 $statusIcon = 'bi-play-fill';
                                             } elseif ($now->isAfter($batch->end_date)) {
                                                 $status = 'completed';
                                                 $statusClass = 'bg-secondary';
                                                 $statusIcon = 'bi-check2-all';
                                             }
                                         @endphp
                                         <span class="badge {{ $statusClass }} rounded-pill">
                                             <i class="bi {{ $statusIcon }} me-1"></i>
                                             {{ ucfirst($status) }}
                                         </span>
                                    </div>
                                    <div class="dropdown batch-actions" onclick="event.stopPropagation();">
                                        <button class="btn btn-link text-dark p-0 dropdown-toggle" 
                                                type="button" 
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if($batch->enrollments()->count() == 0)
                                                <li>
                                                    <button class="dropdown-item text-danger delete-batch" 
                                                            type="button"
                                                            data-batch-id="{{ $batch->id }}"
                                                            data-batch-name="{{ $batch->batch_name }}"
                                                            data-course-id="{{ $course->id }}">
                                                        <i class="bi bi-trash me-2"></i>Delete Batch
                                                    </button>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>

                                <!-- Dates Section -->
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-calendar-event text-primary me-2"></i>
                                        <small>
                                            Start: <strong>{{ $batch->start_date->format('M d, Y') }}</strong>
                                        </small>
                                    </div>
                                </div>

                                <!-- Enrollment Progress -->
                                @php
                                    $enrolledCount = $batch->enrollments()->count();
                                    $percentage = ($enrolledCount / $batch->max_students) * 100;
                                    $progressClass = $percentage >= 90 ? 'bg-danger' : 
                                                   ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <div class="enrollment-stats">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">Enrollment Progress</small>
                                        <small class="fw-bold">{{ $enrolledCount }}/{{ $batch->max_students }}</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $progressClass }}"
                                             role="progressbar"
                                             style="width: {{ $percentage }}%"
                                             aria-valuenow="{{ $enrolledCount }}"
                                             aria-valuemin="0"
                                             aria-valuemax="{{ $batch->max_students }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('admin.course.batches.show', ['course' => $course, 'batch' => $batch]) }}" 
                               class="stretched-link batch-link"></a>
                        </div>
                    </div>

                    <!-- Edit Batch Modal -->
                    <div class="modal fade" id="editBatchModal{{ $batch->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Batch</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="editBatchForm{{ $batch->id }}" method="POST" 
                                    action="{{ route('admin.course.batches.update', ['course' => $course->id, 'batch' => $batch->id]) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="batch_name" class="form-label">Batch Name</label>
                                            <input type="text" 
                                                class="form-control" 
                                                name="batch_name" 
                                                value="{{ $batch->batch_name }}"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="date" 
                                                class="form-control" 
                                                name="start_date" 
                                                value="{{ $batch->start_date->format('Y-m-d') }}"
                                                required>
                                            <div class="form-text text-muted">
                                                Course Duration: {{ $course->duration_days }} days
                                                <br>
                                                End Date will be automatically calculated based on the course duration.
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="max_students" class="form-label">Maximum Students</label>
                                            <input type="number" 
                                                class="form-control" 
                                                name="max_students" 
                                                value="{{ $batch->max_students }}"
                                                min="{{ $batch->enrollments()->count() }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update Batch</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $batches->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                <p class="text-muted">No batches available for this course.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBatchModal">
                    Create First Batch
                </button>
            </div>
        @endif
    </div>
    
    <!-- Add Batch Modal -->
    <div class="modal fade" id="addBatchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addBatchForm" action="{{ route('admin.course.batches.store', $course->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="batch_name" class="form-label">Batch Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="batch_name" 
                                   name="batch_name" 
                                   required>
                        </div>
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="start_date" 
                                   name="start_date" 
                                   required>
                        </div>
                        <div class="mb-3">
                            <label for="max_students" class="form-label">Maximum Students</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="max_students" 
                                   name="max_students" 
                                   min="1" 
                                   required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Batch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <style>
    .batch-card {
        position: relative;
    }

    .batch-actions {
        position: relative;
        z-index: 2;
    }

    .batch-card .dropdown-menu {
        z-index: 1050;
    }

    .batch-card .dropdown-toggle::after {
        display: none;
    }

    .batch-card .stretched-link {
        z-index: 1;
    }

    .dropdown-item {
        cursor: pointer;
    }

    .dropdown-menu {
        min-width: 120px;
    }
    </style>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize dropdowns
        const dropdownTriggerList = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        const dropdownList = [...dropdownTriggerList].map(dropdownTriggerEl => {
            return new bootstrap.Dropdown(dropdownTriggerEl);
        });
        // Handle delete batch
        document.querySelectorAll('.delete-batch').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
        
                const batchId = this.getAttribute('data-batch-id');
                const batchName = this.getAttribute('data-batch-name');
                const courseId = this.getAttribute('data-course-id');
        
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete batch "${batchName}". This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
        
                        // Get CSRF token
                        const token = document.querySelector('meta[name="csrf-token"]').content;
        
                        // Send delete request
                        fetch(`/admin/course/${courseId}/batches/${batchId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => Promise.reject(err));
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: data.message || 'Batch deleted successfully',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(data.message || 'Failed to delete batch');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: error.message || 'Something went wrong while deleting the batch',
                                confirmButtonColor: '#3085d6'
                            });
                        });
                    }
                });
            });
        });
    });
    </script>
    @endpush
    
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('addBatchForm');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            Swal.fire({
                title: 'Creating...',
                text: 'Please wait',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
    
            // Get form data
            const formData = new FormData(this);
    
            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]').content;
    
            // Send request
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
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addBatchModal'));
                    modal.hide();
    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Batch created successfully',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to create batch');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Something went wrong while creating the batch',
                    confirmButtonColor: '#3085d6'
                });
            });
        });
    });
    </script>
    @endpush
    
</x-adminlayout>
