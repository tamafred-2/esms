<x-adminlayout :icon="$icon" :button="$button">
    <div class="container-fluid">
        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form class="row g-3" method="GET" action="{{ route('admin.users') }}" id="searchForm">
                    <div class="col-md-4">
                        <select class="form-select" name="usertype" id="userTypeFilter">
                            <option value="">All User Types</option>
                            <option value="admin" {{ (isset($selected_usertype) && $selected_usertype == 'admin') ? 'selected' : '' }}>Admin</option>
                            <option value="staff" {{ (isset($selected_usertype) && $selected_usertype == 'staff') ? 'selected' : '' }}>Staff</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   placeholder="Search by name, email or contact..."
                                   value="{{ $search_term ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-grid gap-2">
                            @if($search_term || $selected_usertype)
                                <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Active Filters -->
        @if($search_term || $selected_usertype)
            <div class="alert alert-info mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Active Filters:</strong>
                        @if($search_term)
                            <span class="badge bg-primary ms-2">Search: {{ $search_term }}</span>
                        @endif
                        @if($selected_usertype)
                            <span class="badge bg-primary ms-2">Type: {{ ucfirst($selected_usertype) }}</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear All Filters
                    </a>
                </div>
            </div>
        @endif

        <!-- Results Summary -->
        <div class="mb-3">
            <small class="text-muted">
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
            </small>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('admin.users', array_merge(request()->query(), [
                                        'sort' => 'id',
                                        'direction' => ($sort_field === 'id' && $sort_direction === 'asc') ? 'desc' : 'asc'
                                    ])) }}" class="text-decoration-none text-dark">
                                        ID
                                        @if($sort_field === 'id')
                                            <i class="bi bi-arrow-{{ $sort_direction === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Address</th>
                                <th>User Type</th>
                                <th>
                                    <a href="{{ route('admin.users', array_merge(request()->query(), [
                                        'sort' => 'created_at',
                                        'direction' => ($sort_field === 'created_at' && $sort_direction === 'asc') ? 'desc' : 'asc'
                                    ])) }}" class="text-decoration-none text-dark">
                                        Created At
                                        @if($sort_field === 'created_at')
                                            <i class="bi bi-arrow-{{ $sort_direction === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->lastname }}, {{ $user->firstname }} {{ $user->middlename }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->contact_number }}</td>
                                    <td>
                                        {{ $user->street_number }}, {{ $user->barangay }}, 
                                        {{ $user->city }}, {{ $user->province }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $user->usertype === 'admin' ? 'danger' : 'primary' }}">
                                            {{ ucfirst($user->usertype) }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y h:ia') }}</td>
                                    <td>
                                        <!-- Your existing action buttons -->
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6"></i>
                                            <p class="mt-2">No users found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mb-3">
                        <small class="text-muted">
                            Sorted by: {{ ucfirst(str_replace('_', ' ', $sort_field)) }} 
                            ({{ $sort_direction === 'asc' ? 'Ascending' : 'Descending' }})
                        </small>
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl"> <!-- Changed to modal-xl for larger width -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="card">
                            <div class="card-body">
                                <form action="{{ route('admin.register-user') }}" method="POST" id="createUserForm">
                                    @csrf
                                    
                                    <!-- User Type Selection -->
                                    <div class="form-group mb-3">
                                        <label for="usertype" class="form-label">User Type</label>
                                        <select class="form-select @error('usertype') is-invalid @enderror" 
                                                id="usertype" 
                                                name="usertype" 
                                                required>
                                            <option value="" selected disabled>Select User Type</option>
                                            <option value="admin" {{ old('usertype') == 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="staff" {{ old('usertype') == 'staff' ? 'selected' : '' }}>Staff</option>
                                        </select>
                                        @error('usertype')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
    
                                    <!-- Name Fields -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="lastname" class="form-label">Last Name</label>
                                                <input type="text" 
                                                       class="form-control @error('lastname') is-invalid @enderror"
                                                       id="lastname" 
                                                       name="lastname" 
                                                       value="{{ old('lastname') }}" 
                                                       required>
                                                @error('lastname')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="firstname" class="form-label">First Name</label>
                                                <input type="text" 
                                                       class="form-control @error('firstname') is-invalid @enderror"
                                                       id="firstname" 
                                                       name="firstname" 
                                                       value="{{ old('firstname') }}" 
                                                       required>
                                                @error('firstname')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="middlename" class="form-label">Middle Name/Initial</label>
                                                <input type="text" 
                                                       class="form-control @error('middlename') is-invalid @enderror"
                                                       id="middlename" 
                                                       name="middlename" 
                                                       value="{{ old('middlename') }}">
                                                @error('middlename')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
    
                                    <!-- Contact Number -->
                                    <div class="form-group mb-3">
                                        <label for="contact_number" class="form-label">Contact Number</label>
                                        <input type="tel" 
                                               class="form-control @error('contact_number') is-invalid @enderror"
                                               id="contact_number" 
                                               name="contact_number" 
                                               value="{{ old('contact_number') }}" 
                                               required>
                                        @error('contact_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
    
                                    <!-- Address Fields -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="street_number" class="form-label">Street Number</label>
                                                <input type="text" 
                                                       class="form-control @error('street_number') is-invalid @enderror"
                                                       id="street_number" 
                                                       name="street_number" 
                                                       value="{{ old('street_number') }}" 
                                                       required>
                                                @error('street_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="barangay" class="form-label">Barangay</label>
                                                <input type="text" 
                                                       class="form-control @error('barangay') is-invalid @enderror"
                                                       id="barangay" 
                                                       name="barangay" 
                                                       value="{{ old('barangay') }}" 
                                                       required>
                                                @error('barangay')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="city" class="form-label">Municipality/City</label>
                                                <input type="text" 
                                                       class="form-control @error('city') is-invalid @enderror"
                                                       id="city" 
                                                       name="city" 
                                                       value="{{ old('city') }}" 
                                                       required>
                                                @error('city')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="province" class="form-label">Province</label>
                                                <input type="text" 
                                                       class="form-control @error('province') is-invalid @enderror"
                                                       id="province" 
                                                       name="province" 
                                                       value="{{ old('province') }}" 
                                                       required>
                                                @error('province')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
    
                                    <!-- Email -->
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror"
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email') }}" 
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
    
                                    <!-- Password -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password" 
                                                       class="form-control @error('password') is-invalid @enderror"
                                                       id="password" 
                                                       name="password" 
                                                       required>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                                <input type="password" 
                                                       class="form-control"
                                                       id="password_confirmation" 
                                                       name="password_confirmation" 
                                                       required>
                                            </div>
                                        </div>
                                    </div>
    
                                    <!-- Submit Button -->
                                    <div class="mt-3 text-end">
                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="bi bi-person-plus-fill me-2"></i>Create User
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        // Handle Create User Form Submission
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
    
            // Submit form using fetch
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'User created successfully',
                        icon: 'success',
                        confirmButtonColor: '#0d6efd'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Reload the page to show new user
                            window.location.reload();
                        }
                    });
    
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createUserModal'));
                    modal.hide();
                } else {
                    // Show error message
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Something went wrong',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Something went wrong',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        });
    
        // Reset form when modal is closed
        document.getElementById('createUserModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('createUserForm').reset();
        });
    </script>
    @endpush
    

    <!-- Keep your existing modals here -->

    @push('scripts')
    <script>
        // Instant search and filter functionality
        const searchForm = document.getElementById('searchForm');
        const userTypeFilter = document.getElementById('userTypeFilter');
        let searchTimeout;

        // Handle search input with debounce
        document.querySelector('input[name="search"]').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchForm.submit();
            }, 500); // Wait 500ms after user stops typing
        });

        // Handle usertype filter change
        userTypeFilter.addEventListener('change', function() {
            searchForm.submit();
        });

        // SweetAlert2 delete confirmation
        document.querySelectorAll('.delete-user-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });

        
    </script>
    @endpush
</x-adminlayout>
