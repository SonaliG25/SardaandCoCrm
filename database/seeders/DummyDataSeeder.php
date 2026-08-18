<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\ShippingPartner;
use App\Models\Order;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // Create Customers
        $customers = [
            ['name' => 'Rushabh Dhruv', 'email' => 'rushabh@example.com', 'phone' => '9876543210'],
            ['name' => 'Vallaki balakrishna', 'email' => 'vallaki@example.com', 'phone' => '9876543211'],
            ['name' => 'Suresh Nair', 'email' => 'suresh@example.com', 'phone' => '9876543212'],
            ['name' => 'Priya Sharma', 'email' => 'priya@example.com', 'phone' => '9876543213'],
            ['name' => 'Amit Patel', 'email' => 'amit@example.com', 'phone' => '9876543214'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        // Create Vendors
        $vendors = [
            // Dye vendors
            ['name' => 'Shivam Bhai', 'type' => 'dye', 'contact_person' => 'Shivam', 'phone' => '9999999991'],
            ['name' => 'Color Master Dye Works', 'type' => 'dye', 'contact_person' => 'Rajesh', 'phone' => '9999999992'],
            
            // Print vendors
            ['name' => 'The Print Mint', 'type' => 'print', 'contact_person' => 'Sunil', 'phone' => '9999999993'],
            ['name' => 'Digital Print Pro', 'type' => 'print', 'contact_person' => 'Akash', 'phone' => '9999999994'],
            
            // Emb vendors
            ['name' => 'Elite Embroidery', 'type' => 'emb', 'contact_person' => 'Ramesh', 'phone' => '9999999995'],
            ['name' => 'Royal Emb Works', 'type' => 'emb', 'contact_person' => 'Vijay', 'phone' => '9999999996'],
            
            // Masters
            ['name' => 'Raju Master', 'type' => 'master', 'contact_person' => 'Raju', 'phone' => '9999999997'],
            ['name' => 'Sarfraz Master', 'type' => 'master', 'contact_person' => 'Sarfraz', 'phone' => '9999999998'],
        ];

        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }

        // Create Shipping Partners
        $shippingPartners = [
            ['name' => 'Delhivery'],
            ['name' => 'DTDC'],
            ['name' => 'Blue Dart'],
        ];

        foreach ($shippingPartners as $partner) {
            ShippingPartner::create($partner);
        }

        // Create Sample Orders
        $orders = [
            [
                'order_id' => '#4134',
                'customer_id' => 1,
                'order_date' => '2025-11-01',
                'amount' => 8799,
                'dye_vendor_id' => 1,
                'dye_status' => 'received',
                'print_status' => 'na',
                'emb_status' => 'received',
                'master_vendor_id' => 8,
                'master_status' => 'received',
                'shipping_partner_id' => 1,
                'awb_number' => '39461510000232',
                'dispatched_date' => '2025-11-07',
                'shipping_status' => 'delivered',
                'delivered_date' => '2025-11-10',
                'order_status' => 'delivered',
                'payment_status' => 'remittance_balance',
            ],
            [
                'order_id' => '#4135',
                'customer_id' => 2,
                'order_date' => '2025-11-01',
                'amount' => 7299,
                'dye_status' => 'na',
                'print_vendor_id' => 3,
                'print_status' => 'received',
                'emb_status' => 'na',
                'master_vendor_id' => 8,
                'master_status' => 'received',
                'shipping_partner_id' => 1,
                'awb_number' => '39461510000221',
                'dispatched_date' => '2025-11-07',
                'shipping_status' => 'delivered',
                'delivered_date' => '2025-11-11',
                'order_status' => 'delivered',
                'payment_status' => 'remittance_balance',
            ],
            [
                'order_id' => '#4139',
                'customer_id' => 3,
                'order_date' => '2025-11-03',
                'amount' => 8799,
                'dye_vendor_id' => 1,
                'dye_status' => 'received',
                'print_status' => 'na',
                'emb_status' => 'received',
                'master_vendor_id' => 8,
                'master_status' => 'received',
                'shipping_partner_id' => 2,
                'awb_number' => '7X152915904',
                'dispatched_date' => '2025-11-07',
                'shipping_status' => 'delivered',
                'delivered_date' => '2025-11-08',
                'order_status' => 'delivered',
                'payment_status' => 'received',
            ],
            // Add more pending orders
            [
                'order_id' => '#4140',
                'customer_id' => 4,
                'order_date' => now()->format('Y-m-d'),
                'amount' => 6500,
                'dye_vendor_id' => 2,
                'dye_status' => 'pending',
                'print_vendor_id' => 3,
                'print_status' => 'pending',
                'emb_status' => 'na',
                'master_status' => 'pending',
                'order_status' => 'new',
                'payment_status' => 'pending',
            ],
            [
                'order_id' => '#4141',
                'customer_id' => 5,
                'order_date' => now()->format('Y-m-d'),
                'amount' => 9200,
                'dye_vendor_id' => 1,
                'dye_status' => 'received',
                'print_vendor_id' => 4,
                'print_status' => 'pending',
                'emb_vendor_id' => 5,
                'emb_status' => 'pending',
                'master_vendor_id' => 7,
                'master_status' => 'pending',
                'order_status' => 'processing',
                'payment_status' => 'partial',
                'paid_amount' => 5000,
            ],
        ];

        foreach ($orders as $order) {
            Order::create($order);
        }

        $this->command->info('Dummy data seeded successfully!');
    }
}