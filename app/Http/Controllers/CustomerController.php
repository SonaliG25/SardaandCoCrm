<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
   /**
 * Display customers list
 */
public function index(Request $request)
{
    $query = Customer::withCount([
        'orders' => function($q) {
            $q->whereNotIn('order_status', ['cancelled', 'refunded']);
        }
    ])
    ->withSum([
        'orders as total_spent' => function($q) {
            $q->whereNotIn('order_status', ['cancelled', 'refunded']);
        }
    ], 'amount');

    // Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('woocommerce_customer_id', 'like', "%{$search}%");
        });
    }

    // Filter by order count
    if ($request->filled('has_orders')) {
        if ($request->has_orders === 'yes') {
            $query->has('orders');
        } elseif ($request->has_orders === 'no') {
            $query->doesntHave('orders');
        }
    }

    // Sort
    $sortBy = $request->get('sort', 'latest');
    switch ($sortBy) {
        case 'name':
            $query->orderBy('name');
            break;
        case 'orders':
            $query->orderBy('orders_count', 'desc');
            break;
        case 'spent':
            $query->orderBy('total_spent', 'desc');
            break;
        default:
            $query->latest();
    }

    $customers = $query->paginate(20);

    // Stats
    $stats = [
        'total_customers' => Customer::count(),
        'with_orders' => Customer::has('orders')->count(),
        'without_orders' => Customer::doesntHave('orders')->count(),
        'total_revenue' => Customer::withSum([
            'orders as total_revenue' => function($q) {
                $q->whereNotIn('order_status', ['cancelled', 'refunded']);
            }
        ], 'amount')->get()->sum('total_revenue'),
    ];

    return view('customers.index', compact('customers', 'stats'));
}
    /**
     * Show create customer form
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store new customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
        ]);

        $customer = Customer::create($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', '✅ Customer created successfully!');
    }

  /**
 * Display customer details
 */
public function show(Customer $customer)
{
    $customer->load(['orders' => function($query) {
        $query->with('shippingPartner')->latest('order_date');
    }]);
    
    // Calculate stats - exclude cancelled/refunded
    $validOrders = $customer->orders->whereNotIn('order_status', ['cancelled', 'refunded']);
    
    $stats = [
        'total_orders' => $validOrders->count(),
        'total_spent' => $validOrders->sum('amount'),
        'avg_order_value' => $validOrders->count() > 0 
            ? $validOrders->sum('amount') / $validOrders->count() 
            : 0,
        'pending_orders' => $validOrders->whereIn('order_status', ['new', 'processing'])->count(),
        'completed_orders' => $validOrders->where('order_status', 'delivered')->count(),
        'cancelled_orders' => $customer->orders->whereIn('order_status', ['cancelled', 'refunded'])->count(),
    ];

    // Recent orders (last 10)
    $recentOrders = $customer->orders()->take(10)->get();

    return view('customers.show', compact('customer', 'stats', 'recentOrders'));
}
    /**
     * Show edit form
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', '✅ Customer updated successfully!');
    }

    /**
     * Delete customer
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has orders
        if ($customer->orders()->count() > 0) {
            return back()->with('error', '❌ Cannot delete customer with existing orders! Archive them instead.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', '✅ Customer deleted successfully!');
    }

    /**
     * Get customer orders
     */
    public function orders(Customer $customer)
    {
        $orders = $customer->orders()
            ->with('shippingPartner')
            ->latest('order_date')
            ->paginate(15);

        return view('customers.orders', compact('customer', 'orders'));
    }

    /**
     * Sync customers from WooCommerce
     */
    public function syncFromWooCommerce()
    {
        try {
            $wooService = app(\App\Services\WooCommerceService::class);
            
            // if (!$wooService->isConfigured()) {
            //     return back()->with('error', '❌ WooCommerce not configured!');
            // }

            // Get WooCommerce customers
            $client = new \GuzzleHttp\Client([
                'base_uri' => config('services.woocommerce.url'),
                'auth' => [
                    config('services.woocommerce.consumer_key'),
                    config('services.woocommerce.consumer_secret')
                ],
                'verify' => false,
            ]);

            $synced = 0;
            $page = 1;
            $perPage = 50;

            do {
                $response = $client->get('/wp-json/wc/v3/customers', [
                    'query' => [
                        'page' => $page,
                        'per_page' => $perPage,
                    ]
                ]);

                $wcCustomers = json_decode($response->getBody(), true);

                foreach ($wcCustomers as $wcCustomer) {
                    $this->createOrUpdateCustomerFromWooCommerce($wcCustomer);
                    $synced++;
                }

                $page++;
                usleep(500000); // 0.5 second delay to avoid rate limiting

            } while (count($wcCustomers) === $perPage);

            return back()->with('success', "✅ Synced {$synced} customers from WooCommerce!");

        } catch (\Exception $e) {
            \Log::error('WooCommerce customer sync failed', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', '❌ Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Create or update customer from WooCommerce data
     */
    protected function createOrUpdateCustomerFromWooCommerce($wcCustomer)
    {
        // Extract billing/shipping data
        $billing = $wcCustomer['billing'] ?? [];
        $shipping = $wcCustomer['shipping'] ?? [];

        // Prepare address
        $address = trim(implode(', ', array_filter([
            $billing['address_1'] ?? '',
            $billing['address_2'] ?? '',
        ])));

        Customer::updateOrCreate(
            [
                'woocommerce_customer_id' => $wcCustomer['id']
            ],
            [
                'name' => trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? '')) ?: $wcCustomer['username'] ?? 'Customer',
                'email' => $wcCustomer['email'] ?? null,
                'phone' => $billing['phone'] ?? null,
                'address' => $address ?: null,
                'city' => $billing['city'] ?? null,
                'state' => $billing['state'] ?? null,
                'pincode' => $billing['postcode'] ?? null,
                'country' => $billing['country'] ?? 'IN',
            ]
        );
    }

    /**
     * Merge duplicate customers
     */
    public function merge(Request $request)
    {
        $request->validate([
            'primary_customer_id' => 'required|exists:customers,id',
            'merge_customer_id' => 'required|exists:customers,id|different:primary_customer_id',
        ]);

        try {
            DB::beginTransaction();

            $primaryCustomer = Customer::findOrFail($request->primary_customer_id);
            $mergeCustomer = Customer::findOrFail($request->merge_customer_id);

            // Move all orders from merge customer to primary customer
            Order::where('customer_id', $mergeCustomer->id)
                ->update(['customer_id' => $primaryCustomer->id]);

            // Update primary customer with any missing info
            if (!$primaryCustomer->email && $mergeCustomer->email) {
                $primaryCustomer->email = $mergeCustomer->email;
            }
            if (!$primaryCustomer->phone && $mergeCustomer->phone) {
                $primaryCustomer->phone = $mergeCustomer->phone;
            }
            if (!$primaryCustomer->address && $mergeCustomer->address) {
                $primaryCustomer->address = $mergeCustomer->address;
            }
            $primaryCustomer->save();

            // Delete merge customer
            $mergeCustomer->delete();

            DB::commit();

            return back()->with('success', '✅ Customers merged successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '❌ Merge failed: ' . $e->getMessage());
        }
    }
}