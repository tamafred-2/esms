<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MyApp</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white shadow-xl rounded-xl w-full max-w-md p-8">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Welcome Back</h2>
            <p class="text-gray-500">Login to your account</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" required autofocus
                    class="w-full border border-gray-300 rounded-md p-3 focus:outline-none focus:border-indigo-500">
                @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full border border-gray-300 rounded-md p-3 focus:outline-none focus:border-indigo-500">
                @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center space-x-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span>Remember me</span>
                </label>
                <a href="#"
                    class="text-sm text-indigo-600 hover:underline">Forgot Password?</a>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-3 rounded-md hover:bg-indigo-700 transition duration-300">
                Login
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Don't have an account?
            <a href="#" class="text-indigo-600 hover:underline">Register</a>
        </p>
    </div>

</body>

</html>
