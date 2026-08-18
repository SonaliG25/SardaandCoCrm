<!-- Top Navigation Bar -->
<header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200 shadow-sm">
    <!-- Mobile menu button -->
    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none lg:hidden">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Page Title / Breadcrumb -->
    <div class="flex items-center">
        <h2 class="text-2xl font-semibold text-gray-800">
            <?php echo $__env->yieldContent('page-title', 'Dashboard'); ?>
        </h2>
    </div>

    <!-- Right side buttons -->
    <div class="flex items-center space-x-4">
        <!-- Notifications -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-800 focus:outline-none">
                <i class="fas fa-bell text-xl"></i>
                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- Notification Dropdown -->
            <div x-show="open" 
                 x-cloak
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg overflow-hidden z-50">
                <div class="px-4 py-3 bg-gray-50 border-b">
                    <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <a href="#" class="flex items-start px-4 py-3 hover:bg-gray-50 border-b">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shopping-cart text-blue-500"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm text-gray-800">New order received</p>
                            <p class="text-xs text-gray-500 mt-1">5 minutes ago</p>
                        </div>
                    </a>
                    <a href="#" class="flex items-start px-4 py-3 hover:bg-gray-50 border-b">
                        <div class="flex-shrink-0">
                            <i class="fas fa-truck text-green-500"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm text-gray-800">Order #4140 delivered</p>
                            <p class="text-xs text-gray-500 mt-1">1 hour ago</p>
                        </div>
                    </a>
                </div>
                <div class="px-4 py-3 bg-gray-50 border-t">
                    <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">View all notifications</a>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold">
                    <?php echo e(strtoupper(substr(Auth::user()->name, 0, 1))); ?>

                </div>
                <span class="hidden md:block text-sm font-medium text-gray-700"><?php echo e(Auth::user()->name); ?></span>
                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
            </button>

            <!-- Profile Dropdown -->
            <div x-show="open" 
                 x-cloak
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg overflow-hidden z-50">
                <div class="px-4 py-3 bg-gray-50 border-b">
                    <p class="text-sm font-semibold text-gray-800"><?php echo e(Auth::user()->name); ?></p>
                    <p class="text-xs text-gray-500"><?php echo e(Auth::user()->email); ?></p>
                </div>
              
                <a href="<?php echo e(route('settings.index')); ?>" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-cog mr-3 text-gray-500"></i>
                    Settings
                </a>
                <div class="border-t">
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-gray-50">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header><?php /**PATH F:\xampp\htdocs\sardancoCrm\resources\views/layouts/topbar.blade.php ENDPATH**/ ?>