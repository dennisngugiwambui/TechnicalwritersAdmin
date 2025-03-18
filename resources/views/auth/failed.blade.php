<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Technical Writers Admin') }} - Access Denied</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7eb 100%);
        }
        
        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-lg">
        <!-- Logo Section -->
        <div class="flex flex-col items-center mb-8">
            <!-- Logo -->
            <div class="text-center">
                <h1 class="text-3xl font-bold mb-1">
                    <span class="text-orange-500">Technical</span>
                    <span class="text-slate-700">Writers</span>
                </h1>
                <p class="text-slate-500">Admin Portal</p>
            </div>
        </div>
        
        <!-- Main Card -->
        <div class="card rounded-xl shadow-lg p-8 mb-6">
            <div class="text-center">
                <!-- Error Icon -->
                <div class="bg-red-100 rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-lock text-red-500 text-4xl"></i>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Account Access Restricted</h2>
                
                <!-- Status Message -->
                @if(isset($user) && $user->status)
                    @php
                        $status = $user->status;
                        $message = '';
                        $icon = '';
                        
                        switch($status) {
                            case 'suspended':
                                $message = 'Your account has been temporarily suspended.';
                                $icon = 'fa-clock';
                                break;
                            case 'banned':
                                $message = 'Your account has been banned from the platform.';
                                $icon = 'fa-ban';
                                break;
                            case 'terminated':
                                $message = 'Your account has been terminated.';
                                $icon = 'fa-times-circle';
                                break;
                            case 'locked':
                                $message = 'Your account is currently locked.';
                                $icon = 'fa-lock';
                                break;
                            case 'failed':
                            default:
                                $message = 'Your account verification has failed.';
                                $icon = 'fa-exclamation-triangle';
                                break;
                        }
                        
                        // Check for suspended flag
                        if ($user->is_suspended === 'yes') {
                            $message = 'Your account has been suspended.';
                            $icon = 'fa-user-clock';
                        }
                    @endphp
                    
                    <div class="bg-red-50 p-4 rounded-lg mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas {{ $icon }} text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    Account status: {{ ucfirst($status) }}
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>{{ $message }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-red-50 p-4 rounded-lg mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    Access Denied
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>You do not have permission to access the admin portal.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <p class="text-gray-600 mb-6">
                    If you believe this is an error, please contact our support team for assistance.
                </p>
                
                <div class="flex flex-col space-y-3">
                    <a href="{{ route('login') }}" class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-full transition duration-300">
                        Return to Login
                    </a>
                    <a href="mailto:support@technicalwriters.com" class="text-orange-500 hover:text-orange-600">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} Technical Writers. All rights reserved.
        </div>
    </div>
</body>
</html>