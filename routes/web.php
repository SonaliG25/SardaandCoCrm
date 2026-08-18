<?php
use Illuminate\Support\Facades\Cache;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WooCommerceController;
use App\Http\Controllers\ShippingPartnerController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\RoleController;           // ✅ ADD THIS
use App\Http\Controllers\UserController;          // ✅ ADD THIS
use App\Http\Controllers\ActivityLogController;   // ✅ ADD THIS
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\ShippingTracking;
use Carbon\Carbon;
/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Redirect after login based on user type
Route::get('/redirect-after-login', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    
    if (Auth::user()->user_type === 'vendor') {
        return redirect()->route('vendor.dashboard');
    }
    
    return redirect()->route('dashboard');
})->middleware('auth')->name('redirect-after-login');

/*
|--------------------------------------------------------------------------
| Webhooks (Public - No Auth)
|--------------------------------------------------------------------------
*/
// ⚠️ TEMPORARY ROUTE - RUN ONCE THEN DELETE THIS BLOCK ENTIRELY

Route::post('/webhook/razorpay', [WebhookController::class, 'razorpay'])->name('webhook.razorpay');
Route::post('/webhook/woocommerce', [WooCommerceController::class, 'webhook'])->name('woocommerce.webhook');
Route::get('/cron/sync-missing-orders', function () {

  
    $woo = app(\App\Services\WooCommerceService::class);

    $missingOrders = [
        8484,8481,8478,8470,8468,
        8455,8452,8450,8448,8446,
        8443,8440,8437,8435
    ];

    $synced = [];
    $failed = [];

    foreach ($missingOrders as $orderId) {

        try {

            $response = $woo->getOrder($orderId);

            if (!$response['success']) {
                $failed[$orderId] = $response['message'] ?? 'Order not found';
                continue;
            }

            $woo->createOrUpdateOrder($response['data']);

            $synced[] = $orderId;

        } catch (\Exception $e) {

            $failed[$orderId] = $e->getMessage();
        }
    }

    return response()->json([
        'success' => true,
        'synced' => $synced,
        'failed' => $failed
    ]);
});
// Route::get('/test-order/{id}', function ($id) {

//     $woo = app(\App\Services\WooCommerceService::class);

//     return $woo->getOrder($id);

// });
// Route::get('/cron/recover-missing-orders', function () {

   
//     $woocommerceService = app(\App\Services\WooCommerceService::class);

//     // Force sync from May 29, 2026
//     $lastSyncTime = \Carbon\Carbon::parse('2026-05-29 00:00:00');

//     $result = $woocommerceService->getOrdersSince($lastSyncTime);

//     if (!$result['success']) {
//         return response()->json([
//             'success' => false,
//             'message' => $result['message']
//         ], 400);
//     }

//     $orders = $result['orders'] ?? [];

//     $syncedCount = 0;
//     $skippedCount = 0;
//     $failedCount = 0;
//     $failedOrders = [];

//     foreach ($orders as $woOrder) {

//         try {

//             // Skip if already exists
//             $existingOrder = \App\Models\Order::where(
//                 'woocommerce_order_id',
//                 $woOrder['id']
//             )->first();

//             if ($existingOrder) {
//                 $skippedCount++;
//                 continue;
//             }

//             $woocommerceService->createOrUpdateOrder($woOrder);

//             $syncedCount++;

//         } catch (\Exception $e) {

//             $failedCount++;

//             $failedOrders[] = [
//                 'wc_order_id' => $woOrder['id'],
//                 'error' => $e->getMessage()
//             ];

//             \Log::error('Recovery Sync Failed', [
//                 'wc_order_id' => $woOrder['id'],
//                 'error' => $e->getMessage()
//             ]);
//         }
//     }

//     return response()->json([
//         'success' => true,
//         'from_date' => $lastSyncTime->toDateTimeString(),
//         'orders_found' => count($orders),
//         'synced' => $syncedCount,
//         'skipped' => $skippedCount,
//         'failed' => $failedCount,
//         'failed_orders' => $failedOrders
//     ]);
// });

