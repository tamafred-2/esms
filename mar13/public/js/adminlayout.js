


// side bar

document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            // Toggle sidebar
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });
            }

            // Close sidebar when clicking overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });

            // Close sidebar when clicking outside
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 992) {
                    if (!sidebar.contains(event.target) && 
                        !sidebarToggle.contains(event.target)) {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    }
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
            });

            // Prevent clicks within sidebar from closing it
            sidebar.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Add animation for dropdown arrows
            const dropdownToggles = document.querySelectorAll('[data-bs-toggle="collapse"]');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    this.classList.toggle('collapsed');
                });
            });
        });


        let chart;
        let currentChartType = 'pie';
        
        document.addEventListener('DOMContentLoaded', function() {
            initializeChart();
            setupEventListeners();
            updateChartData(); // Initial update
        });
        
        function initializeChart() {
            const ctx = document.getElementById('studentPieChart').getContext('2d');
            
            // Create gradients for 3D effect
            const competentGradient = ctx.createLinearGradient(0, 0, 0, 300);
            competentGradient.addColorStop(0, 'rgba(40, 167, 69, 1)');
            competentGradient.addColorStop(1, 'rgba(40, 167, 69, 0.8)');
            
            const incompetentGradient = ctx.createLinearGradient(0, 0, 0, 300);
            incompetentGradient.addColorStop(0, 'rgb(179, 51, 51)');
            incompetentGradient.addColorStop(1, 'rgb(179, 51, 51)');
        
            chart = new Chart(ctx, {
                type: currentChartType,
                data: {
                    labels: ['Competent', 'Needs Improvement'],
                    datasets: [{
                        data: [45, 30],
                        backgroundColor: [competentGradient, incompetentGradient],
                        borderColor: ['#ffffff', '#ffffff'],
                        borderWidth: 2,
                        offset: 20,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${context.label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    layout: {
                        padding: 20
                    },
                    elements: {
                        arc: {
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 15
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true
                    }
                }
            });
        }
        
        function setupEventListeners() {
            // Chart type change
            document.getElementById('chartType').addEventListener('change', function(e) {
                currentChartType = e.target.value;
                updateChartType();
            });
        
            // Filters
            ['yearFilter', 'courseFilter'].forEach(filterId => {
                document.getElementById(filterId).addEventListener('change', updateChartData);
            });
        }
        
        function updateChartType() {
            chart.destroy();
            initializeChart();
            updateChartData();
        }
        
        function toggleDataset(index) {
            const meta = chart.getDatasetMeta(0);
            const currentState = meta.data[index].hidden || false;
            meta.data[index].hidden = !currentState;
            chart.update();
        }
        
        function updateChartData() {
            const year = document.getElementById('yearFilter').value;
            const course = document.getElementById('courseFilter').value;
        
            // Simulate data based on filters
            let newData;
            
            if (year === 'all' && course === 'all') {
                newData = [45, 30];
            } else {
                // Generate random data for filter combinations
                newData = [
                    Math.floor(Math.random() * 50) + 20,
                    Math.floor(Math.random() * 30) + 10
                ];
            }
        
            // Update chart data
            chart.data.datasets[0].data = newData;
            chart.update();
        
            // Update statistics
            const total = newData.reduce((a, b) => a + b, 0);
            document.querySelector('.total-value').textContent = total;
            
            // Update percentages in legend
            const legendValues = document.querySelectorAll('.legend-value');
            legendValues[0].textContent = `${newData[0]} (${Math.round(newData[0]/total*100)}%)`;
            legendValues[1].textContent = `${newData[1]} (${Math.round(newData[1]/total*100)}%)`;
        
            // Update average score
            const averageScore = Math.round((newData[0] / total) * 100);
            document.querySelector('.average-value').textContent = `${averageScore}%`;
        
            // Update trend
            const trendElement = document.querySelector('.trend-value');
            const trendValue = Math.round((Math.random() * 10) - 5);
            const trendIcon = trendValue >= 0 ? 
                '<i class="bi bi-arrow-up-circle-fill text-success"></i>' : 
                '<i class="bi bi-arrow-down-circle-fill text-danger"></i>';
            trendElement.innerHTML = `${trendIcon} ${trendValue >= 0 ? '+' : ''}${trendValue}%`;
        }



        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.flex-grow-1');
            const isMobile = () => window.innerWidth <= 768;
        
            function toggleSidebar() {
                sidebar.classList.toggle('collapsed');
                if (isMobile()) {
                    sidebar.classList.toggle('mobile-show');
                }
                
                if (sidebar.classList.contains('collapsed')) {
                    mainContent.style.marginLeft = '0';
                    localStorage.setItem('sidebarState', 'collapsed');
                } else {
                    mainContent.style.marginLeft = isMobile() ? '0' : '280px';
                    localStorage.setItem('sidebarState', 'expanded');
                }
            }
        
            // Initialize sidebar state
            if (isMobile()) {
                sidebar.classList.add('collapsed');
                mainContent.style.marginLeft = '0';
            } else {
                const sidebarState = localStorage.getItem('sidebarState');
                if (sidebarState === 'collapsed') {
                    sidebar.classList.add('collapsed');
                    mainContent.style.marginLeft = '0';
                }
            }
        
            // Toggle button click handler
            sidebarToggle.addEventListener('click', function() {
                toggleSidebar();
            });
        
            // Handle window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (isMobile()) {
                        mainContent.style.marginLeft = '0';
                        if (!sidebar.classList.contains('collapsed')) {
                            sidebar.classList.add('mobile-show');
                        }
                    } else {
                        sidebar.classList.remove('mobile-show');
                        mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? '0' : '280px';
                    }
                }, 250);
            });
        });

