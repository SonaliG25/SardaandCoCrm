<!-- Mobile Sidebar Overlay -->
<div x-show="sidebarOpen" 
     @click="sidebarOpen = false" 
     class="fixed inset-0 z-20 transition-opacity bg-black opacity-50 lg:hidden"
     style="display: none;"></div>

<!-- Sidebar - Sarda & Co Color Scheme -->
<aside class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition duration-300 transform bg-gradient-to-b from-sarda-800 to-sarda-900 lg:translate-x-0 lg:static lg:inset-0"
       :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
       x-show="true"
       @click.away="sidebarOpen = false">
    
    <!-- Logo -->
    <div class="flex items-center justify-center py-6 bg-sarda-900 border-b border-sarda-700">
        <a href="{{ route('dashboard') }}" class="flex items-center">
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mr-3 shadow-lg">
                <i class="fas fa-boxes text-sarda-600 text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white">{{ config('app.name', 'CRM') }}</h1>
                <p class="text-xs text-sarda-300">CRM</p>
            </div>
        </a>
    </div>

    <!-- User Role Badge -->
    <div class="px-4 py-3 bg-sarda-700 mx-4 rounded-lg mt-4">
        <p class="text-xs text-sarda-300 uppercase tracking-wider">Current Role</p>
        <p class="text-sm font-bold text-white">{{ Auth::user()->role?->name ?? 'No Role' }}</p>
    </div>

    <!-- Navigation -->
    <nav class="px-4 py-6 space-y-2 pb-24">
        
        <!-- Dashboard - Always Visible -->
        @if (Auth::user()->hasMenuAccess('dashboard'))
        <a href="{{ route('dashboard') }}" 
           class="flex items-center px-4 py-3 text-white rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-sarda-600 shadow-lg transform scale-105' : 'hover:bg-sarda-700 hover:translate-x-1' }}">
            <i class="fas fa-tachometer-alt w-5"></i>
            <span class="ml-3 font-medium">Dashboard</span>
        </a>
        @endif

        <!-- Orders -->
        @if (Auth::user()->hasMenuAccess('orders'))
        <div x-data="{ ordersOpen: {{ request()->routeIs('orders.*') ? 'true' : 'false' }} }">
            <button @click="ordersOpen = !ordersOpen"
                    class="flex items-center justify-between w-full px-4 py-3 text-white rounded-lg transition-all duration-200 {{ request()->routeIs('orders.*') ? 'bg-sarda-600 shadow-lg' : 'hover:bg-sarda-700 hover:translate-x-1' }}">
                <div class="flex items-center">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span class="ml-3 font-medium">Orders</span>
                </div>
                <i class="fas fa-chevron-down transition-transform duration-200" :class="{'rotate-180': ordersOpen}"></i>
            </button>
            
            <div x-show="ordersOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="ml-8 mt-2 space-y-1"
                 style="display: none;">
                <a href="{{ route('orders.index') }}" 
                   class="flex items-center px-4 py-2 text-sm text-sarda-100 rounded-lg hover:bg-sarda-700 transition-colors {{ request()->routeIs('orders.index') ? 'bg-sarda-700' : '' }}">
                    <i class="fas fa-list w-4 text-xs"></i>
                    <span class="ml-2">All Orders</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Customers -->
        @if (Auth::user()->hasMenuAccess('customers'))
        <div x-data="{ customersOpen: {{ request()->routeIs('customers.*') ? 'true' : 'false' }} }">
            <button @click="customersOpen = !customersOpen"
                    class="flex items-center justify-between w-full px-4 py-3 text-white rounded-lg transition-all duration-200 {{ request()->routeIs('customers.*') ? 'bg-sarda-600 shadow-lg' : 'hover:bg-sarda-700 hover:translate-x-1' }}">
                <div class="flex items-center">
                    <i class="fas fa-users w-5"></i>
                    <span class="ml-3 font-medium">Customers</span>
                </div>
                <i class="fas fa-chevron-down transition-transform duration-200" :class="{'rotate-180': customersOpen}"></i>
            </button>
            
            <div x-show="customersOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="ml-8 mt-2 space-y-1"
                 style="display: none;">
                <a href="{{ route('customers.index') }}" 
                   class="flex items-center px-4 py-2 text-sm text-sarda-100 rounded-lg hover:bg-sarda-700 transition-colors {{ request()->routeIs('customers.index') ? 'bg-sarda-700' : '' }}">
                    <i class="fas fa-list w-4 text-xs"></i>
                    <span class="ml-2">All Customers</span>
                </a>
                <a href="{{ route('customers.create') }}" 
                   class="flex items-center px-4 py-2 text-sm text-sarda-100 rounded-lg hover:bg-sarda-700 transition-colors {{ request()->routeIs('customers.create') ? 'bg-sarda-700' : '' }}">
                    <i class="fas fa-user-plus w-4 text-xs"></i>
                    <span class="ml-2">Add Customer</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Vendors -->
        @if (Auth::user()->hasMenuAccess('vendors'))
        <div x-data="{ vendorsOpen: {{ request()->routeIs('vendors.*') ? 'true' : 'false' }} }">
            <button @click="vendorsOpen = !vendorsOpen"
                    class="flex items-center justify-between w-full px-4 py-3 text-white rounded-lg transition-all duration-200 {{ request()->routeIs('vendors.*') ? 'bg-sarda-600 shadow-lg' : 'hover:bg-sarda-700 hover:translate-x-1' }}">
                <div class="flex items-center">
                    <i class="fas fa-truck w-5"></i>
                    <span class="ml-3 font-medium">Vendors</span>
                </div>
                <i class="fas fa-chevron-down transition-transform duration-200" :class="{'rotate-180': vendorsOpen}"></i>
            </button>
            
            <div x-show="vendorsOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="ml-8 mt-2 space-y-1"
                 style="display: none;">
                <a href="{{ route('vendors.index') }}" 
                   class="flex items-center px-4 py-2 text-sm text-sarda-100 rounded-lg hover:bg-sarda-700 transition-colors {{ request()->routeIs('vendors.index') ? 'bg-sarda-700' : '' }}">
                    <i class="fas fa-list w-4 text-xs"></i>
                    <span class="ml-2">All Vendors</span>
                </a>
                <a href="{{ route('vendors.create') }}" 
                   class="flex items-center px-4 py-2 text-sm text-sarda-100 rounded-lg hover:bg-sarda-700 transition-colors {{ request()->routeIs('vendors.create') ? 'bg-sarda-700' : '' }}">
                    <i class="fas fa-plus w-4 text-xs"></i>
                    <span class="ml-2">Add Vendor</span>
                </a>
                <a href="{{ route('vendors.index', ['type' => 'dye']) }}" 
                   class="flex items-center px-4 py-2 text-sm text-sarda-100 rounded-lg hover:bg-sarda-700 transition-colors">
                    <i class="fas fa-tint w-4 text-xs"></i>
                    <span class="ml-2">Dye Vendors</span>
                </a>
                <a href="{{ route('vendors.index', ['type' => 'print']) }}" 
                   class="flex items-center px-4 py-2 text-sm text-sarda-100 rounded-lg hover:bg-sarda-700 transition-colors">
                    <i class="fas fa-print w-4 text-xs"></i>
                    <span class="ml-2">Print Vendors</span>
                </a>
                <a href="{{ route('vendors.index', ['type' => 'emb']) }}" 
                   class="flex items-center px-4 py-2 text-sm text-sarda-100 rounded-lg hover:bg-sarda-700 transition-colors">
                    <i class="fas fa-cut w-4 text-xs"></i>
                    <span class="ml-2">Emb Vendors</span>
                </a>
                <a href="{{ route('vendors.index', ['type' => 'master']) }}" 
                   class="flex items-center px-4 py-2 text-sm text-sarda-100 rounded-lg hover:bg-sarda-700 transition-colors">
                    <i class="fas fa-user-tie w-4 text-xs"></i>
                    <span class="ml-2">Masters</span>
                </a>
            </div>
        </div>
        @endif

       

    

        <!-- Divider -->
        <div class="border-t border-sarda-700 my-4"></div>

        <!-- Reports -->
        @if (Auth::user()->hasMenuAccess('reports'))
        <div x-data="{ reportsOpen: false }">
            <button @click="reportsOpen = !reportsOpen"
                    class="flex items-center justify-between w-full px-4 py-3 text-white rounded-lg transition-all duration-200 hover:bg-sarda-700 hover:translate-x-1">
                <div class="flex items-center">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span class="ml-3 font-medium">Reports</span>
                </div>
                <i class="fas fa-chevron-down transition-transform duration-200" :class="{'rotate-180': reportsOpen}"></i>
            </button>
            
            <div x-show="reportsOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="ml-8 mt-2 space-y-1"
                 style="display: none;">
                <a href="{{ route('reports.sales') }}" 
                   class="flex items-center text-white hover:bg-sarda-700 px-3 py-2 rounded-lg transition text-sm {{ request()->routeIs('reports.sales') ? 'bg-sarda-700' : '' }}">
                    <i class="fas fa-chart-line mr-3"></i>
                    Sales Report
                </a>
                <a href="{{ route('reports.payment') }}" 
                   class="flex items-center text-white hover:bg-sarda-700 px-3 py-2 rounded-lg transition text-sm {{ request()->routeIs('reports.payment') ? 'bg-sarda-700' : '' }}">
                    <i class="fas fa-money-bill-wave mr-3"></i>
                    Payment Report
                </a>
                <a href="{{ route('reports.vendor-performance') }}" 
                   class="flex items-center text-white hover:bg-sarda-700 px-3 py-2 rounded-lg transition text-sm {{ request()->routeIs('reports.vendor-performance') ? 'bg-sarda-700' : '' }}">
                    <i class="fas fa-users-cog mr-3"></i>
                    Vendor Performance
                </a>
            </div>
        </div>
        @endif

        <!-- Settings -->
        @if (Auth::user()->hasMenuAccess('settings'))
        <a href="{{ route('settings.index') }}"
           class="flex items-center px-4 py-3 text-white rounded-lg transition-all duration-200 {{ request()->routeIs('settings.*') ? 'bg-sarda-600 shadow-lg transform scale-105' : 'hover:bg-sarda-700 hover:translate-x-1' }}">
            <i class="fas fa-cog w-5"></i>
            <span class="ml-3 font-medium">Settings</span>
        </a>
        @endif

        <!-- Divider for Admin Section -->
        @if (Auth::user()->hasMenuAccess('users') || Auth::user()->hasMenuAccess('roles'))
        <div class="border-t border-sarda-700 my-4"></div>
        @endif

        <!-- Users Management (Admin/Super Admin) -->
        @if (Auth::user()->hasMenuAccess('users'))
        <a href="{{ route('users.index') }}" 
           class="flex items-center px-4 py-3 text-white rounded-lg transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-sarda-600 shadow-lg transform scale-105' : 'hover:bg-sarda-700 hover:translate-x-1' }}">
            <i class="fas fa-user-tie w-5"></i>
            <span class="ml-3 font-medium">Users</span>
        </a>
        @endif

        <!-- Roles Management (Super Admin Only) -->
        @if (Auth::user()->hasMenuAccess('roles'))
        <a href="{{ route('roles.index') }}" 
           class="flex items-center px-4 py-3 text-white rounded-lg transition-all duration-200 {{ request()->routeIs('roles.*') ? 'bg-sarda-600 shadow-lg transform scale-105' : 'hover:bg-sarda-700 hover:translate-x-1' }}">
            <i class="fas fa-key w-5"></i>
            <span class="ml-3 font-medium">Roles</span>
        </a>
        @endif

        <!-- Activity Logs (Always visible to Super Admin/Admin) -->
        @if (Auth::user()->role?->is_system || Auth::user()->role?->name == 'Admin')
        <a href="{{ route('activity-logs.index') }}" 
           class="flex items-center px-4 py-3 text-white rounded-lg transition-all duration-200 {{ request()->routeIs('activity-logs.*') ? 'bg-sarda-600 shadow-lg transform scale-105' : 'hover:bg-sarda-700 hover:translate-x-1' }}">
            <i class="fas fa-history w-5"></i>
            <span class="ml-3 font-medium">Activity Logs</span>
        </a>
        @endif

    </nav>

    <!-- User Info at Bottom -->
    <div class="absolute bottom-0 left-0 right-0 p-4 bg-sarda-900 border-t border-sarda-700">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-sarda-500 flex items-center justify-center text-white font-bold shadow-lg">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                <p class="text-xs text-sarda-300 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</aside>

@push('scripts')
<script>
function toggleSubmenu(menu) {
    const submenu = document.getElementById(`${menu}-submenu`);
    const icon = document.getElementById(`${menu}-icon`);
    
    submenu.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}
</script>
@endpush

