<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class RazorpayService
{
    protected $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );
    }

    /**
     * Check payment status for an order
     */
    public function checkPaymentStatus(Order $order)
    {
        try {
            // Search payments by order amount and timeframe
            $payments = $this->api->payment->all([
                'count' => 20,
                'from' => strtotime($order->order_date . ' -1 day'),
                'to' => strtotime($order->order_date . ' +1 day'),
            ]);

            foreach ($payments->items as $payment) {
                // Try to match by WooCommerce order ID in notes
                $notes = (array) $payment->notes;
                $wcOrderId = $notes['woocommerce_order_id'] 
                    ?? $notes['order_id'] 
                    ?? null;

                // Also try matching by amount
                $paymentAmount = $payment->amount / 100;
                $orderAmount = (float) $order->amount;

                if ($wcOrderId == $order->woocommerce_order_id || 
                    abs($paymentAmount - $orderAmount) < 0.01) {
                    return $this->processPayment($order, $payment);
                }
            }

            return [
                'success' => false,
                'message' => 'No matching payment found'
            ];

        } catch (\Exception $e) {
            Log::error('Razorpay check failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Fetch payment by Razorpay Payment ID
     */
    public function fetchPayment($paymentId)
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'amount' => $payment->amount / 100,
                'status' => $payment->status,
                'method' => $payment->method,
                'email' => $payment->email,
                'contact' => $payment->contact,
                'created_at' => date('Y-m-d H:i:s', $payment->created_at),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process and update order with payment data
     */
   protected function processPayment(Order $order, $payment)
{
    $status = $payment->status;
    $amount = $payment->amount / 100;

    // Map Razorpay status to CRM status
    $paymentStatus = $this->mapStatus($status);

    $oldStatus = $order->payment_status;

    // Update order with Razorpay details
    $order->update([
        'payment_status' => $paymentStatus,
        'paid_amount' => $status === 'captured' ? $amount : $order->paid_amount,
        'payment_notes' => "Razorpay Payment ID: {$payment->id} | Method: {$payment->method} | Status: {$status}",
        
        // NEW: Add Razorpay specific fields
        'razorpay_payment_id' => $payment->id,
        'razorpay_payment_status' => $status,
        'razorpay_payment_method' => $payment->method,
        'razorpay_amount' => $amount,
        'razorpay_checked_at' => now(),
    ]);

    // Log status history
    if ($oldStatus !== $paymentStatus) {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'stage' => 'payment',
            'old_status' => $oldStatus,
            'new_status' => $paymentStatus,
            'notes' => "Auto-updated from Razorpay: {$payment->id} ({$payment->method})",
            'updated_by' => null,
        ]);
    }

    return [
        'success' => true,
        'payment_id' => $payment->id,
        'status' => $status,
        'crm_status' => $paymentStatus,
        'amount' => $amount,
        'method' => $payment->method,
    ];
}

    /**
     * Map Razorpay status to CRM payment status
     */
    protected function mapStatus($razorpayStatus)
    {
        $map = [
            'captured' => 'received',
            'authorized' => 'received',
            'created' => 'pending',
            'failed' => 'pending',
            'refunded' => 'refunded',
        ];

        return $map[$razorpayStatus] ?? 'pending';
    }

    /**
     * Check if configured
     */
    public function isConfigured()
    {
        return !empty(config('services.razorpay.key_id')) 
            && !empty(config('services.razorpay.key_secret'));
    }
    
    /**
 * Verify webhook signature
 */
public function verifyWebhook($payload, $signature)
{
    try {
        $this->api->utility->verifyWebhookSignature(
            $payload,
            $signature,
            config('services.razorpay.webhook_secret')
        );
        return true;
    } catch (\Exception $e) {
        Log::error('Razorpay webhook verification failed', [
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Process webhook payment event
 */
public function processWebhookPayment($paymentData)
{
    try {
        $paymentId = $paymentData['id'];
        $status = $paymentData['status'];
        $amount = $paymentData['amount'] / 100;
        
        // Get order ID from notes
        $notes = $paymentData['notes'] ?? [];
        $wcOrderId = $notes['woocommerce_order_id'] ?? $notes['order_id'] ?? null;
        
        if (!$wcOrderId) {
            Log::warning('Razorpay webhook: No WooCommerce order ID in payment notes', [
                'payment_id' => $paymentId
            ]);
            return ['success' => false, 'message' => 'No order ID in notes'];
        }
        
        // Find order
        $order = Order::where('woocommerce_order_id', $wcOrderId)->first();
        
        if (!$order) {
            Log::warning('Razorpay webhook: Order not found', [
                'payment_id' => $paymentId,
                'wc_order_id' => $wcOrderId
            ]);
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        // Update order
        $paymentStatus = $this->mapStatus($status);
        $oldStatus = $order->payment_status;
        
        $order->update([
            'payment_status' => $paymentStatus,
            'paid_amount' => $status === 'captured' ? $amount : $order->paid_amount,
            'payment_notes' => "Razorpay: {$paymentId} | {$paymentData['method']} | {$status}",
            'razorpay_payment_id' => $paymentId,
            'razorpay_payment_status' => $status,
            'razorpay_payment_method' => $paymentData['method'],
            'razorpay_amount' => $amount,
            'razorpay_checked_at' => now(),
        ]);
        
        // Log status history
        if ($oldStatus !== $paymentStatus) {
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'stage' => 'payment',
                'old_status' => $oldStatus,
                'new_status' => $paymentStatus,
                'notes' => "Webhook: Razorpay {$paymentId} ({$paymentData['method']})",
                'updated_by' => null,
            ]);
        }
        
        Log::info('Razorpay webhook processed', [
            'order_id' => $order->id,
            'payment_id' => $paymentId,
            'status' => $status
        ]);
        
        return [
            'success' => true,
            'order_id' => $order->id,
            'payment_status' => $paymentStatus
        ];
        
    } catch (\Exception $e) {
        Log::error('Razorpay webhook processing failed', [
            'error' => $e->getMessage(),
            'payment_id' => $paymentData['id'] ?? 'unknown'
        ]);
        
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
}