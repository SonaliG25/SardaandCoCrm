<?php

namespace App\Http\Controllers;

use App\Services\WooCommerceService;
use Illuminate\Http\Request;

class WooCommerceController extends Controller
{
    protected $woocommerceService;

    public function __construct(WooCommerceService $woocommerceService)
    {
        $this->woocommerceService = $woocommerceService;
    }

    /**
     * Show WooCommerce integration dashboard
     */
    public function index()
    {
        return view('woocommerce.index');
    }

    /**
     * Test WooCommerce API connection
     */
    public function testConnection()
    {
        $result = $this->woocommerceService->testConnection();

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Sync orders from WooCommerce
     */
    public function syncOrders(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $status = $request->input('status', 'any');

        $result = $this->woocommerceService->syncOrders($limit, $page, $status);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Handle WooCommerce webhook
     */
    public function webhook(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('X-WC-Webhook-Signature');
        $webhookSecret = config('services.woocommerce.webhook_secret');
        
        $payload = $request->getContent();
        $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $webhookSecret, true));

        if ($signature !== $expectedSignature) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Get webhook topic
        $topic = $request->header('X-WC-Webhook-Topic');
        $data = $request->all();

        // Handle different webhook topics
        switch ($topic) {
            case 'order.created':
            case 'order.updated':
                $this->woocommerceService->createOrUpdateOrder($data);
                break;
        }

        return response()->json(['success' => true]);
    }
}