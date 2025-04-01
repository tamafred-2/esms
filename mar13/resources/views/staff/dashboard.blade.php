<x-stafflayout>
    <div class="container mt-4">
        <h1>Staff Dashboard</h1>
        
        <!-- Profile Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Staff Profile</h5>
                <div class="profile-info">
                    <p class="mb-2"><strong>Name:</strong> {{ $user->name }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $user->email }}</p>
                </div>
            </div>
        </div>

        <!-- Schools Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4" style="padding: 2%;">
                    <h5 class="section-title">Assigned School</h5>
                </div>

                <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3" style="padding: 2%;">
                    @forelse($user->schools as $school)
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
                                        <a href="{{ url('/staff/school/' . $school->id) }}" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="bi bi-info-circle me-2"></i>
                                No school assignments found.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

<style>
    .school-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .school-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .logo-container {
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background-color: #f8f9fa;
    }

    .school-logo {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
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

    .school-name {
        font-weight: 600;
        color: #2c3e50;
    }
</style>
</x-stafflayout>