Route::get('/fix-master-status-enum-x9k2', function () {
     $before = \Illuminate\Support\Facades\DB::select(
        "SHOW COLUMNS FROM orders WHERE Field IN ('dye_status', 'print_status', 'emb_status')"
    );

    \Illuminate\Support\Facades\DB::statement(
        "ALTER TABLE orders MODIFY COLUMN dye_status ENUM('pending', 'received', 'completed', 'na') NOT NULL DEFAULT 'pending'"
    );
    \Illuminate\Support\Facades\DB::statement(
        "ALTER TABLE orders MODIFY COLUMN print_status ENUM('pending', 'received', 'completed', 'na') NOT NULL DEFAULT 'pending'"
    );
    \Illuminate\Support\Facades\DB::statement(
        "ALTER TABLE orders MODIFY COLUMN emb_status ENUM('pending', 'received', 'completed', 'na') NOT NULL DEFAULT 'pending'"
    );

    $after = \Illuminate\Support\Facades\DB::select(
        "SHOW COLUMNS FROM orders WHERE Field IN ('dye_status', 'print_status', 'emb_status')"
    );

    return response()->json([
        'success' => true,
        'message' => 'dye_status, print_status, emb_status enums widened. DELETE THIS ROUTE NOW.',
        'before' => $before,
        'after' => $after,
    ]);
});
Route::get('/cron/sync-orders', function () {
    if (request()->input('token') !== env('CRON_TOKEN')) {
        abort(403, 'Unauthorized');
    }
// Auto-correct sequence table (safety net)
DB::statement("UPDATE order_id_sequences SET last_order_number = (
    SELECT CAST(REPLACE(MAX(order_id), '#', '') AS UNSIGNED) FROM orders
) WHERE id = 1 AND last_order_number < (
    SELECT CAST(REPLACE(MAX(order_id), '#', '') AS UNSIGNED) FROM orders
)");
    $woocommerceService = app(\App\Services\WooCommerceService::class);

    // Get last sync time
    $lastSyncTime = Cache::get('woocommerce_last_sync', now()->subHours(1));

    // Fetch only NEW orders since last sync
    $result = $woocommerceService->getOrdersSince($lastSyncTime);

    if (!$result['success']) {
        ActivityLogService::log(
            'error',
            'woocommerce',
            'cron_sync_' . now()->timestamp,
            'WooCommerce Cron',
            "Failed to fetch orders: " . $result['message']
        );
        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'timestamp' => now()
        ], 400);
    }

    $orders = $result['orders'] ?? [];
    $syncedCount = 0;
    $failedCount = 0;

    foreach ($orders as $woOrder) {
        try {
            // ✅ Use createOrUpdateOrder directly (no extra API call)
            $woocommerceService->createOrUpdateOrder($woOrder);
            $syncedCount++;

            ActivityLogService::log(
                'created',
                'orders',
                $woOrder['id'],
                'Order',
                "Synced WooCommerce order #{$woOrder['id']} - {$woOrder['billing']['first_name']} {$woOrder['billing']['last_name']}"
            );

        } catch (\Exception $e) {
            $failedCount++;

            ActivityLogService::log(
                'error',
                'orders',
                $woOrder['id'],
                'Order',
                "Failed to sync order #{$woOrder['id']}: " . $e->getMessage()
            );

            Log::error('Cron Order Sync Failed', [
                'wc_order_id' => $woOrder['id'],
                'error' => $e->getMessage()
            ]);
        }
    }

    // Update last sync time
    Cache::put('woocommerce_last_sync', now(), 60 * 60 * 24);

    ActivityLogService::log(
        'completed',
        'woocommerce',
        'cron_sync_' . now()->timestamp,
        'WooCommerce Sync',
        "Cron: Synced {$syncedCount} orders, {$failedCount} failed"
    );

    return response()->json([
        'success' => true,
        'new_orders_synced' => $syncedCount,
        'failed_orders' => $failedCount,
        'last_sync' => $lastSyncTime,
        'next_sync' => now()->addMinutes(5),
        'timestamp' => now()
    ]);
});

