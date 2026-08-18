<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ShippingTracking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingTrackingService
{
    /**
     * Track shipment by AWB number
     */
    public function trackShipment($orderId)
    {
        $order = Order::with('shippingPartner')->findOrFail($orderId);
        
        if (!$order->awb_number || !$order->shippingPartner) {
            return ['success' => false, 'error' => 'Missing AWB or shipping partner'];
        }

        $partnerName = strtolower($order->shippingPartner->name);

        switch ($partnerName) {
            case 'delhivery':
                return $this->trackDelhivery($order);
            case 'dtdc':
                return $this->trackDTDC($order);
            case 'blue dart':
            case 'bluedart':
                return $this->trackBlueDart($order);
            default:
                return ['success' => false, 'error' => 'Unsupported shipping partner'];
        }
    }

    /**
     * Track Delhivery shipment
     */
    protected function trackDelhivery($order)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . config('services.delhivery.api_key'),
            ])->get('https://track.delhivery.com/api/v1/packages/json/', [
                'waybill' => $order->awb_number
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['ShipmentData'][0]['Shipment'])) {
                    $shipment = $data['ShipmentData'][0]['Shipment'];
                    $status = $shipment['Status'];
                    
                    // Update order status
                    $this->updateOrderShippingStatus($order, $status['Status'], $status);
                    
                    // Store tracking history
                    if (isset($status['Instructions'])) {
                        foreach ($status['Instructions'] as $instruction) {
                            ShippingTracking::create([
                                'order_id' => $order->id,
                                'awb_number' => $order->awb_number,
                                'status' => $instruction['Status'],
                                'location' => $instruction['Location'] ?? null,
                                'remarks' => $instruction['StatusDetail'] ?? null,
                                'tracked_at' => $instruction['StatusDateTime'],
                                'api_response' => json_encode($instruction)
                            ]);
                        }
                    }
                    
                    return [
                        'success' => true,
                        'status' => $status['Status'],
                        'data' => $shipment
                    ];
                }
            }

            return ['success' => false, 'error' => 'No tracking data found'];

        } catch (\Exception $e) {
            Log::error('Delhivery tracking failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Track DTDC shipment
     */
    protected function trackDTDC($order)
    {
        try {
            $response = Http::withHeaders([
                'API-KEY' => config('services.dtdc.api_key'),
            ])->get('https://api.dtdc.com/api/track', [
                'awb' => $order->awb_number
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['data'])) {
                    $trackingInfo = $data['data'];
                    $status = $trackingInfo['current_status'];
                    
                    // Update order status
                    $this->updateOrderShippingStatus($order, $status, $trackingInfo);
                    
                    // Store tracking history
                    if (isset($trackingInfo['tracking_history'])) {
                        foreach ($trackingInfo['tracking_history'] as $history) {
                            ShippingTracking::updateOrCreate([
                                'order_id' => $order->id,
                                'awb_number' => $order->awb_number,
                                'tracked_at' => $history['date'],
                            ], [
                                'status' => $history['status'],
                                'location' => $history['location'] ?? null,
                                'remarks' => $history['remarks'] ?? null,
                                'api_response' => json_encode($history)
                            ]);
                        }
                    }
                    
                    return [
                        'success' => true,
                        'status' => $status,
                        'data' => $trackingInfo
                    ];
                }
            }

            return ['success' => false, 'error' => 'No tracking data found'];

        } catch (\Exception $e) {
            Log::error('DTDC tracking failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update order shipping status based on courier status
     */
    protected function updateOrderShippingStatus($order, $courierStatus, $fullData = null)
    {
        $statusMap = [
            // Delhivery statuses
            'Dispatched' => 'dispatched',
            'In Transit' => 'in_transit',
            'Out for Delivery' => 'out_for_delivery',
            'Delivered' => 'delivered',
            'RTO' => 'failed',
            'Lost' => 'failed',
            
            // DTDC statuses
            'DISPATCHED' => 'dispatched',
            'IN TRANSIT' => 'in_transit',
            'OUT FOR DELIVERY' => 'out_for_delivery',
            'DELIVERED' => 'delivered',
            'RTO INITIATED' => 'failed',
            
            // Common statuses
            'dispatched' => 'dispatched',
            'in_transit' => 'in_transit',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
        ];

        // $newStatus = $statusMap[trim($courierStatus)] ?? $order->shipping_status;
        
        // $updates = ['shipping_status' => $newStatus];
        
        // if ($newStatus === 'delivered' && !$order->delivered_date) {
        //     $updates['delivered_date'] = now();
        //     $updates['order_status'] = 'delivered';
        // }
        
        $newStatus = $statusMap[trim($courierStatus)] ?? $order->shipping_status;
        
        $updates = ['shipping_status' => $newStatus];
        
        if ($newStatus === 'delivered' && !$order->delivered_date) {
            // Prefer the courier's own delivery timestamp (e.g. Delhivery's StatusDateTime)
            // over the time this background job happened to run.
            $deliveredAt = null;
            if (is_array($fullData) && !empty($fullData['StatusDateTime'])) {
                try {
                    $deliveredAt = \Carbon\Carbon::parse($fullData['StatusDateTime']);
                } catch (\Exception $e) {
                    $deliveredAt = null;
                }
            }

            $updates['delivered_date'] = $deliveredAt ?? now();
            $updates['order_status'] = 'delivered';
        }

        $order->update($updates);
        
        // Create status history
        \App\Models\OrderStatusHistory::create([
            'order_id' => $order->id,
            'stage' => 'shipping',
            'old_status' => $order->shipping_status,
            'new_status' => $newStatus,
            'notes' => 'Auto-updated from shipping API: ' . $courierStatus,
        ]);

        return $order;
    }

    /**
     * Bulk track all pending shipments
     */
    public function trackAllPendingShipments()
    {
        $orders = Order::whereIn('shipping_status', ['dispatched', 'in_transit', 'out_for_delivery'])
            ->whereNotNull('awb_number')
            ->whereNotNull('shipping_partner_id')
            ->get();

        $results = [
            'total' => $orders->count(),
            'updated' => 0,
            'failed' => 0,
        ];

        foreach ($orders as $order) {
            try {
                $result = $this->trackShipment($order->id);
                if ($result['success']) {
                    $results['updated']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                Log::error('Bulk tracking failed for order ' . $order->id . ': ' . $e->getMessage());
                $results['failed']++;
            }
        }

        return $results;
    }
}