document.addEventListener('DOMContentLoaded', function() {
    // Delete event handler
    document.querySelectorAll('.delete-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = this.dataset.eventId;
            const eventTitle = this.dataset.eventTitle;

            Swal.fire({
                title: 'Are you sure?',
                html: `You are about to delete <strong>${eventTitle}</strong>.<br>This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create and submit form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/events/${eventId}`;
                    
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    
                    form.appendChild(csrfInput);
                    form.appendChild(methodInput);
                    document.body.appendChild(form);
                    
                    // Show loading state
                    Swal.fire({
                        title: 'Deleting...',
                        html: 'Please wait...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submit the form
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message || 'Event has been deleted successfully.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Failed to delete event');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Something went wrong while deleting the event.',
                            icon: 'error'
                        });
                    })
                    .finally(() => {
                        document.body.removeChild(form);
                    });
                }
            });
        });
    });
});

// Delete school handler
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-school').forEach(button => {
        button.addEventListener('click', function() {
            const schoolId = this.dataset.schoolId;
            const schoolName = this.dataset.schoolName;

            Swal.fire({
                title: 'Are you sure?',
                html: `You are about to delete <strong>${schoolName}</strong>.<br>This action cannot be undone!`,
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
                        html: 'Please wait...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send delete request
                    fetch(`/admin/school/${schoolId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Failed to delete school',
                            icon: 'error'
                        });
                    });
                }
            });
        });
    });
});


// Course management
document.addEventListener('DOMContentLoaded', function() {
    // Handle assign staff modal
    const assignStaffModal = document.getElementById('assignStaffModal');
    if (assignStaffModal) {
        assignStaffModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const courseId = button.getAttribute('data-course-id');
            document.getElementById('assignCourseId').value = courseId;
        });
    }

    // Handle course deletion
    document.querySelectorAll('.delete-course').forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.getAttribute('data-course-id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send delete request
                    fetch(`/admin/course/${courseId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Deleted!',
                                'Course has been deleted.',
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        Swal.fire(
                            'Error!',
                            error.message,
                            'error'
                        );
                    });
                }
            });
        });
    });
});



