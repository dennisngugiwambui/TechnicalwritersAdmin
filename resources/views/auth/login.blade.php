<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Technical Writers Admin') }} - Login</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    },
                    boxShadow: {
                        'glow': '0 0 15px rgba(249, 115, 22, 0.5)',
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom styles -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7eb 100%);
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc2626;
        }
        
        .form-error-shake {
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
        
        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-2px, 0, 0); }
            40%, 60% { transform: translate3d(2px, 0, 0); }
        }
        
        .login-btn {
            background: linear-gradient(90deg, #f97316, #fb923c);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.6);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4">
    <!-- Animated Shapes (Background) -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-10 left-1/4 w-20 h-20 rounded-full bg-orange-200 opacity-50 animate-float" style="animation-delay: 0s;"></div>
        <div class="absolute top-1/3 right-1/4 w-32 h-32 rounded-full bg-orange-300 opacity-30 animate-float" style="animation-delay: 0.5s;"></div>
        <div class="absolute bottom-1/4 left-10 w-24 h-24 rounded-full bg-blue-200 opacity-30 animate-float" style="animation-delay: 1s;"></div>
        <div class="absolute -bottom-10 right-1/3 w-16 h-16 rounded-full bg-orange-100 opacity-60 animate-float" style="animation-delay: 1.5s;"></div>
    </div>

    <div class="w-full max-w-md">
        <!-- Logo Section with Animation -->
        <div class="flex flex-col items-center mb-10">
            <div class="relative mb-6 animate-float">
                <!-- Main Rocket -->
                <svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="drop-shadow-xl">
                    <path d="M50 20C60 30 70 40 70 60C60 55 40 55 30 60C30 40 40 30 50 20Z" fill="#f97316" stroke="#475569" stroke-width="2"/>
                    <path d="M40 65C35 75 30 85 30 85C20 75 20 65 30 60C35 60 40 65 40 65Z" fill="#f97316" stroke="#475569" stroke-width="2"/>
                    <path d="M60 65C65 75 70 85 70 85C80 75 80 65 70 60C65 60 60 65 60 65Z" fill="#f97316" stroke="#475569" stroke-width="2"/>
                    <circle cx="50" cy="45" r="5" fill="white"/>
                </svg>
                
                <!-- Glow Effect -->
                <div class="absolute inset-0 bg-orange-500 rounded-full filter blur-xl opacity-20 animate-pulse"></div>
                
                <!-- Stars -->
                <div class="absolute -top-4 -left-4 w-4 h-4 bg-white rounded-full opacity-70 animate-pulse"></div>
                <div class="absolute top-0 -right-2 w-3 h-3 bg-white rounded-full opacity-60 animate-pulse" style="animation-delay: 0.5s;"></div>
                <div class="absolute -bottom-2 left-0 w-2 h-2 bg-white rounded-full opacity-80 animate-pulse" style="animation-delay: 1s;"></div>
            </div>
            
            <!-- Logo Text with Gradient -->
            <div class="text-center">
                <h1 class="text-4xl font-bold mb-1">
                    <span class="bg-gradient-to-r from-orange-500 to-orange-400 text-transparent bg-clip-text">Technical</span>
                    <span class="text-slate-700">Writers</span>
                </h1>
                <p class="text-slate-500 text-lg">Admin Portal</p>
            </div>
        </div>
        
        <!-- Login Form Card with Glassmorphism -->
        <div class="form-container rounded-2xl shadow-xl p-8 mb-6">
            <!-- Facebook Login Button with Icon -->
            <a href="#" class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-full w-full transition duration-300 mb-6 shadow-md">
                <i class="fab fa-facebook-f mr-3"></i>
                <span class="font-medium">LOG IN WITH FACEBOOK</span>
            </a>
            
            <!-- OR Divider with Improved Styling -->
            <div class="relative flex items-center justify-center my-8">
                <div class="absolute border-t border-gray-300 w-full opacity-50"></div>
                <div class="relative bg-white px-6 text-gray-400 font-medium rounded-full">OR</div>
            </div>
            
            <!-- Login Form with Improved Styling -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6" id="loginForm">
                @csrf
                
                <!-- Phone Field -->
                <div class="relative">
                    <div class="flex items-center bg-white rounded-full border overflow-hidden transition-all @error('phone') border-red-500 form-error-shake @enderror">
                        <div class="flex items-center justify-center pl-4 pr-2">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input id="phone" type="text" class="flex-1 py-3 px-2 bg-transparent border-0 focus:ring-0 focus:outline-none" name="phone" value="{{ old('phone') }}" required autocomplete="phone" autofocus placeholder="Phone Number">
                    </div>
                    
                    @error('phone')
                        <div class="flex items-center mt-2 text-red-500">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span class="text-sm">{{ $message }}</span>
                        </div>
                    @enderror
                </div>
                
                <!-- Password Field -->
                <div class="relative">
                    <div class="flex items-center bg-white rounded-full border overflow-hidden transition-all @error('password') border-red-500 form-error-shake @enderror">
                        <div class="flex items-center justify-center pl-4 pr-2">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" type="password" class="flex-1 py-3 px-2 bg-transparent border-0 focus:ring-0 focus:outline-none" name="password" required autocomplete="current-password" placeholder="Password">
                    </div>
                    
                    @error('password')
                        <div class="flex items-center mt-2 text-red-500">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span class="text-sm">{{ $message }}</span>
                        </div>
                    @enderror
                </div>
                
                <!-- Remember Me Toggle -->
                <div class="flex items-center">
                    <div class="relative inline-block w-10 mr-2 align-middle select-none">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} class="absolute block w-6 h-6 bg-white border-4 rounded-full appearance-none cursor-pointer checked:right-0 checked:border-orange-500 transition-all duration-200"/>
                        <label for="remember" class="block h-6 overflow-hidden bg-gray-300 rounded-full cursor-pointer"></label>
                    </div>
                    <label for="remember" class="text-sm text-gray-700 cursor-pointer">Remember Me</label>
                </div>
                
                <!-- Submit Button and Forgot Password with Improved Styling -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-8">
                    <button type="submit" class="login-btn text-white py-3 px-10 rounded-full transition duration-300 text-center font-medium">
                        LOG IN
                    </button>
                    
                    @if (Route::has('password.request'))
                        <a class="text-orange-500 hover:text-orange-600 text-sm transition duration-300 underline" href="{{ route('password.request') }}">
                            Forgot your password?
                        </a>
                    @endif
                </div>
            </form>
            
            <!-- Sign Up Link with Improved Styling -->
            <div class="mt-8 text-center text-gray-600">
                <span>Need an account?</span>
                <a href="{{ route('not-hiring') }}" class="text-orange-500 hover:text-orange-600 ml-1 transition duration-300 font-medium">Sign up</a>
            </div>
        </div>
        
        <!-- reCAPTCHA Notice with Improved Styling -->
        <div class="text-xs text-gray-500 text-center px-6 py-3 bg-white bg-opacity-70 rounded-xl shadow-sm">
            This site is protected by reCAPTCHA and the Google
            <a href="https://policies.google.com/privacy" class="text-orange-500 hover:underline">Privacy Policy</a>
            and
            <a href="https://policies.google.com/terms" class="text-orange-500 hover:underline">Terms of Service</a>
            apply.
        </div>
        
        <!-- Footer Links with Improved Styling -->
        <div class="mt-8 flex flex-wrap justify-center gap-x-8 gap-y-2 text-gray-600">
            <a href="#" class="hover:text-orange-500 transition duration-300 transform hover:-translate-y-1">Home</a>
            <a href="#" class="hover:text-orange-500 transition duration-300 transform hover:-translate-y-1">Contacts</a>
            <a href="#" class="hover:text-orange-500 transition duration-300 transform hover:-translate-y-1">Pricing policy</a>
        </div>
    </div>
    
    <script>
        // Check if there are any validation errors and apply shake animation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const hasErrors = form.querySelectorAll('.border-red-500').length > 0;
            
            if (hasErrors) {
                // Apply visual cue for errors
                form.classList.add('form-error-shake');
                setTimeout(() => {
                    form.classList.remove('form-error-shake');
                }, 500);
            }
        });
    </script>
</body>
</html>