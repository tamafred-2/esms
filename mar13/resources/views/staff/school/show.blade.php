<x-stafflayout>
    <div class="container mt-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            @if($school->logo_path)
                                <img src="{{ asset('storage/' . $school->logo_path) }}" 
                                     alt="School Logo" 
                                     class="rounded-circle me-3"
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3"
                                     style="width: 60px; height: 60px;">
                                    <i class="bi bi-building text-danger" style="font-size: 1.5rem;"></i>
                                </div>
                            @endif
                            <div>
                                <h4 class="mb-0" style="color: var(--primary-color);">{{ $school->name }}</h4>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-geo-alt"></i>
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
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-telephone"></i>
                                    {{ $school->contact_number ?? 'No contact number' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="section-title">
                    <i class="bi bi-diagram-3 me-2"></i>Industry Sector of Qualification
                </h5>
            </div>
        </div>
        <!-- Sectors and Courses -->
        <div class="row">
            @if($sectors->where('school_id', $school->id)->count() > 0)
                @foreach($sectors->where('school_id', $school->id) as $sector)
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-white">{{ $sector->name }}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($sector->courses->where('school_id', $school->id)->count() > 0)
                                    <div class="row">
                                        @foreach($sector->courses->where('school_id', $school->id) as $course)
                                        <div class="col-md-4 mb-3">
                                            <a href="{{ route('staff.school.batches.show', ['school' => $school->id, 'course' => $course->id]) }}" 
                                            class="text-decoration-none">
                                                <div class="card h-100 border-0 shadow-sm hover-shadow">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <h5 class="card-title text-dark mb-3">{{ $course->name }}</h5>
                                                        </div>
                                                        <p class="card-text text-muted small mb-3">
                                                            {{ Str::limit($course->description, 100) }}
                                                        </p>
                                                        <div class="d-flex align-items-center text-muted small">
                                                            <span class="me-3">
                                                                <i class="bi bi-clock me-1"></i>
                                                                {{ $course->duration_days }} {{ Str::plural('day', $course->duration_days) }}
                                                            </span>
                                                        </div>
                                                        
                                                        <!-- Schedule Information -->
                                                        @if($course->morning_in || $course->afternoon_in)
                                                            <div class="mt-3 small">
                                                                @if($course->morning_in && $course->morning_out)
                                                                    <p class="mb-1">
                                                                        <strong>Morning:</strong> 
                                                                        {{ $course->morning_in }} - {{ $course->morning_out }}
                                                                    </p>
                                                                @endif
                                                                @if($course->afternoon_in && $course->afternoon_out)
                                                                    <p class="mb-1">
                                                                        <strong>Afternoon:</strong> 
                                                                        {{ $course->afternoon_in }} - {{ $course->afternoon_out }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="bi bi-journal-x text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">No courses available in this sector.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-diagram-3 display-4 text-muted"></i>
                        <p class="mt-2 text-muted">No courses available for this school.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
        
    <style>
    .hover-shadow {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
    }

    .card-header {
        border-bottom: 0;
    }

    a:hover {
        text-decoration: none;
    }

</style>
</x-stafflayout>
