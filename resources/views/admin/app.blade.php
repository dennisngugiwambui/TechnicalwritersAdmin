<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TechnicalWriters Admin') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS via CDN for simplicity -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        },
                    },
                }
            }
        }
    </script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #e5e5e5;
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #d4d4d4;
        }
        
        /* Navigation active states */
        .nav-item.active {
            border-left-color: #f97316;
            background-color: rgba(249, 115, 22, 0.1);
            color: #f97316;
        }
        
        /* Dropdown animations */
        .dropdown-enter {
            transform: translateY(-10px);
            opacity: 0;
        }
        .dropdown-enter-active {
            transform: translateY(0);
            opacity: 1;
            transition: opacity 150ms, transform 150ms;
        }
        .dropdown-exit {
            transform: translateY(0);
            opacity: 1;
        }
        .dropdown-exit-active {
            transform: translateY(-10px);
            opacity: 0;
            transition: opacity 150ms, transform 150ms;
        }
    </style>
    
    <!-- Alpine.js for interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Additional Styles -->
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div x-data="{ sidebarOpen: window.innerWidth >= 1024 }" x-cloak>
        <!-- Sidebar Backdrop (Mobile) -->
        <div 
            x-show="sidebarOpen" 
            class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 lg:hidden" 
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <div
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r shadow-sm overflow-y-auto lg:shadow-none transform transition-transform duration-300 lg:translate-x-0"
            :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
        >
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-4 border-b">
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <div class="bg-primary-500 text-white rounded-lg p-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <span class="text-xl font-semibold">
                        <span class="text-primary-500">Technical</span>Writers
                    </span>
                </a>
                
                <!-- Close button (mobile only) -->
                <button @click="sidebarOpen = false" class="p-2 rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Sidebar Navigation -->
            <nav class="p-4 space-y-1">
                <!-- Dashboard -->
                <a href="{{ route('home') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>
                
                <!-- Orders Section -->
                <div x-data="{ open: {{ request()->routeIs('admin.orders.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list w-5 h-5 mr-3"></i>
                        <span>Orders</span>
                        <i class="fas fa-chevron-down ml-auto transform transition-transform duration-200" :class="{'rotate-180': open}"></i>
                    </button>
                    
                    <div x-show="open" class="pl-4 mt-1 space-y-1" x-collapse>
                        <a href="{{ route('admin.orders.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.orders.index') && !request()->has('status') ? 'text-primary-500' : '' }}">
                            <i class="fas fa-list-ul w-4 h-4 mr-3"></i>
                            <span>All Orders</span>
                            <span class="ml-auto bg-gray-200 text-gray-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $orderCounts['all'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('admin.orders.index', ['status' => 'available']) }}" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->has('status') && request()->status == 'available' ? 'text-primary-500' : '' }}">
                            <i class="fas fa-clipboard w-4 h-4 mr-3"></i>
                            <span>Available</span>
                            <span class="ml-auto bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $orderCounts['available'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('admin.orders.index', ['status' => 'in_progress']) }}" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->has('status') && request()->status == 'in_progress' ? 'text-primary-500' : '' }}">
                            <i class="fas fa-clock w-4 h-4 mr-3"></i>
                            <span>In Progress</span>
                            <span class="ml-auto bg-orange-100 text-orange-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $orderCounts['in_progress'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('admin.orders.index', ['status' => 'revision']) }}" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->has('status') && request()->status == 'revision' ? 'text-primary-500' : '' }}">
                            <i class="fas fa-undo w-4 h-4 mr-3"></i>
                            <span>Revision</span>
                            <span class="ml-auto bg-red-100 text-red-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $orderCounts['revision'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->has('status') && request()->status == 'completed' ? 'text-primary-500' : '' }}">
                            <i class="fas fa-check-circle w-4 h-4 mr-3"></i>
                            <span>Completed</span>
                            <span class="ml-auto bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $orderCounts['completed'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('admin.orders.index', ['status' => 'dispute']) }}" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->has('status') && request()->status == 'dispute' ? 'text-primary-500' : '' }}">
                            <i class="fas fa-exclamation-triangle w-4 h-4 mr-3"></i>
                            <span>Disputes</span>
                            <span class="ml-auto bg-yellow-100 text-yellow-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $orderCounts['dispute'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->has('status') && request()->status == 'cancelled' ? 'text-primary-500' : '' }}">
                            <i class="fas fa-ban w-4 h-4 mr-3"></i>
                            <span>Cancelled</span>
                            <span class="ml-auto bg-gray-100 text-gray-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $orderCounts['cancelled'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('admin.orders.create') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.orders.create') ? 'text-primary-500' : '' }}">
                            <i class="fas fa-plus w-4 h-4 mr-3"></i>
                            <span>Create Order</span>
                        </a>
                    </div>
                </div>


                


                <!-- Bids -->
                <a href="{{ route('admin.orders.bids') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.writers.*') ? 'active' : '' }}">
                    <i class="fas fa-users w-5 h-5 mr-3"></i>
                    <span>Writers</span>
                </a>
                
                <!-- Writers -->
                <a href="{{ route('admin.writers.index') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.writers.*') ? 'active' : '' }}">
                    <i class="fas fa-users w-5 h-5 mr-3"></i>
                    <span>Writers</span>
                </a>
                
                <!-- Payments -->
                <a href="{{ route('admin.payments.index') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave w-5 h-5 mr-3"></i>
                    <span>Payments</span>
                </a>
                
                <!-- Messages -->
                <a href="{{ route('admin.messages.index') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                    <i class="fas fa-comment-alt w-5 h-5 mr-3"></i>
                    <span>Messages</span>
                    @if($unreadMessageCount > 0)
                    <span class="ml-auto bg-red-100 text-red-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $unreadMessageCount }}</span>
                    @endif
                </a>
                
                <!-- Settings -->
                <a href="{{ route('admin.settings') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary-500 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog w-5 h-5 mr-3"></i>
                    <span>Settings</span>
                </a>
            </nav>
            
            <!-- User Info Footer -->
            <div class="border-t mt-auto">
                <div class="p-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            @if(Auth::user()->profile_picture)
                                <img class="h-10 w-10 rounded-full object-cover" src="{{ asset(Auth::user()->profile_picture) }}" alt="{{ Auth::user()->name }}">
                            @else
                                <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-semibold">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ Auth::user()->email }}
                            </p>
                        </div>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" class="origin-bottom-left absolute right-0 bottom-full mb-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <div class="py-1" role="menu" aria-orientation="vertical">
                                    <a href="{{ route('admin.profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">
                                        <i class="fas fa-user mr-2"></i> Profile
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="lg:pl-64 flex flex-col min-h-screen">
            <!-- Top Navbar -->
            <header class="sticky top-0 z-30 bg-white border-b">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <!-- Left -->
                    <div class="flex items-center">
                        <!-- Mobile menu button -->
                        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 lg:hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        
                        <!-- Page title -->
                        <h1 class="ml-4 text-lg font-medium text-gray-900 lg:ml-0">
                            @yield('page-title', 'Dashboard')
                        </h1>
                    </div>
                    
                    <!-- Right -->
                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="hidden md:block">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" placeholder="Search..." class="block w-64 pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <!-- Notifications -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <span class="sr-only">View notifications</span>
                                <i class="fas fa-bell"></i>
                                @if($unreadMessageCount > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center h-4 w-4 rounded-full bg-red-500 text-xs font-bold text-white">
                                    {{ $unreadMessageCount > 9 ? '9+' : $unreadMessageCount }}
                                </span>
                                @endif
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95">
                                <div class="py-1" role="menu" aria-orientation="vertical">
                                    <div class="px-4 py-2 border-b">
                                        <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                    </div>
                                    
                                    @if(count($recentMessages) > 0)
                                        @foreach($recentMessages as $message)
                                        <a href="{{ route('admin.messages.show', $message->id) }}" class="flex px-4 py-3 hover:bg-gray-50 border-b">
                                            <div class="flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-primary-50 flex items-center justify-center text-primary-500">
                                                    <i class="fas fa-comment-alt"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-gray-900">{{ $message->title ?? 'New message' }}</p>
                                                <p class="text-sm text-gray-500">{{ Str::limit($message->message, 50) }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $message->created_at->diffForHumans() }}</p>
                                            </div>
                                        </a>
                                        @endforeach
                                    @else
                                        <div class="py-4 text-center text-sm text-gray-500">
                                            No new notifications
                                        </div>
                                    @endif
                                    
                                    <div class="px-4 py-2 text-center border-t">
                                        <a href="{{ route('admin.messages.index') }}" class="text-sm font-medium text-primary-500 hover:text-primary-600">View all notifications</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Profile dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none">
                                <div class="flex-shrink-0">
                                    @if(Auth::user()->profile_picture)
                                        <img class="h-9 w-9 rounded-full object-cover" src="{{ asset(Auth::user()->profile_picture) }}" alt="{{ Auth::user()->name }}">
                                    @else
                                        <div class="h-9 w-9 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-semibold">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="hidden md:flex md:flex-col md:items-end">
                                    <span class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</span>
                                    <span class="text-xs text-gray-500">Administrator</span>
                                </div>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95">
                                <div class="py-1" role="menu" aria-orientation="vertical">
                                    <a href="{{ route('admin.profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">
                                        <i class="fas fa-user mr-2"></i> Profile
                                    </a>
                                    <a href="{{ route('admin.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">
                                        <i class="fas fa-cog mr-2"></i> Settings
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <!-- Flash messages -->
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6">
                    <div class="rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    {{ session('success') }}
                                </p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button @click="show = false" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <span class="sr-only">Dismiss</span>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6">
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">
                                    {{ session('error') }}
                                </p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button @click="show = false" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <span class="sr-only">Dismiss</span>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Main content -->
                @yield('content')
            </main>
            
            <!-- Footer -->
            <footer class="bg-white border-t py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        &copy; {{ date('Y') }} TechnicalWriters Admin. All rights reserved.
                    </div>
                    <div class="flex space-x-6">
                        <a href="#" class="text-gray-400 hover:text-gray-500">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-gray-500">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-gray-500">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Additional Scripts -->
    @stack('scripts')
 </body>
 </html>