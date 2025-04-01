<x-adminlayout>
<style>
.batch-card {
    transition: transform 0.2s;
    border: none;
    background: #fff;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1rem;
    padding: 1.5rem;
    border-radius: 8px;
}

.batch-card:hover {
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

.status-badge {
    padding: 0.35em 0.65em;
    border-radius: 0.25rem;
    font-size: 0.875em;
    font-weight: 600;
}

/* Using your dashboard colors */
.status-upcoming { 
    background-color: #e3f2fd; 
    color: #0d47a1; 
}
.status-ongoing { 
    background-color:rgb(241, 231, 229); 
    color:rgb(199, 50, 39); 
}
.status-completed { 
    background-color: #ede7f6; 
    color: #4527a0; 
}

.date-progress {
    height: 8px;
    background-color: #e9ecef;
    border-radius: 1rem;
    overflow: hidden;
    margin-top: 0.5rem;
}

.date-progress-bar {
    height: 100%;
    border-radius: 1rem;
    transition: width 0.6s ease;
    background: linear-gradient(to right,rgb(226, 35, 35),rgb(247, 149, 68));
}

.info-label {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
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

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Daily Time Management</h4>
    </div>

    <div class="row">
        @forelse($batches as $batch)
            @php
                try {
                    $startDate = $batch->start_date ? \Carbon\Carbon::parse($batch->start_date) : null;
                    $endDate = $batch->end_date ? \Carbon\Carbon::parse($batch->end_date) : null;
                    $today = \Carbon\Carbon::now();
                    
                    if ($startDate && $endDate) {
                        $totalDays = $startDate->diffInDays($endDate);
                        $daysElapsed = $startDate->diffInDays($today);
                        $progress = $totalDays > 0 ? min(($daysElapsed / $totalDays) * 100, 100) : 0;
                    } else {
                        $progress = 0;
                    }

                    // Calculate status based on dates
                    if (!$startDate || !$endDate) {
                        $status = 'upcoming';
                    } elseif ($today < $startDate) {
                        $status = 'upcoming';
                    } elseif ($today > $endDate) {
                        $status = 'completed';
                    } else {
                        $status = 'ongoing';
                    }
                } catch (\Exception $e) {
                    $progress = 0;
                    $status = 'upcoming';
                }
            @endphp

            <div class="col-12">
                <div class="batch-card">
                    <div class="row">
                        <!-- School Name -->
                        <div class="col-md-3 mb-3">
                            <div class="info-label">School</div>
                            <div class="info-value">{{ $batch->school_name ?? 'N/A' }}</div>
                        </div>
                        
                        <!-- Course Name -->
                        <div class="col-md-3 mb-3">
                            <div class="info-label">Course</div>
                            <div class="info-value">{{ $batch->course_name }}</div>
                        </div>
                        
                        <!-- Batch Name -->
                        <div class="col-md-3 mb-3">
                            <div class="info-label">Batch</div>
                            <div class="info-value">{{ $batch->batch_name }}</div>
                        </div>
                        
                        <!-- Status -->
                        <div class="col-md-3 mb-3">
                            <div class="info-label">Status</div>
                            <span class="status-badge status-{{ $status }}">
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span class="info-label">
                                    {{ $batch->start_date ? date('M d, Y', strtotime($batch->start_date)) : 'Start Date Not Set' }}
                                </span>
                                <span class="info-label">
                                    {{ $batch->end_date ? date('M d, Y', strtotime($batch->end_date)) : 'End Date Not Set' }}
                                </span>
                            </div>
                            <div class="date-progress">
                                <div class="date-progress-bar" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                    <p class="text-muted">No batches found</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $batches->links() }}
    </div>
</div>
</x-adminlayout>