/*
|--------------------------------------------------------------------------
| Main CRM Routes (Protected - Any Authenticated User with Menu Access)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // Dashboard - Everyone can access (check menu access in sidebar)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // Orders - Menu access controlled in controller/blade
    Route::resource('orders', OrderController::class);
    Route::post('orders/{order}/update-stage', [OrderController::class, 'updateStage'])->name('orders.update-stage');
    Route::post('orders/{order}/update-shipping', [OrderController::class, 'updateShipping'])->name('orders.update-shipping');
    Route::post('orders/{order}/update-payment', [OrderController::class, 'updatePayment'])->name('orders.update-payment');
    Route::post('orders/bulk-update', [OrderController::class, 'bulkUpdate'])->name('orders.bulk-update');
    Route::get('orders-export', [OrderController::class, 'export'])->name('orders.export');
    Route::post('orders/{order}/refresh-tracking', [OrderController::class, 'refreshTracking'])->name('orders.refresh-tracking');
    Route::post('orders/{order}/check-payment', [OrderController::class, 'checkPayment'])->name('orders.check-payment');
    Route::post('orders/check-all-payments', [OrderController::class, 'checkAllPayments'])->name('orders.check-all-payments');
    Route::post('orders/{order}/auto-dispatch', [OrderController::class, 'autoDispatch'])->name('orders.auto-dispatch');
    Route::post('/orders/{order}/sync', [OrderController::class, 'syncSingle'])->name('orders.sync');
    Route::post('/orders/{order}/cancel-shipment', [OrderController::class, 'cancelShipment'])->name('orders.cancel-shipment');
Route::post('orders/fix-delivered-dates', [OrderController::class, 'fixDeliveredDates'])->name('orders.fix-delivered-dates');
Route::post('orders/sync-workflow-statuses', [OrderController::class, 'syncAllWorkflowStatuses'])->name('orders.sync-workflow-statuses');

    // Customers - Menu access controlled
    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/orders', [CustomerController::class, 'orders'])->name('customers.orders');
    Route::post('customers/sync-woocommerce', [CustomerController::class, 'syncFromWooCommerce'])->name('customers.sync-woocommerce');
    Route::post('customers/merge', [CustomerController::class, 'merge'])->name('customers.merge');

    // Vendors - Menu access controlled
    Route::resource('vendors', VendorController::class);
    Route::post('vendors/{vendor}/toggle-status', [VendorController::class, 'toggleStatus'])->name('vendors.toggle-status');
    Route::get('vendors/{vendor}/performance', [VendorController::class, 'performance'])->name('vendors.performance');
    Route::post('vendors/{vendor}/create-user', [VendorController::class, 'createUser'])->name('vendors.create-user');
    Route::post('/vendors/quick-store', [VendorController::class, 'quickStore'])->name('vendors.quick-store');

    // Shipping Partners - Menu access controlled
    Route::resource('shipping-partners', ShippingPartnerController::class);
    Route::post('/shipping-partners/{shippingPartner}/toggle-status', [ShippingPartnerController::class, 'toggleStatus'])->name('shipping-partners.toggle-status');

    // Reports - Menu access controlled
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'salesReport'])->name('sales');
        Route::get('/payment', [ReportController::class, 'paymentReport'])->name('payment');
        Route::get('/vendor-performance', [ReportController::class, 'vendorPerformance'])->name('vendor-performance');
    });
    
    // Settings - Menu access controlled
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/profile', [SettingsController::class, 'updateProfile'])->name('update-profile');
        Route::post('/password', [SettingsController::class, 'updatePassword'])->name('update-password');
        Route::post('/business', [SettingsController::class, 'updateBusiness'])->name('update-business');
        Route::post('/api', [SettingsController::class, 'updateApiSettings'])->name('update-api');
        Route::post('/test-connection', [SettingsController::class, 'testConnection'])->name('test-connection');
    });

    // WooCommerce Integration - Menu access controlled
    Route::prefix('woocommerce')->name('woocommerce.')->group(function () {
        Route::get('/', [WooCommerceController::class, 'index'])->name('index');
        Route::post('/test-connection', [WooCommerceController::class, 'testConnection'])->name('test-connection');
        Route::post('/sync-orders', [WooCommerceController::class, 'syncOrders'])->name('sync-orders');
    });

});

/*
|--------------------------------------------------------------------------
| User Management Routes (Admin & Super Admin Only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.menu:users'])->group(function () {
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');
});

/*
|--------------------------------------------------------------------------
| Role Management Routes (Super Admin Only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.menu:roles'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::post('roles/{role}/clone', [RoleController::class, 'clone'])->name('roles.clone');
});

/*
|--------------------------------------------------------------------------
| Activity Logs Routes (Admin & Super Admin Only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
});

/*
|--------------------------------------------------------------------------
| Vendor Routes (Protected - Vendor Only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\VendorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [\App\Http\Controllers\VendorDashboardController::class, 'myOrders'])->name('orders');
    Route::post('/orders/{order}/update-status', [\App\Http\Controllers\VendorDashboardController::class, 'updateStatus'])->name('update-status');
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

// use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Auth;
// use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\OrderController;
// use App\Http\Controllers\CustomerController;
// use App\Http\Controllers\VendorController;
// use App\Http\Controllers\WooCommerceController;
// use App\Http\Controllers\ShippingPartnerController;
// use App\Http\Controllers\WebhookController;
// use App\Http\Controllers\ReportController;
// use App\Http\Controllers\SettingsController;

// /*
// |--------------------------------------------------------------------------
// | Public Routes
// |--------------------------------------------------------------------------
// */

