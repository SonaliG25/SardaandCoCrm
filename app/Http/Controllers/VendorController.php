<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Display vendors list
     */
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $vendors = $query->latest()->paginate(20);

        // Get vendor stats
        foreach ($vendors as $vendor) {
            switch ($vendor->type) {
                case 'dye':
                    $vendor->pending_count = Order::where('dye_vendor_id', $vendor->id)
                        ->whereIn('dye_status', ['pending', 'received'])->count();
                    $vendor->completed_count = Order::where('dye_vendor_id', $vendor->id)
                        ->where('dye_status', 'completed')->count();
                    break;
                case 'print':
                    $vendor->pending_count = Order::where('print_vendor_id', $vendor->id)
                        ->whereIn('print_status', ['pending', 'received'])->count();
                    $vendor->completed_count = Order::where('print_vendor_id', $vendor->id)
                        ->where('print_status', 'completed')->count();
                    break;
                case 'emb':
                    $vendor->pending_count = Order::where('emb_vendor_id', $vendor->id)
                        ->whereIn('emb_status', ['pending', 'received'])->count();
                    $vendor->completed_count = Order::where('emb_vendor_id', $vendor->id)
                        ->where('emb_status', 'completed')->count();
                    break;
                case 'master':
                    $vendor->pending_count = Order::where('master_vendor_id', $vendor->id)
                        ->whereIn('master_status', ['pending', 'received'])->count();
                    $vendor->completed_count = Order::where('master_vendor_id', $vendor->id)
                        ->where('master_status', 'completed')->count();
                    break;
            }
        }

        return view('vendors.index', compact('vendors'));
    }

    /**
     * Show create vendor form
     */
    public function create()
    {
        return view('vendors.create');
    }

    /**
 * Store new vendor
 */
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|in:dye,print,emb,master',
        'contact_person' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email',
        'is_active' => 'boolean',
    ]);

    // Create vendor
    $vendor = Vendor::create($validated);
    
    // Auto-create user if email is provided
    if ($request->filled('email')) {
        try {
            $this->createUserForVendor($vendor, $request->email);
            
            $message = 'Vendor created successfully! ✅ Login credentials generated. Email: ' . $request->email . ' | Password: vendor123';
        } catch (\Exception $e) {
            $message = 'Vendor created successfully! ⚠️ Login creation failed: ' . $e->getMessage();
        }
    } else {
        $message = 'Vendor created successfully! (No email provided - login not created)';
    }

    return redirect()->route('vendors.show', $vendor)
        ->with('success', $message);
}

/**
 * Helper method to create user for vendor
 */
protected function createUserForVendor(Vendor $vendor, $email = null)
{
    // Check if user already exists
    $existingUser = \App\Models\User::where('vendor_id', $vendor->id)->first();
    
    if ($existingUser) {
        throw new \Exception('User account already exists');
    }

    // Generate email if not provided
    if (!$email) {
        $email = strtolower(str_replace(' ', '', $vendor->name)) . '@vendor.local';
    }
    
    $password = 'vendor123';

    $user = \App\Models\User::create([
        'name' => $vendor->contact_person ?? $vendor->name,
        'email' => $email,
        'password' => \Hash::make($password),
        'user_type' => 'vendor',
        'vendor_id' => $vendor->id,
    ]);

    return $user;
}

/**
 * Create user account for vendor (manual creation)
 */
