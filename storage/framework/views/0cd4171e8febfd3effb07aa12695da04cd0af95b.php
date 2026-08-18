

<?php $__env->startSection('title', 'Sales Report'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-chart-line text-sarda-600 mr-2"></i>
            Sales Report
        </h1>
    </div>

    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="<?php echo e(route('reports.sales')); ?>" class="flex items-end space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" 
                       name="start_date" 
                       value="<?php echo e($startDate); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" 
                       name="end_date" 
                       value="<?php echo e($endDate); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg">
                <i class="fas fa-filter mr-2"></i>
                Apply
            </button>
        </form>
    </div>

<!-- Summary Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #f2601f, #e34715);">
        <div class="flex items-center justify-between">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">Total Revenue</p>
                <p class="text-3xl font-bold">₹<?php echo e(number_format($stats['total_revenue'])); ?></p>
            </div>
            <i class="fas fa-rupee-sign text-5xl" style="color: white; opacity: 0.2;"></i>
        </div>
    </div>

    <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #f2601f, #e34715);">
        <div class="flex items-center justify-between">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">Total Orders</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['total_orders'])); ?></p>
            </div>
            <i class="fas fa-shopping-cart text-5xl" style="color: white; opacity: 0.2;"></i>
        </div>
    </div>

    <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #f2601f, #e34715);">
        <div class="flex items-center justify-between">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">Avg Order Value</p>
                <p class="text-3xl font-bold">₹<?php echo e(number_format($stats['average_order_value'])); ?></p>
            </div>
            <i class="fas fa-chart-bar text-5xl" style="color: white; opacity: 0.2;"></i>
        </div>
    </div>

    <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #f2601f, #e34715);">
        <div class="flex items-center justify-between">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">Delivered</p>
                <p class="text-3xl font-bold"><?php echo e(number_format($stats['delivered_orders'])); ?></p>
            </div>
            <i class="fas fa-check-circle text-5xl" style="color: white; opacity: 0.2;"></i>
        </div>
    </div>
</div>
    <!-- Payment Method Breakdown -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales by Payment Method</h3>
        <div class="space-y-3">
            <?php $__currentLoopData = $paymentMethodSales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 flex-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                        <?php if($method->payment_gateway == 'razorpay'): ?> bg-purple-100
                        <?php elseif($method->payment_gateway == 'cod'): ?> bg-yellow-100
                        <?php else: ?> bg-gray-100
                        <?php endif; ?>">
                        <i class="fas 
                            <?php if($method->payment_gateway == 'razorpay'): ?> fa-credit-card text-purple-600
                            <?php elseif($method->payment_gateway == 'cod'): ?> fa-money-bill text-yellow-600
                            <?php else: ?> fa-wallet text-gray-600
                            <?php endif; ?>"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900"><?php echo e(ucfirst($method->payment_gateway ?? 'Other')); ?></p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-sarda-600 h-2 rounded-full" 
                                 style="width: <?php echo e(($method->revenue / $stats['total_revenue']) * 100); ?>%"></div>
                        </div>
                    </div>
                </div>
                <div class="text-right ml-4">
                    <p class="font-bold text-gray-900">₹<?php echo e(number_format($method->revenue)); ?></p>
                    <p class="text-xs text-gray-500"><?php echo e($method->count); ?> orders</p>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Customers</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Spent</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__currentLoopData = $topCustomers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                <?php if($index == 0): ?> bg-yellow-100 text-yellow-800
                                <?php elseif($index == 1): ?> bg-gray-100 text-gray-800
                                <?php elseif($index == 2): ?> bg-orange-100 text-orange-800
                                <?php else: ?> bg-gray-50 text-gray-600
                                <?php endif; ?> font-bold">
                                <?php echo e($index + 1); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo e($customer->customer->name); ?></div>
                            <div class="text-sm text-gray-500"><?php echo e($customer->customer->phone); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo e($customer->order_count); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                            ₹<?php echo e(number_format($customer->total_spent)); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH F:\xampp\htdocs\sardancoCrm\resources\views/reports/sales.blade.php ENDPATH**/ ?>