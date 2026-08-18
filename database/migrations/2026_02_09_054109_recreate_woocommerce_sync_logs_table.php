<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop the old table completely
        Schema::dropIfExists('woocommerce_sync_logs');
        
        // Create fresh table with all required columns
        Schema::create('woocommerce_sync_logs', function (Blueprint $table) {
            $table->id();
            
            // Sync information
            $table->string('sync_type'); // pull_orders, order_imported, status_updated
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('woocommerce_order_id')->nullable();
            
            // Status and results
            $table->string('status'); // processing, completed, failed, completed_with_errors
            $table->integer('records_processed')->default(0);
            $table->integer('records_failed')->default(0);
            
            // Error and debug info
            $table->text('error_message')->nullable();
            $table->text('message')->nullable();
            $table->longText('raw_data')->nullable();
            
            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('sync_type');
            $table->index('status');
            $table->index('woocommerce_order_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('woocommerce_sync_logs');
    }
};