// Route::get('/', function () {
//     return redirect()->route('login');
// });

// // Redirect after login based on user type
// Route::get('/redirect-after-login', function () {
//     if (!Auth::check()) {
//         return redirect()->route('login');
//     }
    
//     if (Auth::user()->user_type === 'vendor') {
//         return redirect()->route('vendor.dashboard');
//     }
    
//     return redirect()->route('dashboard');
// })->middleware('auth')->name('redirect-after-login');

// /*
// |--------------------------------------------------------------------------
// | Webhooks (Public - No Auth)
// |--------------------------------------------------------------------------
// */

// Route::post('/webhook/razorpay', [WebhookController::class, 'razorpay'])->name('webhook.razorpay');
// Route::post('/webhook/woocommerce', [WooCommerceController::class, 'webhook'])->name('woocommerce.webhook');
// // Auto-sync cron endpoint (no authentication required for cron)


// Route::get('/cron/sync-orders', function () {
//     // Security: Check for secret token
//   if (request()->input('token') !== env('CRON_TOKEN')) {
//     abort(403, 'Unauthorized');
// }

//     $woocommerceService = app(\App\Services\WooCommerceService::class);
//     $result = $woocommerceService->syncOrders(50, 1, 'any');
    
//     return response()->json([
//         'success' => $result['success'],
//         'message' => $result['message'],
//         'timestamp' => now()
//     ]);
// });

// Route::get('/admin/migrate-product-images', function() {
//     $wooService = app(\App\Services\WooCommerceService::class);
//     $orders = \App\Models\Order::whereNotNull('woocommerce_order_id')->get();
    
//     $updated = 0;
    
//     foreach ($orders as $order) {
//         try {
//             // Fetch order from WooCommerce
//             $result = $wooService->getOrder($order->woocommerce_order_id);
            
//             if ($result['success'] && isset($result['data']['line_items'][0]['image']['src'])) {
//                 $imageUrl = $result['data']['line_items'][0]['image']['src'];
                
//                 $order->update(['product_image' => $imageUrl]);
//                 $updated++;
//             }
//         } catch (\Exception $e) {
//             // Skip on error
//         }
//     }
    
