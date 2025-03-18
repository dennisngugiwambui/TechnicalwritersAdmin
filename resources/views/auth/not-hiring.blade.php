<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Technical Writers Admin') }} - Registration Closed</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center px-4">
    <div class="flex flex-col items-center mb-6">
        <!-- Logo -->
        <div class="text-center">
            <h1 class="text-3xl font-bold text-slate-700">
                <span class="text-orange-500">Technical</span>Writers
            </h1>
            <p class="text-slate-500 mt-1">Admin Portal</p>
        </div>
    </div>
    
    <!-- Main Card -->
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="text-center">
            <div class="flex justify-center">
                <div class="bg-orange-100 rounded-full p-4">
                    <i class="fas fa-user-lock text-orange-500 text-4xl"></i>
                </div>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-800 mt-4">Registration Closed</h2>
            
            <div class="mt-6 text-gray-600">
                <p>We are not hiring new Admins at the moment.</p>
                <p class="mt-4">Thank you for your interest in joining our administrative team. Administrative access is currently restricted and by invitation only.</p>
            </div>
            
            <div class="mt-8 mb-4">
                <a href="{{ route('login') }}" class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-6 rounded-md transition duration-300 inline-block">
                    Return to Login
                </a>
            </div>
        </div>
        
        <div class="mt-6 border-t border-gray-200 pt-6 text-center text-gray-600">
            <p>If you need assistance or have questions, please contact our support team.</p>
        </div>
    </div>
    
    <!-- Footer Links -->
    <div class="mt-8 flex space-x-8 text-gray-600">
        <a href="#" class="hover:text-orange-500 transition duration-300">Home</a>
        <a href="#" class="hover:text-orange-500 transition duration-300">Contacts</a>
        <a href="#" class="hover:text-orange-500 transition duration-300">Support</a>
    </div>
</body>
</html>