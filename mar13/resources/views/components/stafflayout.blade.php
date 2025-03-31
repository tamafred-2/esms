<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Nazareno | Staff</title>
</head>
<body>
    @if(auth()->check() && auth()->user()->usertype === 'staff')
        {{$slot}}
    @else
        <!-- Unauthorized access -->
        <div class="container mt-5">
            <div class="alert alert-danger text-center">
                <h4>Please log in to access this area</h4>
                <p>You will be redirected to the login page...</p>
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "/";
            }, 3000);
        </script>
    @endif
</body>
</html>