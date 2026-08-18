<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Services\ShippingTrackingService;

class TrackShipments extends Command
{
    protected $signature = 'shipments:track';
    protected $description = 'Track all pending shipments';

    public function handle(ShippingTrackingService $service)
    {
        $this->info('Tracking shipments...');

        $results = $service->trackAllPendingShipments();

        $this->info("Total: {$results['total']}, Updated: {$results['updated']}, Failed: {$results['failed']}");
    }
}