document.addEventListener('DOMContentLoaded', function() {
    // Handle Edit Staff Modal
    const editStaffModal = document.getElementById('editStaffModal');
    if (editStaffModal) {
        editStaffModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const staffId = button.getAttribute('data-staff-id');
            const staffPosition = button.getAttribute('data-staff-position');
            
            const form = this.querySelector('form');
            form.action = `/admin/school/{{ $school->id }}/staff/${staffId}`;
            form.querySelector('#edit_position').value = staffPosition;
        });
    }

    // Handle Remove Staff
    document.querySelectorAll('.remove-staff').forEach(button => {
        button.addEventListener('click', function() {
            const staffId = this.dataset.staffId;
            const staffName = this.dataset.staffName;
            const schoolId = this.dataset.schoolId;
    
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to remove ${staffName} from this school.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Removing staff...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
    
                    // Send remove request
                    fetch(`/admin/school/${schoolId}/remove-staff/${staffId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: 'DELETE'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message || 'Staff removed successfully',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Failed to remove staff');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: error.message || 'Something went wrong!'
                        });
                    });
                }
            });
        });
    });
});


    // Form submission handling
    const form = document.getElementById('createUserForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            submitBtn.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        showConfirmButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.reset();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Something went wrong!',
                        showConfirmButton: true
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Something went wrong!',
                    showConfirmButton: true
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
            });
        });
    }


    document.addEventListener('DOMContentLoaded', function() {
        // Delete Course Sweet Alert
        document.querySelectorAll('.delete-course').forEach(button => {
            button.addEventListener('click', function() {
                const courseId = this.dataset.courseId;
                const courseName = this.dataset.courseName;
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete "${courseName}". This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create and submit form programmatically
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/admin/course/${courseId}`;
                        
                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = '{{ csrf_token() }}';
                        
                        const method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = '_method';
                        method.value = 'DELETE';
                        
                        form.appendChild(csrf);
                        form.appendChild(method);
                        document.body.appendChild(form);
                        
                        form.submit();
                    }
                });
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Handle card clicks
        document.querySelectorAll('.cards').forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking dropdown or its children
                if (!e.target.closest('.dropdown')) {
                    const link = this.querySelector('.card-link-overlay');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
            });
        });
    
        // Prevent dropdown clicks from triggering card click
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    });

    document.getElementById('addSchoolForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Reset previous error messages
    document.querySelectorAll('.invalid-feedback').forEach(element => {
        element.textContent = '';
    });
    document.querySelectorAll('.form-control').forEach(element => {
        element.classList.remove('is-invalid');
    });

    const formData = new FormData(this);

    fetch('{{ route("dashboard.storeSchool") }}', {
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
                text: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Reload the page or update the schools list
                    window.location.reload();
                }
            });

            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('schoolModal'));
            modal.hide();
            
            // Reset the form
            this.reset();
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        if (error.response && error.response.status === 422) {
            // Validation errors
            const errors = error.response.data.errors;
            Object.keys(errors).forEach(field => {
                const input = document.getElementById(field);
                const errorDisplay = document.getElementById(field + 'Error');
                if (input && errorDisplay) {
                    input.classList.add('is-invalid');
                    errorDisplay.textContent = errors[field][0];
                }
            });
        } else {
            // General error
            Swal.fire({
                title: 'Error!',
                text: 'An unexpected error occurred. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
});

// Optional: Preview image before upload
document.getElementById('logo_path').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) { // 2MB in bytes
            Swal.fire({
                title: 'Error!',
                text: 'File size must not exceed 2MB',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            this.value = ''; // Clear the file input
            return;
        }
        
        if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select a valid image file (JPEG, PNG, or JPG)',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            this.value = ''; // Clear the file input
            return;
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const assignStaffForm = document.getElementById('assignStaffForm');
    
    if (assignStaffForm) {
        assignStaffForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const staffId = document.getElementById('staff_id').value;
            if (!staffId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select a staff member'
                });
                return;
            }

            // Show loading state
            Swal.fire({
                title: 'Assigning staff...',
                text: 'Please wait',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Create form data
            const formData = new FormData(this);

            // Send request
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal first
                    const modal = bootstrap.Modal.getInstance(document.getElementById('assignStaffModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        showConfirmButton: true,
                        timer: 1500
                    }).then(() => {
                        // Reload the page after the success message
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to assign staff');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Failed to assign staff'
                });
            });
        });
    }
    // Handle staff removal
    document.querySelectorAll('.remove-staff').forEach(button => {
        button.addEventListener('click', function() {
            const staffId = this.dataset.staffId;
            const staffName = this.dataset.staffName;

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to remove ${staffName} from this school.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Removing staff...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send remove request
                    fetch(`/admin/school/${schoolId}/remove-staff/${staffId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message || 'Staff removed successfully',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Failed to remove staff');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: error.message || 'Something went wrong!'
                        });
                    });
                }
            });
        });
    });
});



document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-sector').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const sectorId = this.dataset.sectorId;
            const sectorName = this.dataset.sectorName;

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete sector "${sectorName}". This action cannot be undone.`,
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

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                    fetch(`/admin/sectors/${sectorId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message || 'Sector has been deleted successfully.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Failed to delete sector');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: error.message || 'Something went wrong!'
                        });
                    });
                }
            });
        });
    });
});