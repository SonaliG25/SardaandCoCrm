<?php

namespace App\Http\Controllers;

use App\Models\ShippingPartner;
use App\Models\Order;
use Illuminate\Http\Request;

class ShippingPartnerController extends Controller
{
    /**
     * Display a listing of shipping partners
     */
    public function index(Request $request)
    {
        $query = ShippingPartner::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $partners = $query->paginate(15);

        // Add statistics for each partner
        foreach ($partners as $partner) {
            $partner->total_shipments = Order::where('shipping_partner_id', $partner->id)->count();
            $partner->pending_shipments = Order::where('shipping_partner_id', $partner->id)
                ->where('shipping_status', 'pending')
                ->count();
            $partner->in_transit = Order::where('shipping_partner_id', $partner->id)
                ->whereIn('shipping_status', ['dispatched', 'in_transit', 'out_for_delivery'])
                ->count();
            $partner->delivered = Order::where('shipping_partner_id', $partner->id)
                ->where('shipping_status', 'delivered')
                ->count();
        }

        return view('shipping-partners.index', compact('partners'));
    }

    /**
     * Show the form for creating a new shipping partner
     */
    public function create()
    {
        return view('shipping-partners.create');
    }

    /**
     * Store a newly created shipping partner
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        ShippingPartner::create($validated);

        return redirect()->route('shipping-partners.index')
            ->with('success', 'Shipping partner created successfully!');
    }

    /**
     * Display the specified shipping partner
     */
    public function show(ShippingPartner $shippingPartner)
    {
        // Get orders assigned to this partner
        $orders = Order::where('shipping_partner_id', $shippingPartner->id)
            ->with('customer')
            ->latest()
            ->paginate(10);

        // Statistics
        $stats = [
            'total_shipments' => Order::where('shipping_partner_id', $shippingPartner->id)->count(),
            'pending' => Order::where('shipping_partner_id', $shippingPartner->id)
                ->where('shipping_status', 'pending')
                ->count(),
            'dispatched' => Order::where('shipping_partner_id', $shippingPartner->id)
                ->where('shipping_status', 'dispatched')
                ->count(),
            'in_transit' => Order::where('shipping_partner_id', $shippingPartner->id)
                ->where('shipping_status', 'in_transit')
                ->count(),
            'out_for_delivery' => Order::where('shipping_partner_id', $shippingPartner->id)
                ->where('shipping_status', 'out_for_delivery')
                ->count(),
            'delivered' => Order::where('shipping_partner_id', $shippingPartner->id)
                ->where('shipping_status', 'delivered')
                ->count(),
            'failed' => Order::where('shipping_partner_id', $shippingPartner->id)
                ->where('shipping_status', 'failed')
                ->count(),
        ];

        return view('shipping-partners.show', compact('shippingPartner', 'orders', 'stats'));
    }

    /**
     * Show the form for editing the shipping partner
     */
    public function edit(ShippingPartner $shippingPartner)
    {
        return view('shipping-partners.edit', compact('shippingPartner'));
    }

    /**
     * Update the specified shipping partner
     */
    public function update(Request $request, ShippingPartner $shippingPartner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $shippingPartner->update($validated);

        return redirect()->route('shipping-partners.show', $shippingPartner)
            ->with('success', 'Shipping partner updated successfully!');
    }

    /**
     * Remove the specified shipping partner
     */
    public function destroy(ShippingPartner $shippingPartner)
    {
        // Check if partner has orders
        $ordersCount = Order::where('shipping_partner_id', $shippingPartner->id)->count();
        
        if ($ordersCount > 0) {
            return redirect()->route('shipping-partners.index')
                ->with('error', 'Cannot delete shipping partner with existing orders. Please reassign orders first.');
        }

        $shippingPartner->delete();

        return redirect()->route('shipping-partners.index')
            ->with('success', 'Shipping partner deleted successfully!');
    }

    /**
     * Toggle shipping partner active status
     */
    public function toggleStatus(ShippingPartner $shippingPartner)
    {
        $shippingPartner->update(['is_active' => !$shippingPartner->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $shippingPartner->is_active
        ]);
    }
}