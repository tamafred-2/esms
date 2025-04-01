<x-adminlayout :icon="$icon" :button="$button">
    <div class="container">
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Success!',
                        text: "{{ session('success') }}", // Changed to double quotes
                        icon: 'success',
                        timer: 5000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                });
            </script>
        @endif
        <div class="row justify-content-center">
            <!-- Events Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Upcoming Events</h5>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                            <i class="bi bi-plus-circle"></i> Add Event
                        </button>
                    </div>
                    <div class="card-body">
                        @forelse($events as $event)
                            <div class="event-item mb-3 border-bottom pb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $event->title }}</h6>
                                        <div class="text-muted small">
                                            <i class="bi bi-calendar"></i> 
                                            {{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}
                                            <i class="bi bi-clock ms-2"></i>
                                            {{ \Carbon\Carbon::parse($event->time)->format('h:i A') }}
                                        </div>
                                        @if($event->description)
                                            <p class="small text-muted mb-0 mt-1">{{ $event->description }}</p>
                                        @endif
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" 
                                            class="btn btn-outline-primary btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editEvent{{ $event->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.events.destroy', $event->id) }}" 
                                            method="POST"
                                            class="d-inline m-0 delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger delete-btn">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-event display-4 text-muted"></i>
                                <p class="text-muted mt-2">No upcoming events</p>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#addEventModal">
                                    <i class="bi bi-plus-circle"></i> Add Event
                                </button>
                            </div>
                        @endforelse
                        <!-- Events pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $events->appends(['school-page' => request()->get('school-page')])->links() }}
                        </div>
                    </div>
                </div>

                <!-- Add Event Modal -->
                <div class="modal fade" id="addEventModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Event</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="addEventForm" action="{{ route('admin.events.store') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Event Title</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description (Optional)</label>
                                        <textarea name="description" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" name="date" class="form-control" 
                                                min="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Time</label>
                                            <input type="time" name="time" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Event</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Edit Event Modal -->
                @foreach($events as $event)
                    <div class="modal fade" id="editEvent{{ $event->id }}" tabindex="-1" aria-labelledby="editEventLabel{{ $event->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editEventLabel{{ $event->id }}">Edit Event</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="editEventForm{{ $event->id }}" 
                                    data-event-id="{{ $event->id }}"
                                    method="POST" 
                                    action="{{ route('admin.events.update', $event->id) }}" 
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <!-- Event Title -->
                                        <div class="mb-3">
                                            <label for="title{{ $event->id }}" class="form-label">Event Title</label>
                                            <input type="text" class="form-control" id="title{{ $event->id }}" 
                                                   name="title" value="{{ $event->title }}" required>
                                        </div>
                
                                        <!-- Description -->
                                        <div class="mb-3">
                                            <label for="description{{ $event->id }}" class="form-label">Description</label>
                                            <textarea class="form-control" id="description{{ $event->id }}" 
                                                    name="description" rows="3">{{ $event->description }}</textarea>
                                        </div>
                
                                        <!-- Date and Time -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="date{{ $event->id }}" class="form-label">Date</label>
                                                <input type="date" class="form-control" id="date{{ $event->id }}" 
                                                       name="date" value="{{ $event->date }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="time{{ $event->id }}" class="form-label">Time</label>
                                                <input type="time" class="form-control" id="time{{ $event->id }}" 
                                                       name="time" value="{{ $event->time }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update Event</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
            
            <!-- Chart Section -->
            <div class="col-md-6">
                <div class="card statistics-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pie-chart-fill me-2"></i>
                                Student Performance Statistics
                            </h5>
                            <div class="chart-controls">
                                <select id="chartType" class="form-select form-select-sm">
                                    <option value="pie">Pie Chart</option>
                                    <option value="doughnut">Doughnut Chart</option>
                                    <option value="bar">Bar Chart</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="filters mb-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <select id="yearFilter" class="form-select">
                                        <option value="all">All Years</option>
                                        <option value="2024">2025</option>
                                        <option value="2023">2024</option>
                                        <option value="2022">2023</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select id="courseFilter" class="form-select">
                                        <option value="all">All Courses</option>
                                        <option value="math"> NC II Bread and Pastry Production</option>
                                        <option value="science">NC II Food and Beverages Services</option>
                                        <option value="english">NC III Events Management services</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="chart-wrapper">
                            <div class="chart-container" style="position: relative; height: 280px; width: 100%; margin:0 auto;">
                                <canvas id="studentPieChart"></canvas>
                            </div>
                            <div class="chart-legend mt-4" style="text-align: center;">
                                <div class="legend-item" role="button" onclick="toggleDataset(0)">
                                    <span class="legend-dot competent"></span>
                                    <span class="legend-label">Competent Students</span>
                                    <span class="legend-value">45 (60%)</span>
                                </div>
                                <div class="legend-item" role="button" onclick="toggleDataset(1)">
                                    <span class="legend-dot incompetent"></span>
                                    <span class="legend-label">Needs Improvement</span>
                                    <span class="legend-value">30 (40%)</span>
                                </div>
                            </div>
                            <div class="statistics-summary mt-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="total-container">
                                                <span class="total-label">Total Students</span>
                                                <span class="total-value">75</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="average-container">
                                                <span class="average-label">Average Score</span>
                                                <span class="average-value">78%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="trend-container">
                                                <span class="trend-label">Trend</span>
                                                <span class="trend-value"><i class="bi bi-arrow-up-circle-fill text-success"></i> +5%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>
    <hr>
    
    <!-- Schools Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4" style="padding: 2%;">
                <h5 class="section-title">Schools</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#schoolModal">
                    <i class="bi bi-plus-circle me-2"></i>Add School
                </button>
            </div>
    
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3" style="padding: 2%;">
                @forelse($schools as $school)
                    <div class="col">
                        <div class="card school-card h-100 shadow-sm">
                            <div class="logo-container">
                            @if($school->logo_path)
                                <img src="{{ asset('storage/' . $school->logo_path) }}" 
                                    alt="{{ $school->name }}" 
                                    class="school-logo">
                            @endif
                            </div>
    
                            <div class="card-body p-3">
                                <h6 class="school-name text-center mb-2">{{ $school->name }}</h6>
                                <div class="school-info">
                                    <div class="info-item">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span class="text-truncate">
                                            @php
                                                $address_parts = array_filter([
                                                    $school->street_number,
                                                    $school->barangay,
                                                    $school->city,
                                                    $school->province
                                                ]);
                                                $full_address = !empty($address_parts) ? implode(', ', $address_parts) : 'No address provided';
                                            @endphp
                                            {{ $full_address }}
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <i class="bi bi-telephone-fill"></i>
                                        <span>{{ $school->contact_number ?? 'No contact number' }}</span>
                                    </div>
                                </div>
                            </div>
    
                            <div class="card-footer bg-transparent p-2">
                                <div class="btn-group w-100">
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editSchool{{ $school->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="{{ route('admin.school.show', $school->id) }}" 
                                       class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.school.destroy', $school->id) }}" 
                                          method="POST" 
                                          class="d-inline m-0"
                                          onsubmit="return confirm('Are you sure you want to delete this school?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                            class="btn btn-outline-danger btn-sm delete-school" 
                                            data-school-id="{{ $school->id }}" 
                                            data-school-name="{{ $school->name }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="editSchool{{ $school->id }}" tabindex="-1" aria-labelledby="editSchoolLabel{{ $school->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editSchoolLabel{{ $school->id }}">Edit School</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="editSchoolForm{{ $school->id }}" data-school-id="{{ $school->id }}"method="POST" action="{{ route('admin.school.update', $school->id) }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <!-- School Name -->
                                        <div class="mb-3">
                                            <label for="name{{ $school->id }}" class="form-label">School Name</label>
                                            <input type="text" class="form-control" id="name{{ $school->id }}" name="name" 
                                                value="{{ $school->name }}" required>
                                        </div>

                                        <!-- Address Section -->
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                School Address
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="street_number{{ $school->id }}" class="form-label">Street Number / Street Address</label>
                                                        <input type="text" class="form-control" id="street_number{{ $school->id }}" 
                                                            name="street_number" value="{{ $school->street_number }}" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="barangay{{ $school->id }}" class="form-label">Barangay</label>
                                                        <input type="text" class="form-control" id="barangay{{ $school->id }}" 
                                                            name="barangay" value="{{ $school->barangay }}" required>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="city{{ $school->id }}" class="form-label">Municipal/City</label>
                                                        <input type="text" class="form-control" id="city{{ $school->id }}" 
                                                            name="city" value="{{ $school->city }}" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="province{{ $school->id }}" class="form-label">Province</label>
                                                        <input type="text" class="form-control" id="province{{ $school->id }}" 
                                                            name="province" value="{{ $school->province }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact Number -->
                                        <div class="mb-3">
                                            <label for="contact_number{{ $school->id }}" class="form-label">School Contact Number</label>
                                            <input type="text" class="form-control" id="contact_number{{ $school->id }}" 
                                                name="contact_number" value="{{ $school->contact_number }}"
                                                pattern="[0-9]+" title="Please enter only numbers" required>
                                        </div>

                                        <!-- Logo -->
                                        <div class="mb-3">
                                            <label for="logo_path{{ $school->id }}" class="form-label">School Logo</label>
                                            <input type="file" class="form-control" id="logo_path{{ $school->id }}" 
                                                name="logo_path" accept="image/*">
                                            <small class="text-muted">Optional: Upload new school logo (PNG, JPG, JPEG)</small>
                                            @if($school->logo_path)
                                                <div class="mt-2">
                                                    <small class="text-muted">Current logo:</small>
                                                    <img src="{{ asset('storage/' . $school->logo_path) }}" 
                                                        alt="Current Logo" class="img-thumbnail" style="height: 50px">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update School</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle me-2"></i>
                            No schools found. 
                            <button type="button" class="btn btn-link p-0 m-0 align-baseline" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#schoolModal">
                                Add a new school
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>
    
            <!-- Schools pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $schools->appends(['event-page' => request()->get('event-page')])->links() }}
            </div>
        </div>
    </div>
    
    <!-- School Details Modal -->
    <div class="modal fade" id="schoolDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">School Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="" alt="School Logo" class="img-fluid mb-3" id="modalSchoolLogo">
                        </div>
                        <div class="col-md-8">
                            <h4 id="modalSchoolName"></h4>
                            <p><i class="bi bi-geo-alt"></i> <span id="modalSchoolLocation"></span></p>
                            <div class="school-details">
                                <h2>School Details</h2>
                                <a href="{{ route('admin.dashboard') }}" class="school-image-button">
                                    <img src="{{ asset('path/to/your/school-image.jpg') }}" alt="School Image">
                                    <span>Go to User Dashboard</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">View Details</button>
                </div>
            </div>
        </div>
    </div>

    <!-- School Modal -->
    <div class="modal fade" id="schoolModal" tabindex="-1" aria-labelledby="schoolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="schoolModalLabel">Add New School</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="schoolForm" method="POST" action="{{ route('admin.school.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- School Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">School Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
    
                        <!-- Address Section -->
                        <div class="card mb-3">
                            <div class="card-header">
                                School Address
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="street_number" class="form-label">Street Number / Street Address</label>
                                        <input type="text" class="form-control" id="street_number" name="street_number" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="barangay" class="form-label">Barangay</label>
                                        <input type="text" class="form-control" id="barangay" name="barangay" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">Municipal/City</label>
                                        <input type="text" class="form-control" id="city" name="city" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="province" class="form-label">Province</label>
                                        <input type="text" class="form-control" id="province" name="province" required>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <!-- Contact Number -->
                        <div class="mb-3">
                            <label for="contact_number" class="form-label">School Contact Number</label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" 
                                   pattern="[0-9]+" title="Please enter only numbers" required>
                        </div>
    
                        <!-- Logo -->
                        <div class="mb-3">
                            <label for="logo_path" class="form-label">School Logo</label>
                            <input type="file" class="form-control" id="logo_path" name="logo_path" 
                                   accept="image/*">
                            <small class="text-muted">Optional: Upload school logo (PNG, JPG, JPEG)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    
    <style>
    .school-card {
        transition: transform 0.2s;
    }
    
    .school-card:hover {
        transform: translateY(-5px);
    }
    
    .logo-container {
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    
    .school-logo {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }
    
    .default-logo {
        font-size: 3rem;
        color: #6c757d;
    }
    
    .school-info {
        font-size: 0.9rem;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .info-item i {
        min-width: 16px;
    }
    
    .info-item span {
        flex: 1;
    }
    
    .btn-group form {
        flex: 1;
    }
    
    .btn-group .btn {
        border-radius: 0;
    }
    
    .btn-group .btn:first-child {
        border-top-left-radius: 0.25rem;
        border-bottom-left-radius: 0.25rem;
    }
    
    .btn-group .btn:last-child {
        border-top-right-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
    }
    </style>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const handleEventEdit = () => {
            const editForms = document.querySelectorAll('form[id^="editEventForm"]');
            
            editForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const eventId = this.dataset.eventId;
                    const submitButton = this.querySelector('button[type="submit"]');
                    
                    formData.append('_method', 'PUT');
                    
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
    
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Hide modal
                            const modal = bootstrap.Modal.getInstance(document.querySelector(`#editEvent${eventId}`));
                            modal.hide();
    
                            // Show success message and refresh page
                            Swal.fire({
                                title: 'Success!',
                                text: data.message || 'Event updated successfully!',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Something went wrong');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Failed to update event',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Update Event';
                    });
                });
            });
        };
    
        // Initialize edit functionality
        handleEventEdit();
    });
    </script>
    @endpush
    
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const handleSchoolEdit = () => {
            const editForms = document.querySelectorAll('form[id^="editSchoolForm"]');
            
            editForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const schoolId = this.dataset.schoolId;
                    const submitButton = this.querySelector('button[type="submit"]');
                    
                    formData.append('_method', 'PUT');
                    
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';

                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Hide modal
                            const modal = bootstrap.Modal.getInstance(document.querySelector(`#editSchool${schoolId}`));
                            modal.hide();

                            // Show success message and refresh page
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Refresh the page after the success message
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Something went wrong');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Failed to update school',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Update School';
                    });
                });
            });
        };

        // Initialize edit functionality
        handleSchoolEdit();

        // Image preview function
        const handleImagePreview = () => {
            const imageInputs = document.querySelectorAll('input[type="file"][id^="logo_path"]');
            
            imageInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        const previewContainer = this.closest('.mb-3').querySelector('.preview-container') || this.parentElement.querySelector('.mt-2');
                        
                        reader.onload = function(e) {
                            const previewHtml = `
                                <div class="mt-2">
                                    <small class="text-muted">New logo preview:</small>
                                    <img src="${e.target.result}" 
                                        alt="Logo Preview" 
                                        class="img-thumbnail" 
                                        style="height: 50px">
                                </div>`;
                            
                            if (previewContainer) {
                                previewContainer.innerHTML = previewHtml;
                            } else {
                                input.insertAdjacentHTML('afterend', previewHtml);
                            }
                        };
                        
                        reader.readAsDataURL(file);
                    }
                });
            });
        };

        // Initialize image preview
        handleImagePreview();
    });
    </script>
    @endpush


    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const handleSchoolEdit = () => {
            const editForms = document.querySelectorAll('form[id^="editSchoolForm"]');
            
            editForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const schoolId = this.dataset.schoolId;
                    const submitButton = this.querySelector('button[type="submit"]');
                    
                    formData.append('_method', 'PUT');
                    
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
    
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Hide modal
                            const modal = bootstrap.Modal.getInstance(document.querySelector(`#editSchool${schoolId}`));
                            modal.hide();
    
                            // Show success message and refresh page
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Refresh the page after the success message
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Something went wrong');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Failed to update school',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Update School';
                    });
                });
            });
        };
    
        // Initialize edit functionality
        handleSchoolEdit();
    
        // Image preview function
        const handleImagePreview = () => {
            const imageInputs = document.querySelectorAll('input[type="file"][id^="logo_path"]');
            
            imageInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        const previewContainer = this.closest('.mb-3').querySelector('.preview-container') || this.parentElement.querySelector('.mt-2');
                        
                        reader.onload = function(e) {
                            const previewHtml = `
                                <div class="mt-2">
                                    <small class="text-muted">New logo preview:</small>
                                    <img src="${e.target.result}" 
                                         alt="Logo Preview" 
                                         class="img-thumbnail" 
                                         style="height: 50px">
                                </div>`;
                            
                            if (previewContainer) {
                                previewContainer.innerHTML = previewHtml;
                            } else {
                                input.insertAdjacentHTML('afterend', previewHtml);
                            }
                        };
                        
                        reader.readAsDataURL(file);
                    }
                });
            });
        };
    
        // Initialize image preview
        handleImagePreview();
    });
    </script>
    @endpush
    
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    
                    Swal.fire({
                        title: 'Delete Event?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        focusCancel: true,
                        customClass: {
                            confirmButton: 'btn btn-danger',
                            cancelButton: 'btn btn-secondary me-3'
                        },
                        buttonsStyling: false,
                        padding: '2em'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Disable any default form submission behavior
                            form.addEventListener('submit', (e) => {
                                e.preventDefault();
                            }, { once: true });
                            
                            // Submit form using fetch
                            fetch(form.action, {
                                method: 'POST',
                                body: new FormData(form),
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            }).then(() => {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Event has been deleted.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Reload the page after the success message
                                    window.location.reload();
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const schoolForm = document.getElementById('schoolForm');
        const schoolModal = document.getElementById('schoolModal');
    
        if (schoolForm) {
            schoolForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                
                // Disable button and show loading state
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
                        // Hide modal
                        const modal = bootstrap.Modal.getInstance(schoolModal);
                        modal.hide();
                        
                        // Show success message
                        Swal.fire({
                            title: 'Success!',
                            text: data.message || 'School added successfully!',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload page
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Something went wrong');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    let errorMessage = 'Something went wrong. Please try again.';
                    
                    if (error.errors) {
                        // Handle validation errors
                        errorMessage = Object.values(error.errors).flat().join('\n');
                    } else if (error.message) {
                        errorMessage = error.message;
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error'
                    });
                })
                .finally(() => {
                    // Re-enable button and restore original text
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Save School';
                });
            });
    
            // Reset form when modal is hidden
            schoolModal.addEventListener('hidden.bs.modal', function () {
                schoolForm.reset();
                // Remove any validation messages
                schoolForm.querySelectorAll('.is-invalid').forEach(element => {
                    element.classList.remove('is-invalid');
                });
                schoolForm.querySelectorAll('.invalid-feedback').forEach(element => {
                    element.remove();
                });
            });
    
            // Contact number validation
            const contactInput = document.getElementById('contact_number');
            if (contactInput) {
                contactInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
        }
    });
    </script>
    
    @endpush
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Event Form Submission
        const addEventForm = document.getElementById('addEventForm');
        const addEventModal = document.getElementById('addEventModal');
        
        if (addEventForm) {
            addEventForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                
                // Disable button and show loading state
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide modal
                        const modal = bootstrap.Modal.getInstance(addEventModal);
                        modal.hide();
                        
                        // Show success message
                        Swal.fire({
                            title: 'Success!',
                            text: data.message || 'Event created successfully!',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload only the events section or the entire page
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Something went wrong');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Something went wrong. Please try again.',
                        icon: 'error'
                    });
                })
                .finally(() => {
                    // Re-enable button and restore original text
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Save Event';
                });
            });
    
            // Reset form when modal is hidden
            addEventModal.addEventListener('hidden.bs.modal', function () {
                addEventForm.reset();
                // Remove any validation messages
                addEventForm.querySelectorAll('.is-invalid').forEach(element => {
                    element.classList.remove('is-invalid');
                });
                addEventForm.querySelectorAll('.invalid-feedback').forEach(element => {
                    element.remove();
                });
            });
        }
    });

        document.addEventListener('DOMContentLoaded', function() {
        // Delete school handler
            document.querySelectorAll('.delete-school').forEach(button => {
            button.addEventListener('click', function() {
                const schoolId = this.getAttribute('data-school-id');
                const schoolName = this.getAttribute('data-school-name');

                Swal.fire({
                    title: 'Are you sure?',
                    html: `You are about to delete <strong>${schoolName}</strong><br>This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            willOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Send DELETE request
                        fetch(`/admin/school/${schoolId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'School has been deleted successfully.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Reload the page
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(data.message || 'Failed to delete school');
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error!',
                                text: error.message || 'Failed to delete school',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                    }
                });
            });
        });
    });
    </script>
    @endpush
    
</x-adminlayout>