//     return "Updated $updated orders with WooCommerce image URLs";
// })->middleware('auth');
// /*
// |--------------------------------------------------------------------------
// | Admin Routes (Protected - Admin Only)
// |--------------------------------------------------------------------------
// */

// Route::middleware(['auth', 'admin'])->group(function () {
    
//     // Dashboard
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
//     Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

//     // Orders
//     Route::resource('orders', OrderController::class);
//     Route::post('orders/{order}/update-stage', [OrderController::class, 'updateStage'])->name('orders.update-stage');
//     Route::post('orders/{order}/update-shipping', [OrderController::class, 'updateShipping'])->name('orders.update-shipping');
//     Route::post('orders/{order}/update-payment', [OrderController::class, 'updatePayment'])->name('orders.update-payment');
//     Route::post('orders/bulk-update', [OrderController::class, 'bulkUpdate'])->name('orders.bulk-update');
//     Route::get('orders-export', [OrderController::class, 'export'])->name('orders.export');
//     Route::post('orders/{order}/refresh-tracking', [OrderController::class, 'refreshTracking'])->name('orders.refresh-tracking');
//     Route::post('orders/{order}/check-payment', [OrderController::class, 'checkPayment'])->name('orders.check-payment');
//     Route::post('orders/check-all-payments', [OrderController::class, 'checkAllPayments'])->name('orders.check-all-payments');
//     Route::post('orders/{order}/auto-dispatch', [OrderController::class, 'autoDispatch'])->name('orders.auto-dispatch');
// Route::post('/orders/{order}/sync', [OrderController::class, 'syncSingle'])->name('orders.sync');
//     // Customers
//     Route::resource('customers', CustomerController::class);
//     Route::get('customers/{customer}/orders', [CustomerController::class, 'orders'])->name('customers.orders');

//     // Vendors
//     Route::resource('vendors', VendorController::class);
//     Route::post('vendors/{vendor}/toggle-status', [VendorController::class, 'toggleStatus'])->name('vendors.toggle-status');
//     Route::get('vendors/{vendor}/performance', [VendorController::class, 'performance'])->name('vendors.performance');
//     Route::post('vendors/{vendor}/create-user', [VendorController::class, 'createUser'])->name('vendors.create-user');
// Route::post('/vendors/quick-store', [VendorController::class, 'quickStore'])
//     ->name('vendors.quick-store');
//     // Shipping Partners
//     Route::resource('shipping-partners', ShippingPartnerController::class);
//     Route::post('/shipping-partners/{shippingPartner}/toggle-status', [ShippingPartnerController::class, 'toggleStatus'])->name('shipping-partners.toggle-status');
//     Route::post('/orders/{order}/cancel-shipment', [OrderController::class, 'cancelShipment'])
//     ->name('orders.cancel-shipment');
//     // Reports
//     Route::prefix('reports')->name('reports.')->group(function () {
//         Route::get('/sales', [ReportController::class, 'salesReport'])->name('sales');
//         Route::get('/payment', [ReportController::class, 'paymentReport'])->name('payment');
//         Route::get('/vendor-performance', [ReportController::class, 'vendorPerformance'])->name('vendor-performance');
//     });
    
//     // Settings
//     Route::prefix('settings')->name('settings.')->group(function () {
//         Route::get('/', [SettingsController::class, 'index'])->name('index');
//         Route::post('/profile', [SettingsController::class, 'updateProfile'])->name('update-profile');
//         Route::post('/password', [SettingsController::class, 'updatePassword'])->name('update-password');
//         Route::post('/business', [SettingsController::class, 'updateBusiness'])->name('update-business');
//         Route::post('/api', [SettingsController::class, 'updateApiSettings'])->name('update-api');
//         Route::post('/test-connection', [SettingsController::class, 'testConnection'])->name('test-connection');
//     });

