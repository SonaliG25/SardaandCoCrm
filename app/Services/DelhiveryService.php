<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DelhiveryService
{
    protected $apiToken;
    protected $clientId;
    protected $baseUrl;
    protected $mode;
    protected $business;

    public function __construct()
    {
        $this->apiToken = config('services.delhivery.api_token');
        $this->clientId = config('services.delhivery.client_id');
        $this->baseUrl = config('services.delhivery.api_base_url');
        $this->mode = config('services.delhivery.mode');
        $this->business = config('services.delhivery.business');
    }

    /**
     * Create a new shipment with Delhivery
     */
public function createShipment($order)
{
    try {
        // Validate order
        if (!$order->customer) {
            throw new Exception('Order has no customer');
        }

        // Determine payment mode
        $paymentMode = $order->payment_status === 'received' ? 'Prepaid' : 'COD';
        $codAmount = $paymentMode === 'COD' ? ($order->balance_amount ?? $order->amount) : 0;

        // ✅ CRITICAL: Wrap shipment in "shipments" array
        $shipmentData = [
            'shipments' => [
                [
                    'name' => $order->customer->name,
                    'add' => $order->customer->address ?: 'Address not provided',
                    'pin' => $order->customer->pincode ?: '',
                    'city' => $order->customer->city ?: '',
                    'state' => $order->customer->state ?: '',
                    'country' => 'India',
                    'phone' => $order->customer->phone ?: '',
                    'order' => (string)$order->woocommerce_order_id,
                    'payment_mode' => $paymentMode,
                    'cod_amount' => (string)$codAmount,
                    'order_date' => $order->order_date->format('Y-m-d H:i:s'),
                    'total_amount' => (string)$order->amount,
                    'return_pin' => $this->business['pincode'] ?? '',
                    'return_city' => $this->business['city'] ?? '',
                    'return_phone' => $this->business['phone'] ?? '',
                    'return_add' => $this->business['address'] ?? '',
                    'return_state' => $this->business['state'] ?? '',
                    'return_country' => 'India',
                    'products_desc' => $order->product_description ?: 'Garment',
                    'hsn_code' => '',
                    'quantity' => '1',
                    'waybill' => '',
                    'shipment_width' => '10',
                    'shipment_height' => '10',
                    'weight' => '0.5',
                    'seller_add' => $this->business['address'] ?? '',
                    'seller_name' => $this->business['name'] ?? '',
                    'seller_inv' => (string)$order->woocommerce_order_id,
                    'seller_gst_tin' => $this->business['gst'] ?? '',
                    'shipping_mode' => 'Surface',
                    'address_type' => 'home',
                ]
            ]
        ];

        Log::info('Delhivery: Creating shipment', [
            'order_id' => $order->id,
            'shipment_data' => $shipmentData
        ]);

        // Send as form-urlencoded with format and data parameters
        $response = Http::asForm()->withHeaders([
            'Authorization' => 'Token ' . $this->apiToken,
        ])->post($this->baseUrl . 'cmu/create.json', [
            'format' => 'json',
            'data' => json_encode($shipmentData),
        ]);

        $responseBody = $response->body();
        $data = $response->json();
        
        Log::info('Delhivery: Response received', [
            'order_id' => $order->id,
            'status' => $response->status(),
            'body' => $responseBody,
            'parsed' => $data
        ]);

        if ($response->successful() && isset($data['success']) && $data['success'] === false) {
            Log::error('Delhivery: API returned error', [
                'order_id' => $order->id,
                'error' => $data['rmk'] ?? 'Unknown error',
                'response' => $data
            ]);
            
            return [
                'success' => false,
                'message' => $data['rmk'] ?? 'Delhivery API error'
            ];
        }

        if ($response->successful()) {
            $awbNumber = null;

            // Try different response formats
            if (isset($data['packages'][0]['waybill'])) {
                $awbNumber = $data['packages'][0]['waybill'];
            }
            elseif (isset($data['upload_wbn'])) {
                $awbNumber = $data['upload_wbn'];
            }

            if ($awbNumber) {
                // ✅ Update order with AWB
                $order->update([
                    'awb_number' => $awbNumber,
                    'shipping_status' => 'dispatched',
                    'dispatched_date' => now(),
                ]);

                Log::info('Delhivery: Shipment created', [
                    'order_id' => $order->id,
                    'awb' => $awbNumber
                ]);

                return [
                    'success' => true,
                    'awb_number' => $awbNumber,
                    'status' => 'Manifest',
                    'message' => 'Shipment created successfully',
                ];
            }

            return [
                'success' => false,
                'message' => 'AWB not found in response',
            ];
        }

        return [
            'success' => false,
            'message' => 'API request failed: ' . $response->status()
        ];

    } catch (Exception $e) {
        Log::error('Delhivery: Exception', [
            'message' => $e->getMessage(),
            'order_id' => $order->id ?? null,
            'trace' => $e->getTraceAsString()
        ]);

        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// /**
//  * Track shipment by AWB number
//  */
// public function trackShipment($awbNumber)
// {
//     try {
//         Log::info('Delhivery: Tracking shipment', [
//             'awb' => $awbNumber
//         ]);
        
//         $response = Http::withHeaders([
//             'Content-Type' => 'application/json',
//             'Authorization' => 'Token ' . $this->apiToken,
//         ])->get($this->baseUrl . 'v1/packages/json/', [
//             'waybill' => $awbNumber
//         ]);
        
//         if ($response->successful()) {
//             $data = $response->json();
            
//             Log::info('Delhivery: Track response received', [
//                 'awb' => $awbNumber,
//                 'response' => $data
//             ]);
            
//                   if (isset($data['ShipmentData'][0]['Shipment'])) {
//             $shipment = $data['ShipmentData'][0]['Shipment'];
//             $scans = $shipment['Scans'] ?? [];
            
//             // ✅ Get status from nested Status object
//             $currentStatus = $shipment['Status']['Status'] ?? 'Unknown';
            
//             return [
//                 'success' => true,
//                 'awb_number' => $awbNumber,
//                 'status' => $currentStatus,  // ✅ This is "In Transit"
//                 'current_location' => $shipment['Status']['StatusLocation'] ?? '',
//                 'destination' => $shipment['Destination'] ?? '',
//                 'expected_delivery' => $shipment['ExpectedDeliveryDate'] ?? null,
//                 'scans' => $this->formatScans($scans),
//                 'raw_data' => $data
//             ];
//         }
            
//             return [
//                 'success' => false,
//                 'message' => 'No tracking data found for this AWB'
//             ];
//         }
        
//         Log::error('Delhivery: Track shipment failed', [
//             'awb' => $awbNumber,
//             'status' => $response->status(),
//             'response' => $response->body()
//         ]);
        
//         return [
//             'success' => false,
//             'message' => 'Tracking failed: ' . $response->body()
//         ];
        
//     } catch (Exception $e) {
//         Log::error('Delhivery: Track Exception', [
//             'message' => $e->getMessage(),
//             'awb' => $awbNumber
//         ]);
        
//         return [
//             'success' => false,
//             'message' => 'Tracking Error: ' . $e->getMessage()
//         ];
//     }
// }

// /**
//  * Format scan events for display
//  */
// protected function formatScans($scans)
// {
//     $formatted = [];
    
//     if (empty($scans) || !is_array($scans)) {
//         return $formatted;
//     }
    
//     foreach ($scans as $scan) {
//         // ✅ Access nested ScanDetail object
//         $detail = $scan['ScanDetail'] ?? $scan;
        
//         // Skip if no valid datetime
//         if (empty($detail['ScanDateTime'])) {
//             continue;
//         }
        
//         $formatted[] = [
//             'date_time' => $detail['ScanDateTime'],
//             'location' => $detail['ScannedLocation'] ?? 'Unknown',
//             'status' => $detail['Scan'] ?? 'Unknown',
//             'remarks' => $detail['Instructions'] ?? '',
//         ];
//     }
    
//     return $formatted;
// }

/**
 * Track shipment by AWB number
 */
public function trackShipment($awbNumber)
{
    try {
        Log::info('Delhivery: Tracking shipment', [
            'awb' => $awbNumber
        ]);
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Token ' . $this->apiToken,
        ])->get($this->baseUrl . 'v1/packages/json/', [
            'waybill' => $awbNumber
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            
            Log::info('Delhivery: Track response received', [
                'awb' => $awbNumber,
                'response' => $data
            ]);
            
            if (isset($data['ShipmentData'][0]['Shipment'])) {
                $shipment = $data['ShipmentData'][0]['Shipment'];
                $scans = $shipment['Scans'] ?? [];
                
                // Get current status from nested Status object
                $currentStatus = $shipment['Status']['Status'] ?? 'Unknown';
                
                return [
                    'success' => true,
                    'awb_number' => $awbNumber,
                    'status' => $currentStatus,
                    'current_location' => $shipment['Status']['StatusLocation'] ?? '',
                    'destination' => $shipment['Destination'] ?? '',
                    'expected_delivery' => $shipment['ExpectedDeliveryDate'] ?? null,
                    'scans' => $this->formatScans($scans),
                    'raw_data' => $data
                ];
            }
            
            return [
                'success' => false,
                'message' => 'No tracking data found for this AWB'
            ];
        }
        
        Log::error('Delhivery: Track shipment failed', [
            'awb' => $awbNumber,
            'status' => $response->status(),
            'response' => $response->body()
        ]);
        
        return [
            'success' => false,
            'message' => 'Tracking failed: ' . $response->body()
        ];
        
    } catch (Exception $e) {
        Log::error('Delhivery: Track Exception', [
            'message' => $e->getMessage(),
            'awb' => $awbNumber
        ]);
        
        return [
            'success' => false,
            'message' => 'Tracking Error: ' . $e->getMessage()
        ];
    }
}

/**
 * Format scan events for display
 */
protected function formatScans($scans)
{
    $formatted = [];
    
    if (empty($scans) || !is_array($scans)) {
        return $formatted;
    }
    
    foreach ($scans as $scan) {
        // Access nested ScanDetail object
        $detail = $scan['ScanDetail'] ?? $scan;
        
        // Skip if no valid datetime
        if (empty($detail['ScanDateTime'])) {
            continue;
        }
        
        $formatted[] = [
            'date_time' => $detail['ScanDateTime'],
            'location' => $detail['ScannedLocation'] ?? 'Unknown',
            'status' => $detail['Scan'] ?? 'Unknown',
            'remarks' => $detail['Instructions'] ?? '',
        ];
    }
    
    Log::info('Delhivery: Formatted scans', [
        'total' => count($formatted),
        'first_scan' => $formatted[0] ?? null
    ]);
    
    return $formatted;
}
    /**
     * Cancel shipment
     */
    public function cancelShipment($awbNumber)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Token ' . $this->apiToken,
            ])->post($this->baseUrl . 'cmu/cancel/json/', [
                'waybill' => $awbNumber,
                'cancellation' => true
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Shipment cancelled successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Cancellation failed: ' . $response->body()
            ];

        } catch (Exception $e) {
            Log::error('Delhivery: Cancel Exception', [
                'message' => $e->getMessage(),
                'awb' => $awbNumber
            ]);

            return [
                'success' => false,
                'message' => 'Cancel Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Request pickup
     */
    public function requestPickup($pickupDate, $pickupTime = 'morning')
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Token ' . $this->apiToken,
            ])->post($this->baseUrl . 'fm/request/new/', [
                'pickup_location' => [
                    'name' => $this->business['name'],
                    'add' => $this->business['address'],
                    'city' => $this->business['city'],
                    'pin_code' => $this->business['pincode'],
                    'country' => 'India',
                    'phone' => $this->business['phone'],
                ],
                'pickup_date' => $pickupDate,
                'pickup_time' => $pickupTime, // morning, afternoon, evening
                'expected_package_count' => 1
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Pickup request created',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Pickup request failed: ' . $response->body()
            ];

        } catch (Exception $e) {
            Log::error('Delhivery: Pickup Exception', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Pickup Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Map Delhivery status to CRM status
     */
   public function mapStatusToCRM($delhiveryStatus)
{
    $statusMap = [
        // Manifest statuses
        'Manifest' => 'dispatched',
        'Manifested' => 'dispatched',
        
        // Pending statuses
        'Pending' => 'pending',
        'Pickup Pending' => 'pending',
        'PP' => 'pending',
        
        // In Transit statuses
        'UD' => 'in_transit',
        'PC' => 'in_transit',
        'IT' => 'in_transit',
        'In Transit' => 'in_transit',
        'In-Transit' => 'in_transit',
        
        // Out for Delivery
        'OO' => 'out_for_delivery',
        'Out for Delivery' => 'out_for_delivery',
        'Out For Delivery' => 'out_for_delivery',
        
        // Delivered
        'DL' => 'delivered',
        'Delivered' => 'delivered',
        
        // Failed/RTO
        'RT' => 'failed',
        'RTO' => 'failed',
        'RP' => 'failed',
        'CA' => 'failed',
        'Cancelled' => 'failed',
    ];

    // ✅ Return mapped status or default to 'in_transit' (not 'pending')
    return $statusMap[$delhiveryStatus] ?? 'in_transit';
}

    /**
     * Check if API credentials are configured
     */
    public function isConfigured()
    {
        return !empty($this->apiToken) && !empty($this->business['pincode']);
    }
    
}