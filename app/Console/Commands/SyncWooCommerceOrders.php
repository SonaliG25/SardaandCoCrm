<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WooCommerceService;

class SyncWooCommerceOrders extends Command
{
    protected $signature = 'woocommerce:sync {--limit=10} {--status=processing}';
    protected $description = 'Sync orders from WooCommerce';

    public function handle(WooCommerceService $woocommerceService)
    {
        $this->info('Starting WooCommerce order sync...');

        $limit = $this->option('limit');
        $status = $this->option('status');

        $result = $woocommerceService->syncOrders($limit, 1, $status);

        if ($result['success']) {
            $this->info($result['message']);
            $this->info("Processed: {$result['processed']}");
            $this->info("Failed: {$result['failed']}");
        } else {
            $this->error($result['message']);
        }

        return 0;
    }
}