<x-stafflayout>
    <div class="container py-4">
        <!-- Course Information Header -->
        <div class="mb-4">
            <h4 class="mb-3">{{ $course->name }} - Batches</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('staff.school.show', $school) }}">Back to Courses</a></li>
                    <li class="breadcrumb-item active">{{ $course->name }}</li>
                </ol>
            </nav>
        </div>

        <!-- Batches List -->
        @if($batches->count() > 0)
            <div class="row g-4">
                @foreach($batches as $batch)
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm batch-card">
                            <div class="card-body">
                                <!-- Batch Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
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
                                </div>

                                <!-- Schedule Information -->
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-calendar-event text-primary me-2"></i>
                                        <small>
                                            Start: <strong>{{ $batch->start_date->format('M d, Y') }}</strong>
                                        </small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-calendar-check text-primary me-2"></i>
                                        <small>
                                            End: <strong>{{ $batch->end_date->format('M d, Y') }}</strong>
                                        </small>
                                    </div>
                                    @if($batch->schedule)
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-clock text-primary me-2"></i>
                                            <small>
                                                Schedule: <strong>{{ $batch->schedule }}</strong>
                                            </small>
                                        </div>
                                    @endif
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
                            <a href="{{ route('staff.school.batches.student.show', ['school' => $school, 'course' => $course, 'batch' => $batch]) }}" 
                               class="stretched-link batch-link"></a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No batches have been created for this course yet.
            </div>
        @endif
    </div>
</x-stafflayout>
