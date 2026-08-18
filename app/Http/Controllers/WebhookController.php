<?php

namespace App\Http\Controllers;

use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Razorpay webhook
     */
    public function razorpay(Request $request)
    {
        // Get raw payload and signature
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        
        if (!$signature) {
            Log::warning('Razorpay webhook: Missing signature');
            return response()->json(['error' => 'Missing signature'], 400);
        }
        
        $razorpayService = app(RazorpayService::class);
        
        // Verify webhook signature
        if (!$razorpayService->verifyWebhook($payload, $signature)) {
            Log::warning('Razorpay webhook: Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        
        // Parse payload
        $data = json_decode($payload, true);
        $event = $data['event'] ?? null;
        
        Log::info('Razorpay webhook received', [
            'event' => $event,
            'payload' => $data
        ]);
        
        // Process based on event type
        switch ($event) {
            case 'payment.authorized':
            case 'payment.captured':
            case 'payment.failed':
                $paymentData = $data['payload']['payment']['entity'] ?? null;
                
                if ($paymentData) {
                    $result = $razorpayService->processWebhookPayment($paymentData);
                    
                    if ($result['success']) {
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Payment processed'
                        ], 200);
                    }
                }
                break;
                
            case 'refund.created':
                // Handle refund
                $refundData = $data['payload']['refund']['entity'] ?? null;
                if ($refundData) {
                    Log::info('Razorpay refund webhook', ['data' => $refundData]);
                    // TODO: Process refund if needed
                }
                break;
                
            default:
                Log::info('Razorpay webhook: Unhandled event', ['event' => $event]);
        }
        
        return response()->json(['status' => 'ok'], 200);
    }
}