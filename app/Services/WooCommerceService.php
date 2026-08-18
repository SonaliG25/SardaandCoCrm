<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Customer;
use App\Models\WooCommerceSyncLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use GuzzleHttp\Client;

class WooCommerceService
{
    protected $client;
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerSecret;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.woocommerce.url'), '/');
        $this->consumerKey = config('services.woocommerce.consumer_key');
        $this->consumerSecret = config('services.woocommerce.consumer_secret');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => config('services.woocommerce.timeout', 30),
            'verify' => true,
            'auth' => [$this->consumerKey, $this->consumerSecret]
        ]);
    }

/**
 * Get next order ID using sequence table (prevents duplicates in concurrent syncs)
 */
private function getNextOrderId()
{
    try {
        DB::beginTransaction();
        
        $sequence = DB::table('order_id_sequences')
            ->lockForUpdate()
            ->where('id', 1)
            ->first();
        
        if (!$sequence) {
            DB::rollBack();
            throw new \Exception('Sequence table not initialized');
        }
        
        $nextNumber = $sequence->last_order_number + 1;
        DB::table('order_id_sequences')
            ->where('id', 1)
            ->update(['last_order_number' => $nextNumber]);
        
        DB::commit();
        
        return '#' . $nextNumber;
        
    } catch (\Exception $e) {
        if (DB::transactionLevel() > 0) DB::rollBack();
        Log::error('getNextOrderId failed', ['error' => $e->getMessage()]);
        throw $e;
    }
}
    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $response = $this->client->get('wp-json/wc/v3/system_status');
            
            if ($response->getStatusCode() === 200) {
                return [
                    'success' => true,
                    'message' => 'WooCommerce API connected successfully!',
                    'data' => json_decode($response->getBody(), true)
                ];
            }

            return [
                'success' => false,
                'message' => 'Connection failed with status: ' . $response->getStatusCode()
            ];

        } catch (Exception $e) {
            Log::error('WooCommerce Connection Test Failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Connection Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get orders since a specific time (for incremental sync)
     */
  /**
 * Get orders since a specific time (for incremental sync)
 */
public function getOrdersSince($dateTime)
{
    try {
        $after = $dateTime->toIso8601String();

        $response = $this->client->get('wp-json/wc/v3/orders', [
            'after' => $after,
            'per_page' => 100,
            'status' => 'any'
        ]);

        if ($response->getStatusCode() !== 200) {
            return [
                'success' => false,
                'message' => 'WooCommerce API error: ' . $response->getStatusCode()
            ];
        }

        $orders = json_decode($response->getBody(), true);

        return [
            'success' => true,
            'orders' => $orders,
            'count' => count($orders),
            'message' => 'Fetched ' . count($orders) . ' orders since ' . $after
        ];
    } catch (\Exception $e) {
        Log::error('Failed to fetch orders since', [
            'date_time' => $dateTime,
            'error' => $e->getMessage()
        ]);

        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

    /**
     * Sync orders from WooCommerce
     */
    public function syncOrders($limit = 50, $page = 1, $status = 'any')
    {
        try {
            $syncLog = WooCommerceSyncLog::create([
                'sync_type' => 'pull_orders',
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $processedCount = 0;
            $errorCount = 0;
            $errors = [];
            $currentPage = $page;
            $hasMorePages = true;

            while ($hasMorePages) {
                $response = $this->client->get('/wp-json/wc/v3/orders', [
                    'query' => [
                        'per_page' => $limit,
                        'page' => $currentPage,
                        'status' => $status,
                        'orderby' => 'date',
                        'order' => 'desc'
                    ]
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new Exception('API returned status: ' . $response->getStatusCode());
                }

                $wcOrders = json_decode($response->getBody(), true);

                if (empty($wcOrders)) {
                    $hasMorePages = false;
                    break;
                }

                foreach ($wcOrders as $wcOrder) {
                    try {
                        $this->createOrUpdateOrder($wcOrder);
                        $processedCount++;
                    } catch (Exception $e) {
                        $errorCount++;
                        $errors[] = [
                            'wc_order_id' => $wcOrder['id'],
                            'error' => $e->getMessage()
                        ];
                        Log::error('WooCommerce Order Sync Error', [
                            'wc_order_id' => $wcOrder['id'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $totalPages = (int) $response->getHeader('X-WP-TotalPages')[0] ?? 1;
                
                if ($currentPage >= $totalPages) {
                    $hasMorePages = false;
                } else {
                    $currentPage++;
                    usleep(500000);
                }
            }

            $syncLog->update([
                'status' => $errorCount > 0 ? 'completed_with_errors' : 'completed',
                'records_processed' => $processedCount,
                'records_failed' => $errorCount,
                'error_message' => $errorCount > 0 ? json_encode($errors) : null,
                'completed_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "Synced {$processedCount} orders successfully" . ($errorCount > 0 ? " ({$errorCount} failed)" : ""),
                'processed' => $processedCount,
                'failed' => $errorCount,
                'errors' => $errors
            ];

        } catch (Exception $e) {
            Log::error('WooCommerce Sync Orders Failed', [
                'error' => $e->getMessage()
            ]);

            if (isset($syncLog)) {
                $syncLog->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }

            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create or update order from WooCommerce data
     */
  public function createOrUpdateOrder($wcOrder)
{
    $existingOrder = Order::where('woocommerce_order_id', $wcOrder['id'])->first();
    if ($existingOrder) {
        Log::info('Order already synced, skipping', [
            'wc_order_id' => $wcOrder['id'],
            'order_id' => $existingOrder->order_id
        ]);
        return $existingOrder;
    }

    try {
        $customerEmail = $wcOrder['billing']['email'] ?? 'guest_' . $wcOrder['id'] . '@woocommerce.local';
        $wcCustomerId = $wcOrder['customer_id'];
        
        $customer = null;
        if (!empty($wcCustomerId) && $wcCustomerId != 0) {
            $customer = Customer::where('woocommerce_customer_id', $wcCustomerId)->first();
        }
        if (!$customer) {
            $customer = Customer::where('email', $customerEmail)->first();
        }

        $wcCity = $wcOrder['billing']['city'] ?? null;
        $wcState = $wcOrder['billing']['state'] ?? null;
        $wcPincode = $wcOrder['billing']['postcode'] ?? null;
        
        $parsedData = null;
        if ((empty($wcCity) || empty($wcState) || empty($wcPincode)) && !empty($wcOrder['billing']['address_1'])) {
            $fullAddress = implode(', ', array_filter([
                $wcOrder['billing']['address_1'] ?? '',
                $wcOrder['billing']['address_2'] ?? '',
            ]));
            $parsedData = $this->parseIndianAddress($fullAddress);
        }

        $customerData = [
            'name' => trim(($wcOrder['billing']['first_name'] ?? '') . ' ' . ($wcOrder['billing']['last_name'] ?? '')),
            'email' => $customerEmail,
            'phone' => $wcOrder['billing']['phone'] ?? null,
            'address' => implode(', ', array_filter([
                $wcOrder['billing']['address_1'] ?? '',
                $wcOrder['billing']['address_2'] ?? '',
                $wcOrder['billing']['city'] ?? '',
                $wcOrder['billing']['state'] ?? '',
                $wcOrder['billing']['postcode'] ?? '',
            ])),
            'city' => $wcCity ?: ($parsedData['city'] ?? null),
            'state' => $wcState ?: ($parsedData['state'] ?? null),
            'pincode' => $wcPincode ?: ($parsedData['pincode'] ?? null),
            'woocommerce_customer_id' => $wcCustomerId,
        ];
        
        if ($customer) {
            $customer->update($customerData);
        } else {
            $customer = Customer::create($customerData);
        }

        $productImagePath = null;
        if (!empty($wcOrder['line_items'][0]['image']['src'])) {
            $productImagePath = $this->downloadProductImage(
                $wcOrder['line_items'][0]['image']['src'],
                $wcOrder['id']
            );
        }

        $orderStatus = $this->mapWooCommerceStatus($wcOrder['status'] ?? 'pending');

        $paymentGateway = 'other';
        if (!empty($wcOrder['payment_method'])) {
            $wcPaymentMethod = strtolower($wcOrder['payment_method']);
            $paymentGateway = match($wcPaymentMethod) {
                'razorpay' => 'razorpay',
                'cod' => 'cod',
                'bacs' => 'bank_transfer',
                'cheque' => 'cheque',
                default => 'other'
            };
        }

        $paymentStatus = ($paymentGateway === 'cod') ? 'pending' : ($wcOrder['date_paid'] ? 'received' : 'pending');

        $razorpayPaymentId = null;
        if (isset($wcOrder['meta_data'])) {
            foreach ($wcOrder['meta_data'] as $meta) {
                if ($meta['key'] === '_razorpay_payment_id') {
                    $razorpayPaymentId = $meta['value'];
                    break;
                }
            }
        }

        $awbNumber = null;
        $shippingStatus = 'pending';
        if (isset($wcOrder['meta_data'])) {
            foreach ($wcOrder['meta_data'] as $meta) {
                if (in_array($meta['key'], ['_delhivery_awb', '_awb_number', '_tracking_number', 'awb_number', 'tracking_number'])) {
                    $awbNumber = $meta['value'];
                    if (!empty($awbNumber)) $shippingStatus = 'dispatched';
                    break;
                }
            }
        }

        $delhiveryPartner = \App\Models\ShippingPartner::where('name', 'like', '%Delhivery%')
            ->where('is_active', true)->first();

        // ✅ GET UNIQUE ID WITH SEQUENCE TABLE
        $orderId = $this->getNextOrderId();

        // Create order
        $order = Order::create([
            'order_id' => $orderId,
            'woocommerce_order_id' => $wcOrder['id'],
            'customer_id' => $customer->id,
            'order_date' => $wcOrder['date_created'],
            'amount' => (float) $wcOrder['total'],
            'paid_amount' => (float) ($wcOrder['date_paid'] ? $wcOrder['total'] : 0),
            'payment_status' => $paymentStatus,
            'payment_gateway' => $paymentGateway,
            'order_status' => $orderStatus,
            'product_description' => $this->getProductDescription($wcOrder['line_items']),
            'product_image' => $productImagePath,
            'woocommerce_order_number' => $wcOrder['number'] ?? $wcOrder['id'],
            'razorpay_payment_id' => $razorpayPaymentId,
            'shipping_partner_id' => $delhiveryPartner ? $delhiveryPartner->id : null,
            'awb_number' => $awbNumber,
            'shipping_status' => $shippingStatus,
            'dispatched_date' => $awbNumber ? now() : null,
        ]);

        $this->syncOrderProducts($order, $wcOrder['line_items']);

        if ($awbNumber && $delhiveryPartner) {
            $this->fetchDelhiveryTracking($order, $awbNumber);
        }

        if ($paymentGateway === 'razorpay' && $paymentStatus === 'received') {
            $this->autoCheckRazorpayPayment($order, $razorpayPaymentId);
        }

        WooCommerceSyncLog::create([
            'sync_type' => 'order_imported',
            'order_id' => $order->id,
            'woocommerce_order_id' => $wcOrder['id'],
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        Log::info('WooCommerce Order Created', [
            'wc_order_id' => $wcOrder['id'],
            'order_id' => $orderId,
            'customer' => $customer->name
        ]);

        return $order;

    } catch (\Exception $e) {
        Log::error('createOrUpdateOrder failed', [
            'wc_order_id' => $wcOrder['id'],
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

    protected function downloadProductImage($imageUrl, $orderId)
    {
        // ✅ Just return the WooCommerce image URL - don't download!
        if (!empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return $imageUrl;
        }
        
        return null;
    }

    protected function getProductDescription($lineItems)
    {
        $descriptions = [];
        foreach ($lineItems as $item) {
            $descriptions[] = $item['name'] . ' (Qty: ' . $item['quantity'] . ')';
        }
        return implode(', ', $descriptions);
    }

    /**
     * FIX #3: Map WooCommerce order status including ON-HOLD
     */
    protected function mapWooCommerceStatus($wcStatus)
    {
        $statusMap = [
            'pending'                  => 'new',
            'processing'               => 'processing',
            'on-hold'                  => 'new',
            'completed'                => 'delivered',
            'cancelled'                => 'cancelled',
            'refunded'                 => 'refunded',
            'failed'                   => 'failed',
            'wc-rto-pay-pending'       => 'rto_pay_pending',
            'rto-pay-pending'          => 'rto_pay_pending',
            'wc-exchange-received'     => 'exchange_received',
            'exchange-request-received'=> 'exchange_received',
            'wc-exchange-completed'    => 'exchange_completed',
            'exchange-request-complete'=> 'exchange_completed',
            'wc-refund-requested'      => 'refund_requested',
            'refund-requested'         => 'refund_requested',
            'wc-refund-approved'       => 'refund_approved',
            'refund-approved'          => 'refund_approved',
            'wc-refund-cancelled'      => 'refund_cancelled',
            'refund-cancelled'         => 'refund_cancelled',
        ];

        return $statusMap[$wcStatus] ?? 'new';
    }

    public function updateOrderStatus(Order $order, $status)
    {
        try {
            if (!$order->woocommerce_order_id) {
                return [
                    'success' => false,
                    'message' => 'Order not linked to WooCommerce'
                ];
            }

            $wcStatus = $this->mapCRMStatusToWooCommerce($status);

            $response = $this->client->put('/wp-json/wc/v3/orders/' . $order->woocommerce_order_id, [
                'json' => [
                    'status' => $wcStatus
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                WooCommerceSyncLog::create([
                    'sync_type' => 'status_updated',
                    'order_id' => $order->id,
                    'woocommerce_order_id' => $order->woocommerce_order_id,
                    'status' => 'completed',
                    'started_at' => now(),
                    'completed_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'WooCommerce order status updated'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update WooCommerce status'
            ];

        } catch (Exception $e) {
            Log::error('WooCommerce Status Update Failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Update Error: ' . $e->getMessage()
            ];
        }
    }

    protected function mapCRMStatusToWooCommerce($crmStatus)
    {
        $statusMap = [
            'new'                => 'processing',
            'processing'         => 'processing',
            'dispatched'         => 'processing',
            'delivered'          => 'completed',
            'cancelled'          => 'cancelled',
            'refunded'           => 'refunded',
            'failed'             => 'failed',
            'rto_pay_pending'    => 'rto-pay-pending',
            'exchange_received'  => 'exchange-request-received',
            'exchange_completed' => 'exchange-request-complete',
            'refund_requested'   => 'refund-requested',
            'refund_approved'    => 'refund-approved',
            'refund_cancelled'   => 'refund-cancelled',
        ];

        return $statusMap[$crmStatus] ?? 'processing';
    }

    public function addTrackingInfo(Order $order)
    {
        try {
            if (!$order->woocommerce_order_id || !$order->awb_number) {
                return [
                    'success' => false,
                    'message' => 'Missing WooCommerce order ID or AWB number'
                ];
            }

            $trackingUrl = $order->shippingPartner 
                ? $order->shippingPartner->tracking_url . $order->awb_number
                : '';

            $note = "Your order has been dispatched!\n\n";
            $note .= "Courier: " . ($order->shippingPartner->name ?? 'N/A') . "\n";
            $note .= "AWB/Tracking Number: " . $order->awb_number . "\n";
            if ($trackingUrl) {
                $note .= "Track your order: " . $trackingUrl;
            }

            $response = $this->client->post('/wp-json/wc/v3/orders/' . $order->woocommerce_order_id . '/notes', [
                'json' => [
                    'note' => $note,
                    'customer_note' => true
                ]
            ]);

            if ($response->getStatusCode() === 201) {
                return [
                    'success' => true,
                    'message' => 'Tracking info added to WooCommerce order'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to add tracking info'
            ];

        } catch (Exception $e) {
            Log::error('WooCommerce Add Tracking Failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Add Tracking Error: ' . $e->getMessage()
            ];
        }
    }

    public function getOrder($woocommerceOrderId)
    {
        try {
            $response = $this->client->get('/wp-json/wc/v3/orders/' . $woocommerceOrderId);

            if ($response->getStatusCode() === 200) {
                return [
                    'success' => true,
                    'data' => json_decode($response->getBody(), true)
                ];
            }

            return [
                'success' => false,
                'message' => 'Order not found'
            ];

        } catch (Exception $e) {
            Log::error('WooCommerce Get Order Failed', [
                'wc_order_id' => $woocommerceOrderId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Get Order Error: ' . $e->getMessage()
            ];
        }
    }
    
    protected function autoCheckRazorpayPayment($order, $razorpayPaymentId = null)
    {
        try {
            $razorpayService = app(\App\Services\RazorpayService::class);
            
            if (!$razorpayService->isConfigured()) {
                return;
            }

            if ($razorpayPaymentId) {
                $result = $razorpayService->fetchPayment($razorpayPaymentId);
                
                if ($result['success']) {
                    $order->update([
                        'razorpay_payment_id' => $razorpayPaymentId,
                        'razorpay_payment_status' => $result['status'],
                        'razorpay_payment_method' => $result['method'],
                        'razorpay_amount' => $result['amount'],
                        'razorpay_checked_at' => now(),
                    ]);
                }
            } else {
                $razorpayService->checkPaymentStatus($order);
            }
        } catch (\Exception $e) {
            Log::warning('Auto Razorpay check failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function assignDefaultShippingPartner($order)
    {
        try {
            if ($order->shipping_partner_id) {
                return;
            }

            $shippingPartner = \App\Models\ShippingPartner::where('name', 'like', '%Delhivery%')
                ->where('is_active', true)
                ->first();

            if ($shippingPartner) {
                $order->update([
                    'shipping_partner_id' => $shippingPartner->id,
                    'shipping_status' => 'pending',
                ]);

                Log::info('Default shipping partner assigned', [
                    'order_id' => $order->id,
                    'partner' => 'Delhivery'
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to assign default shipping partner', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function fetchDelhiveryTracking($order, $awbNumber)
    {
        try {
            $delhiveryService = app(\App\Services\DelhiveryService::class);
            
            if (!$delhiveryService->isConfigured()) {
                return;
            }

            $result = $delhiveryService->trackShipment($awbNumber);

            if ($result['success']) {
                $shippingStatus = $delhiveryService->mapStatusToCRM($result['status']);
                
                $order->update([
                    'shipping_status' => $shippingStatus,
                    'delivered_date' => $shippingStatus === 'delivered' ? now() : null,
                ]);

                if (!empty($result['scans'])) {
                    foreach ($result['scans'] as $scan) {
                        \App\Models\ShippingTracking::updateOrCreate(
                            [
                                'order_id' => $order->id,
                                'event_time' => $scan['time'],
                            ],
                            [
                                'awb_number' => $awbNumber,
                                'status' => $scan['status'],
                                'location' => $scan['location'],
                                'remarks' => $scan['remarks'] ?? null,
                                'raw_data' => json_encode($scan),
                            ]
                        );
                    }
                }

                Log::info('Tracking fetched from Delhivery', [
                    'order_id' => $order->id,
                    'awb' => $awbNumber,
                    'status' => $shippingStatus
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Delhivery tracking', [
                'order_id' => $order->id,
                'awb' => $awbNumber,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Parse Indian address to extract pincode, city, and state
     */
    protected function parseIndianAddress($addressString)
    {
        // Extract pincode (6 digits)
        preg_match('/\b(\d{6})\b/', $addressString, $pinMatches);
        $pincode = $pinMatches[1] ?? null;
        
        // Extract state code (2 capital letters near the end)
        preg_match('/\b([A-Z]{2})\b/', $addressString, $stateMatches);
        $stateCode = $stateMatches[1] ?? null;
        
        // Map state codes to full names
        $stateMap = [
            'AP' => 'Andhra Pradesh',
            'AR' => 'Arunachal Pradesh',
            'AS' => 'Assam',
            'BR' => 'Bihar',
            'CG' => 'Chhattisgarh',
            'GA' => 'Goa',
            'GJ' => 'Gujarat',
            'HR' => 'Haryana',
            'HP' => 'Himachal Pradesh',
            'JH' => 'Jharkhand',
            'KA' => 'Karnataka',
            'KL' => 'Kerala',
            'MP' => 'Madhya Pradesh',
            'MH' => 'Maharashtra',
            'MN' => 'Manipur',
            'ML' => 'Meghalaya',
            'MZ' => 'Mizoram',
            'NL' => 'Nagaland',
            'OD' => 'Odisha',
            'OR' => 'Odisha',
            'PB' => 'Punjab',
            'RJ' => 'Rajasthan',
            'SK' => 'Sikkim',
            'TN' => 'Tamil Nadu',
            'TS' => 'Telangana',
            'TR' => 'Tripura',
            'UP' => 'Uttar Pradesh',
            'UK' => 'Uttarakhand',
            'WB' => 'West Bengal',
            'DL' => 'Delhi',
            'AN' => 'Andaman and Nicobar',
            'CH' => 'Chandigarh',
            'DN' => 'Dadra and Nagar Haveli',
            'DD' => 'Daman and Diu',
            'LD' => 'Lakshadweep',
            'PY' => 'Puducherry',
            'JK' => 'Jammu and Kashmir',
            'LA' => 'Ladakh',
        ];
        
        $stateFull = isset($stateCode) && isset($stateMap[$stateCode]) 
                     ? $stateMap[$stateCode] 
                     : null;
        
        // Try to extract city (word before state code or pincode)
        $city = null;
        if ($pincode) {
            // Get text before pincode, split by comma, take last part
            $beforePincode = substr($addressString, 0, strpos($addressString, $pincode));
            $parts = array_filter(array_map('trim', explode(',', $beforePincode)));
            
            // City is usually in the last 2-3 parts
            if (count($parts) >= 2) {
                $city = $parts[count($parts) - 2]; // Second last part is often city
            }
        }
        
        Log::info('Address parsed', [
            'original' => $addressString,
            'pincode' => $pincode,
            'state' => $stateFull,
            'city' => $city
        ]);
        
        return [
            'pincode' => $pincode,
            'state' => $stateFull,
            'city' => $city,
        ];
    }

    /**
     * Sync order products from line items
     */
    protected function syncOrderProducts($order, $lineItems)
    {
        $existingProductIds = [];
        
        foreach ($lineItems as $item) {
            // Check if product already exists
            $existing = OrderProduct::where('order_id', $order->id)
                ->where('wc_line_item_id', $item['id'])
                ->first();

            if ($existing) {
                // ✅ Only update product info, NEVER touch workflow statuses
                $existing->update([
                    'product_name'  => $item['name'],
                    'product_sku'   => $item['sku'] ?? null,
                    'quantity'      => $item['quantity'],
                    'price'         => $item['price'],
                    'product_image' => $item['image']['src'] ?? null,
                ]);
                $existingProductIds[] = $existing->id;
            } else {
                // New product — create with default pending statuses
                $new = OrderProduct::create([
                    'order_id'       => $order->id,
                    'wc_line_item_id'=> $item['id'],
                    'product_name'   => $item['name'],
                    'product_sku'    => $item['sku'] ?? null,
                    'quantity'       => $item['quantity'],
                    'price'          => $item['price'],
                    'product_image'  => $item['image']['src'] ?? null,
                    'dye_status'     => 'pending',
                    'print_status'   => 'pending',
                    'emb_status'     => 'pending',
                    'master_status'  => 'pending',
                ]);
                $existingProductIds[] = $new->id;
            }
        }
        
        // Remove line items deleted from WooCommerce
        OrderProduct::where('order_id', $order->id)
            ->whereNotIn('id', $existingProductIds)
            ->delete();
    }

    /**
     * Parse customer from WooCommerce order
     */
    private function parseCustomer($woOrder)
    {
        $billing = $woOrder['billing'] ?? [];
        $email = $billing['email'] ?? null;
        $phone = $billing['phone'] ?? null;

        // Find existing customer
        $customer = Customer::where('email', $email)
            ->orWhere('phone', $phone)
            ->first();

        if (!$customer) {
            // Parse address
            $fullAddress = implode(', ', array_filter([
                $billing['address_1'] ?? '',
                $billing['address_2'] ?? '',
            ]));
            
            $parsedData = $this->parseIndianAddress($fullAddress);

            $customer = Customer::create([
                'name' => trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? '')),
                'email' => $email,
                'phone' => $phone,
                'address' => implode(', ', array_filter([
                    $billing['address_1'] ?? '',
                    $billing['address_2'] ?? '',
                    $billing['city'] ?? '',
                    $billing['state'] ?? '',
                    $billing['postcode'] ?? '',
                ])),
                'city' => $billing['city'] ?? $parsedData['city'] ?? null,
                'state' => $billing['state'] ?? $parsedData['state'] ?? null,
                'pincode' => $billing['postcode'] ?? $parsedData['pincode'] ?? null,
                'country' => $billing['country'] ?? 'IN',
                'woocommerce_customer_id' => $woOrder['customer_id'] ?? null,
            ]);
        }

        return $customer;
    }

    /**
     * Map WooCommerce payment status to CRM
     */
    private function mapPaymentStatus($woStatus, $paymentMethod = 'other')
    {
        if ($woStatus === 'completed') {
            return 'received';
        }
        if ($woStatus === 'processing' && $paymentMethod === 'cod') {
            return 'pending';
        }
        if ($woStatus === 'processing') {
            return 'received';
        }
        return 'pending';
    }

    /**
     * Extract product description from order
     */
    private function extractProductDescription($woOrder)
    {
        if (empty($woOrder['line_items'])) {
            return null;
        }

        $items = array_map(function($item) {
            return $item['name'] . ' (Qty: ' . $item['quantity'] . ')';
        }, $woOrder['line_items']);

        return implode(', ', $items);
    }

    /**
     * Extract product image from order
     */
    private function extractProductImage($woOrder)
    {
        if (empty($woOrder['line_items'][0]['image']['src'])) {
            return null;
        }

        return $woOrder['line_items'][0]['image']['src'];
    }

    /**
     * Sync single order from WooCommerce with duplicate detection
     */
    public function syncSingleOrder($woocommerceOrderId)
    {
        try {
            // ✅ CHECK IF ALREADY SYNCED
            $existingOrder = Order::where('woocommerce_order_id', $woocommerceOrderId)->first();
            if ($existingOrder) {
                Log::info('WooCommerce Order already synced', [
                    'wc_order_id' => $woocommerceOrderId,
                    'crm_order_id' => $existingOrder->order_id
                ]);
                return [
                    'success' => true,
                    'message' => 'Order already synced',
                    'order_id' => $existingOrder->id,
                    'action' => 'skipped'
                ];
            }

            // Fetch from WooCommerce API
            $response = $this->client->get("/orders/{$woocommerceOrderId}");
            
            if (empty($response)) {
                return ['success' => false, 'message' => 'Order not found in WooCommerce'];
            }

            $woOrder = json_decode($response->getBody(), true);

            // ✅ TRANSACTION WITH LOCK - Generate unique ID
            DB::beginTransaction();
            try {
                // Double-check inside transaction
                $existingOrder = Order::lockForUpdate()
                    ->where('woocommerce_order_id', $woocommerceOrderId)
                    ->first();
                
                if ($existingOrder) {
                    DB::rollBack();
                    return [
                        'success' => true,
                        'message' => 'Order already synced (detected in transaction)',
                        'order_id' => $existingOrder->id,
                        'action' => 'skipped'
                    ];
                }

                // Generate unique order ID with lock
                $lastOrder = Order::lockForUpdate()->latest('id')->first();
                $orderNumber = $lastOrder ? (int) str_replace('#', '', $lastOrder->order_id) + 1 : 4134;
                $orderId = '#' . $orderNumber;

                // Parse customer
                $customer = $this->parseCustomer($woOrder);
                
                // Determine payment status
                $paymentStatus = $this->mapPaymentStatus(
                    $woOrder['status'] ?? 'pending',
                    $woOrder['payment_method'] ?? 'other'
                );

                // Create order
                $order = Order::create([
                    'woocommerce_order_id' => $woocommerceOrderId,
                    'customer_id' => $customer->id,
                    'order_id' => $orderId,
                    'order_date' => $woOrder['date_created'],
                    'amount' => (float) $woOrder['total'],
                    'paid_amount' => (float) $woOrder['total'],
                    'payment_status' => $paymentStatus,
                    'payment_gateway' => $woOrder['payment_method'] ?? 'other',
                    'order_status' => 'processing',
                    'product_description' => $this->extractProductDescription($woOrder),
                    'product_image' => $this->extractProductImage($woOrder),
                    'shipping_partner_id' => 1, // Default Delhivery
                    'remark' => 'Synced from WooCommerce',
                    'dye_status' => 'pending',
                    'print_status' => 'pending',
                    'emb_status' => 'pending',
                    'master_status' => 'pending',
                ]);

                // Create order products
                if (!empty($woOrder['line_items'])) {
                    foreach ($woOrder['line_items'] as $item) {
                        OrderProduct::create([
                            'order_id' => $order->id,
                            'wc_line_item_id' => $item['id'],
                            'product_name' => $item['name'] ?? 'Product',
                            'product_sku' => $item['sku'] ?? null,
                            'quantity' => (int) $item['quantity'],
                            'price' => (float) $item['price'],
                            'product_image' => $item['image']['src'] ?? null,
                            'dye_status' => 'pending',
                            'print_status' => 'pending',
                            'emb_status' => 'pending',
                            'master_status' => 'pending',
                        ]);
                    }
                }

                DB::commit();

                Log::info('WooCommerce Order Synced Successfully', [
                    'wc_order_id' => $woocommerceOrderId,
                    'crm_order_id' => $order->id,
                    'order_number' => $orderId,
                    'customer' => $customer->name
                ]);

                return [
                    'success' => true,
                    'message' => 'Order synced successfully',
                    'order_id' => $order->id,
                    'order_number' => $orderId,
                    'action' => 'created'
                ];

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('WooCommerce Order Sync Error', [
                'wc_order_id' => $woocommerceOrderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
