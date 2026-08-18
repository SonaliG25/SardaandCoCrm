<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    /**
     * Show settings page
     */
    public function index()
    {
        $user = Auth::user();
        
        $settings = [
            'woocommerce' => [
                'url' => config('services.woocommerce.url'),
                'consumer_key' => config('services.woocommerce.consumer_key'),
                'consumer_secret' => config('services.woocommerce.consumer_secret'),
            ],
            'razorpay' => [
                'key_id' => config('services.razorpay.key_id'),
                'key_secret' => config('services.razorpay.key_secret'),
            ],
            'delhivery' => [
                'api_token' => config('services.delhivery.api_token'),
            ],
            'business' => [
                'name' => env('BUSINESS_NAME', 'SardaandCo'),
                'address' => env('BUSINESS_ADDRESS'),
                'phone' => env('BUSINESS_PHONE'),
                'email' => env('BUSINESS_EMAIL'),
                'gst' => env('BUSINESS_GST'),
            ],
        ];

        return view('settings.index', compact('user', 'settings'));
    }
    
    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
        ]);
        
        $user = Auth::user();
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        
        return back()->with('success', '✅ Profile updated successfully!');
    }
    
    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', '❌ Current password is incorrect!');
        }
        
        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        
        return back()->with('success', '✅ Password updated successfully!');
    }
    
    /**
     * Update business settings
     */
    public function updateBusiness(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_address' => 'nullable|string',
            'business_phone' => 'nullable|string|max:20',
            'business_email' => 'nullable|email|max:255',
            'business_gst' => 'nullable|string|max:50',
        ]);
        
        $this->updateEnvFile([
            'BUSINESS_NAME' => $request->business_name,
            'BUSINESS_ADDRESS' => $request->business_address,
            'BUSINESS_PHONE' => $request->business_phone,
            'BUSINESS_EMAIL' => $request->business_email,
            'BUSINESS_GST' => $request->business_gst,
        ]);
        
        return back()->with('success', '✅ Business settings updated successfully!');
    }
    
    /**
     * Update API settings
     */
    public function updateApiSettings(Request $request)
    {
        $validated = $request->validate([
            'woocommerce_url' => 'nullable|url',
            'woocommerce_consumer_key' => 'nullable|string',
            'woocommerce_consumer_secret' => 'nullable|string',
            'razorpay_key_id' => 'nullable|string',
            'razorpay_key_secret' => 'nullable|string',
            'delhivery_api_token' => 'nullable|string',
        ]);

        // Prepare updates (only include non-empty values)
        $updates = [];
        
        if ($request->filled('woocommerce_url')) {
            $updates['WOOCOMMERCE_URL'] = $validated['woocommerce_url'];
        }
        if ($request->filled('woocommerce_consumer_key')) {
            $updates['WOOCOMMERCE_CONSUMER_KEY'] = $validated['woocommerce_consumer_key'];
        }
        if ($request->filled('woocommerce_consumer_secret')) {
            $updates['WOOCOMMERCE_CONSUMER_SECRET'] = $validated['woocommerce_consumer_secret'];
        }
        if ($request->filled('razorpay_key_id')) {
            $updates['RAZORPAY_KEY_ID'] = $validated['razorpay_key_id'];
        }
        if ($request->filled('razorpay_key_secret')) {
            $updates['RAZORPAY_KEY_SECRET'] = $validated['razorpay_key_secret'];
        }
        if ($request->filled('delhivery_api_token')) {
            $updates['DELHIVERY_API_TOKEN'] = $validated['delhivery_api_token'];
        }

        $this->updateEnvFile($updates);

        // Clear config cache
        Artisan::call('config:clear');

        return back()->with('success', '✅ API settings updated successfully!');
    }

    /**
     * Update .env file with given key-value pairs
     */
    protected function updateEnvFile($data)
    {
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            return;
        }
        
        $envContent = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            // Escape value for .env format
            $value = str_replace('"', '\"', $value ?? '');
            $envValue = "\"{$value}\"";
            
            // Check if key exists
            if (preg_match("/^{$key}=/m", $envContent)) {
                // Update existing
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$envValue}",
                    $envContent
                );
            } else {
                // Add new
                $envContent .= "\n{$key}={$envValue}";
            }
        }

        file_put_contents($envFile, $envContent);
    }

    /**
     * Test API connection
     */
    public function testConnection(Request $request)
    {
        $type = $request->input('type');

        try {
            switch ($type) {
                case 'woocommerce':
                    $result = $this->testWooCommerceConnection();
                    break;

                case 'razorpay':
                    $result = $this->testRazorpayConnection();
                    break;

                case 'delhivery':
                    $result = $this->testDelhiveryConnection();
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid connection type',
                    ], 400);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test WooCommerce connection
     */
    protected function testWooCommerceConnection()
    {
        try {
            $service = app(\App\Services\WooCommerceService::class);
            
            // Try to fetch system status or a single product
            $client = new \GuzzleHttp\Client([
                'base_uri' => config('services.woocommerce.url'),
                'auth' => [
                    config('services.woocommerce.consumer_key'),
                    config('services.woocommerce.consumer_secret')
                ],
                'verify' => false,
            ]);
            
            $response = $client->get('/wp-json/wc/v3/system_status');
            
            if ($response->getStatusCode() === 200) {
                return ['success' => true, 'message' => '✅ WooCommerce connection successful!'];
            }
            
            return ['success' => false, 'message' => '❌ Connection failed'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '❌ ' . $e->getMessage()];
        }
    }

    /**
     * Test Razorpay connection
     */
    protected function testRazorpayConnection()
    {
        try {
            $api = new \Razorpay\Api\Api(
                config('services.razorpay.key_id'),
                config('services.razorpay.key_secret')
            );
            
            // Try to fetch payments (just to test credentials)
            $payments = $api->payment->all(['count' => 1]);
            
            return ['success' => true, 'message' => '✅ Razorpay connection successful!'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '❌ ' . $e->getMessage()];
        }
    }

    /**
     * Test Delhivery connection
     */
    protected function testDelhiveryConnection()
    {
        $apiToken = config('services.delhivery.api_token');
        
        if (!$apiToken) {
            return ['success' => false, 'message' => '❌ API token not configured'];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Token ' . $apiToken,
            ])->get('https://track.delhivery.com/api/v1/packages/json/', [
                'waybill' => 'TEST123'
            ]);

            if ($response->successful() || $response->status() === 404) {
                // 404 is OK - means API is reachable, just waybill doesn't exist
                return ['success' => true, 'message' => '✅ Delhivery connection successful!'];
            }

            return ['success' => false, 'message' => '❌ Connection failed: ' . $response->body()];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '❌ ' . $e->getMessage()];
        }
    }
}