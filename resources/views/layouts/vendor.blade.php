<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vendor Portal') - SardaandCo</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Vendor Sidebar -->
        <aside class="w-64 text-white flex flex-col" style="background: linear-gradient(to bottom, #3b82f6, #1e40af);">
            <!-- Logo -->
            <div class="p-6 border-b border-white border-opacity-20">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <i class="fas fa-industry text-2xl text-blue-600"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">{{ Auth::user()->vendor->name ?? 'Vendor' }}</h1>
                        <p class="text-xs opacity-75">{{ ucfirst(Auth::user()->vendor->type ?? 'Portal') }} Vendor</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <a href="{{ route('vendor.dashboard') }}" 
                   class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-6 py-3 transition
                          {{ request()->routeIs('vendor.dashboard') ? 'bg-white bg-opacity-20 border-l-4 border-white' : '' }}">
                    <i class="fas fa-home mr-3"></i>
                    Dashboard
                </a>

                <a href="{{ route('vendor.orders') }}" 
                   class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-6 py-3 transition
                          {{ request()->routeIs('vendor.orders') ? 'bg-white bg-opacity-20 border-l-4 border-white' : '' }}">
                    <i class="fas fa-clipboard-list mr-3"></i>
                    My Orders
                </a>
            </nav>

            <!-- User Profile -->
            <div class="p-6 border-t border-white border-opacity-20">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                        <span class="text-xl font-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium">{{ Auth::user()->name }}</p>
                        <p class="text-xs opacity-75">{{ Auth::user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-white opacity-75 hover:opacity-100" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">
                            <i class="far fa-clock mr-1"></i>
                            {{ now()->format('d M Y, h:i A') }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100">
                @if(session('success'))
                    <div class="mx-4 mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mx-4 mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>