//     // WooCommerce Integration
//     Route::prefix('woocommerce')->name('woocommerce.')->group(function () {
//         Route::get('/', [WooCommerceController::class, 'index'])->name('index');
//         Route::post('/test-connection', [WooCommerceController::class, 'testConnection'])->name('test-connection');
//         Route::post('/sync-orders', [WooCommerceController::class, 'syncOrders'])->name('sync-orders');
        
//     });
    
//       // Customers
//     Route::resource('customers', CustomerController::class);
//     Route::get('customers/{customer}/orders', [CustomerController::class, 'orders'])->name('customers.orders');
//     Route::post('customers/sync-woocommerce', [CustomerController::class, 'syncFromWooCommerce'])->name('customers.sync-woocommerce');
//     Route::post('customers/merge', [CustomerController::class, 'merge'])->name('customers.merge');
    
    
// Route::get('/admin/migrate-order-products', function() {
//     $orders = \App\Models\Order::whereNotNull('woocommerce_order_id')->get();
//     $wooService = app(\App\Services\WooCommerceService::class);
    
//     $migrated = 0;
//     $failed = 0;
    
//     foreach ($orders as $order) {
//         try {
//             // Fetch from WooCommerce
//             $result = $wooService->getOrder($order->woocommerce_order_id);
            
//             if ($result['success'] && isset($result['data']['line_items'])) {
//                 $lineItems = $result['data']['line_items'];
                
//                 foreach ($lineItems as $item) {
//                     \App\Models\OrderProduct::create([
//                         'order_id' => $order->id,
//                         'product_name' => $item['name'],
//                         'product_sku' => $item['sku'] ?? null,
//                         'quantity' => $item['quantity'],
//                         'price' => $item['price'],
//                         'product_image' => $item['image']['src'] ?? null,
//                         // Copy order-level workflow to product-level
//                         'dye_status' => $order->dye_status ?? 'pending',
//                         'dye_vendor_id' => $order->dye_vendor_id,
//                         'dye_received_date' => $order->dye_received_date,
//                         'print_status' => $order->print_status ?? 'pending',
//                         'print_vendor_id' => $order->print_vendor_id,
//                         'print_received_date' => $order->print_received_date,
//                         'emb_status' => $order->emb_status ?? 'pending',
//                         'emb_vendor_id' => $order->emb_vendor_id,
//                         'emb_received_date' => $order->emb_received_date,
//                         'master_status' => $order->master_status ?? 'pending',
//                         'master_vendor_id' => $order->master_vendor_id,
//                         'master_received_date' => $order->master_received_date,
//                     ]);
//                 }
                
//                 $migrated++;
//             }
//         } catch (\Exception $e) {
//             $failed++;
//             \Log::error('Migration failed for order ' . $order->id, ['error' => $e->getMessage()]);
//         }
//     }
    
//     return "Migration complete! Migrated: $migrated, Failed: $failed";
// })->middleware('auth');
// });

// /*
// |--------------------------------------------------------------------------
// | Vendor Routes (Protected - Vendor Only)
// |--------------------------------------------------------------------------
// */

// Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
//     Route::get('/dashboard', [\App\Http\Controllers\VendorDashboardController::class, 'index'])->name('dashboard');
//     Route::get('/orders', [\App\Http\Controllers\VendorDashboardController::class, 'myOrders'])->name('orders');
//     Route::post('/orders/{order}/update-status', [\App\Http\Controllers\VendorDashboardController::class, 'updateStatus'])->name('update-status');
// });

// /*
// |--------------------------------------------------------------------------
// | Auth Routes
// |--------------------------------------------------------------------------
// */
// // Role Management (Super Admin only)
// Route::middleware(['auth', 'role:Super Admin'])->group(function () {
//     Route::resource('roles', RoleController::class);
//     Route::post('roles/{role}/clone', [RoleController::class, 'clone'])->name('roles.clone');
// });

// // Activity Logs (Admin & Super Admin)
// Route::middleware(['auth'])->group(function () {
//     Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
//     Route::get('activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
// });

// require __DIR__.'/auth.php';