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
                                        @if($user->street_address || $user->barangay || $user->municipality || $user->province)
                                            {{ $user->street_address }}
                                            @if($user->barangay), {{ $user->barangay }}@endif
                                            @if($user->municipality), {{ $user->municipality }}@endif
                                            @if($user->province), {{ $user->province }}@endif
                                        @else
                                            <span class="text-muted">No address provided</span>
                                        @endif
                                    </td>
                                    <td style="text-align:left">
                                        <span class="badge bg-{{ $user->usertype === 'admin' ? 'danger' : 'warning' }}" >
                                            {{ ucfirst($user->usertype) }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y h:ia') }}</td>
                                    <td>

                                        <button type="button" 
                                            class="btn btn-outline-danger btn-sm edit-user-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUserModal"
                                            data-user-id="{{ $user->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" 
                                            class="btn btn-outline-warning btn-sm view-user-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewUserModal"
                                            data-user-id="{{ $user->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- <form method="POST" 
                                            action="{{ route('admin.deleteuser', $user->id) }}" 
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-outline-danger btn-sm delete-btn"
                                                    data-name="{{ $user->firstname }} {{ $user->lastname }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form> -->
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
    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="street_address" class="form-label">Street Address</label>
                                                <input type="text" 
                                                    class="form-control @error('street_address') is-invalid @enderror"
                                                    id="street_address" 
                                                    name="street_address"
                                                    value="{{ old('street_address') }}" 
                                                    required>
                                                @error('street_address')
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
                                                <label for="municipality" class="form-label">Municipality/City</label>
                                                <input type="text" 
                                                       class="form-control @error('municipality') is-invalid @enderror"
                                                       id="municipality" 
                                                       name="municipality"
                                                       value="{{ old('municipality') }}" 
                                                       required>
                                                @error('municipality')
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



    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-3 fw-bold">Name:</div>
                                            <div class="col-md-9" id="view-name"></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3 fw-bold">Email:</div>
                                            <div class="col-md-9" id="view-email"></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3 fw-bold">Contact Number:</div>
                                            <div class="col-md-9" id="view-contact"></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3 fw-bold">Address:</div>
                                            <div class="col-md-9" id="view-address"></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3 fw-bold">User Type:</div>
                                            <div class="col-md-9">
                                                <span class="badge bg-primary" id="view-usertype"></span>
                                            </div>
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
    
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- User Type Selection -->
                        <div class="form-group mb-3">
                            <label for="edit-usertype" class="form-label">User Type</label>
                            <select class="form-select" id="edit-usertype" name="usertype" required>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
    
                        <!-- Name Fields -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit-lastname" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="edit-lastname" name="lastname" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit-firstname" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="edit-firstname" name="firstname" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit-middlename" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="edit-middlename" name="middlename">
                                </div>
                            </div>
                        </div>
    
                        <!-- Contact Number -->
                        <div class="form-group mb-3">
                            <label for="edit-contact_number" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="edit-contact_number" name="contact_number" required>
                        </div>
    
                        <!-- Address Fields -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit-street_address" class="form-label">Street Address</label>
                                    <input type="text" class="form-control" id="edit-street_address" name="street_address">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit-barangay" class="form-label">Barangay</label>
                                    <input type="text" class="form-control" id="edit-barangay" name="barangay">
                                </div>
                            </div>
                        </div>
    
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit-municipality" class="form-label">Municipality/City</label>
                                    <input type="text" class="form-control" id="edit-municipality" name="municipality">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit-province" class="form-label">Province</label>
                                    <input type="text" class="form-control" id="edit-province" name="province">
                                </div>
                            </div>
                        </div>
    
                        <!-- Email -->
                        <div class="form-group mb-3">
                            <label for="edit-email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="edit-email" name="email" required>
                        </div>
    
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Add event listener to all delete buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const userName = this.getAttribute('data-name');
            const form = this.closest('form');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete ${userName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form with fetch
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: new FormData(form)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                // Reload page after successful deletion
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Error deleting user');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: error.message,
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        });
    });
</script>
    


    @push('scripts')
    <script>
        // Handle Create User Form Submission
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            
            // Convert street_number to street_address if needed
            if (formData.has('street_number')) {
                const streetValue = formData.get('street_number');
                formData.delete('street_number');
                formData.append('street_address', streetValue);
            }
    
            // Submit form using fetch
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
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
            })
            .catch(error => {
                if (error.errors) {
                    // Validation errors
                    let errorMessage = '<ul class="list-unstyled">';
                    Object.values(error.errors).forEach(errors => {
                        errors.forEach(err => {
                            // Replace "street number" with "street address" in error messages
                            const fixedError = err.replace('street number', 'street address');
                            errorMessage += `<li>${fixedError}</li>`;
                        });
                    });
                    errorMessage += '</ul>';
    
                    Swal.fire({
                        title: 'Validation Error',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                } else {
                    // General error
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Something went wrong',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        });
    
        // Reset form when modal is closed
        document.getElementById('createUserModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('createUserForm').reset();
            // Remove any validation error messages
            document.querySelectorAll('.is-invalid').forEach(element => {
                element.classList.remove('is-invalid');
            });
            document.querySelectorAll('.invalid-feedback').forEach(element => {
                element.remove();
            });
        });
    
        // Instant validation for password confirmation
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            if (this.value !== password) {
                this.classList.add('is-invalid');
                if (!this.nextElementSibling) {
                    const feedback = document.createElement('div');
                    feedback.classList.add('invalid-feedback');
                    feedback.textContent = 'Passwords do not match';
                    this.parentNode.appendChild(feedback);
                }
            } else {
                this.classList.remove('is-invalid');
                const feedback = this.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            }
        });
    </script>
    @endpush
        
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

    </script>
    @endpush
</x-adminlayout>