public function createUser(Vendor $vendor)
{
    try {
        $user = $this->createUserForVendor($vendor, $vendor->email);
        
        return back()->with('success', "✅ Vendor login created! Email: {$user->email} | Password: vendor123 (Ask vendor to change password after login)");
    } catch (\Exception $e) {
        return back()->with('error', '❌ ' . $e->getMessage());
    }
}

    /**
     * Display vendor details
     */
    public function show(Vendor $vendor)
    {
        // Get orders assigned to this vendor based on type
        $ordersQuery = Order::with('customer');
        
        switch ($vendor->type) {
            case 'dye':
                $ordersQuery->where('dye_vendor_id', $vendor->id);
                $statusField = 'dye_status';
                break;
            case 'print':
                $ordersQuery->where('print_vendor_id', $vendor->id);
                $statusField = 'print_status';
                break;
            case 'emb':
                $ordersQuery->where('emb_vendor_id', $vendor->id);
                $statusField = 'emb_status';
                break;
            case 'master':
                $ordersQuery->where('master_vendor_id', $vendor->id);
                $statusField = 'master_status';
                break;
        }

        $orders = $ordersQuery->latest('order_date')->paginate(20);

        // Stats
        $stats = [
            'total_orders' => $orders->total(),
            'pending' => $ordersQuery->where($statusField, 'pending')->count(),
            'received' => $ordersQuery->where($statusField, 'received')->count(),
            'completed' => $ordersQuery->where($statusField, 'completed')->count(),
        ];

        return view('vendors.show', compact('vendor', 'orders', 'stats', 'statusField'));
    }

    /**
     * Show edit form
     */
    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    /**
     * Update vendor
     */
    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:dye,print,emb,master',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'is_active' => 'boolean',
        ]);

        $vendor->update($validated);

        return redirect()->route('vendors.show', $vendor)
            ->with('success', 'Vendor updated successfully!');
    }

    /**
     * Delete vendor
     */
    public function destroy(Vendor $vendor)
    {
        // Check if vendor has assigned orders
        $hasOrders = false;
        switch ($vendor->type) {
            case 'dye':
                $hasOrders = Order::where('dye_vendor_id', $vendor->id)->exists();
                break;
            case 'print':
                $hasOrders = Order::where('print_vendor_id', $vendor->id)->exists();
                break;
            case 'emb':
                $hasOrders = Order::where('emb_vendor_id', $vendor->id)->exists();
                break;
            case 'master':
                $hasOrders = Order::where('master_vendor_id', $vendor->id)->exists();
                break;
        }

        if ($hasOrders) {
            return back()->with('error', 'Cannot delete vendor with assigned orders! Set them as inactive instead.');
        }

        $vendor->delete();

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor deleted successfully!');
    }

    /**
     * Toggle vendor active status
     */
    public function toggleStatus(Vendor $vendor)
    {
        $vendor->update(['is_active' => !$vendor->is_active]);

        $status = $vendor->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Vendor {$status} successfully!",
            'is_active' => $vendor->is_active,
        ]);
    }

    /**
     * Get vendor performance report
     */
    public function performance(Vendor $vendor)
    {
        $statusField = $vendor->type . '_status';
        $vendorField = $vendor->type . '_vendor_id';

        // Orders by status
        $ordersByStatus = Order::where($vendorField, $vendor->id)
            ->select($statusField . ' as status', DB::raw('COUNT(*) as count'))
            ->groupBy($statusField)
            ->get()
            ->pluck('count', 'status');

        // Orders by month (last 6 months)
        $ordersByMonth = Order::where($vendorField, $vendor->id)
            ->where('order_date', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Average completion time (dummy data for now)
        $avgCompletionTime = rand(2, 7) . ' days';

        return view('vendors.performance', compact('vendor', 'ordersByStatus', 'ordersByMonth', 'avgCompletionTime'));
    }
    
    
   public function quickStore(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|in:dye,print,emb,master',
        'phone' => 'required|string|max:20',
        'email' => 'nullable|email|max:255',
        'contact_person' => 'nullable|string|max:255',
        'address' => 'nullable|string',
        'is_active' => 'nullable|boolean',
    ]);

    // ✅ Check if vendor already exists (same name + type)
    $existingVendor = Vendor::where('name', $validated['name'])
        ->where('type', $validated['type'])
        ->first();
    
    if ($existingVendor) {
        return response()->json([
            'success' => true,
            'vendor' => $existingVendor,
            'message' => 'Vendor already exists!'
        ]);
    }

    $vendor = Vendor::create([
        'name' => $validated['name'],
        'type' => $validated['type'],
        'phone' => $validated['phone'],
        'email' => $validated['email'] ?? null,
        'contact_person' => $validated['contact_person'] ?? null,
        'address' => $validated['address'] ?? null,
        'is_active' => $request->has('is_active') ? 1 : 0,
    ]);

    return response()->json([
        'success' => true,
        'vendor' => $vendor,
        'message' => 'Vendor created successfully!'
    ]);
}


}