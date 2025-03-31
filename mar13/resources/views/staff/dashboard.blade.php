<x-stafflayout>
    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Staff Dashboard</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Personal Information</h5>
                        <p><strong>Name:</strong> {{ Auth::user()->full_name }}</p>
                        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                        <p><strong>Contact:</strong> {{ Auth::user()->contact_number }}</p>
                        <p><strong>Address:</strong> {{ Auth::user()->full_address }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Additional Information</h5>
                        <p><strong>Gender:</strong> {{ Auth::user()->gender }}</p>
                        <p><strong>Civil Status:</strong> {{ Auth::user()->civil_status }}</p>
                        <p><strong>Nationality:</strong> {{ Auth::user()->nationality }}</p>
                        <p><strong>Classification:</strong> {{ Auth::user()->classification }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-stafflayout>
