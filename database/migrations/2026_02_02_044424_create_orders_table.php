<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_id')->unique(); // #4134
    $table->foreignId('customer_id')->constrained();
    $table->date('order_date');
    $table->decimal('amount', 10, 2);
    
    // WooCommerce Integration
    $table->string('woocommerce_order_id')->nullable()->unique();
    $table->text('woocommerce_raw_data')->nullable(); // JSON storage
    
    // Product Details
    $table->string('product_image')->nullable();
    $table->text('product_description')->nullable();
    
    // Workflow Stages
    $table->foreignId('dye_vendor_id')->nullable()->constrained('vendors');
    $table->enum('dye_status', ['pending', 'received', 'completed', 'na'])->default('pending');
    $table->date('dye_received_date')->nullable();
    
    $table->foreignId('print_vendor_id')->nullable()->constrained('vendors');
    $table->enum('print_status', ['pending', 'received', 'completed', 'na'])->default('pending');
    $table->date('print_received_date')->nullable();
    
    $table->foreignId('emb_vendor_id')->nullable()->constrained('vendors');
    $table->enum('emb_status', ['pending', 'received', 'completed', 'na'])->default('pending');
    $table->date('emb_received_date')->nullable();
    
    $table->foreignId('master_vendor_id')->nullable()->constrained('vendors');
    $table->enum('master_status', ['pending', 'received', 'completed'])->default('pending');
    $table->date('master_received_date')->nullable();
    
    // Shipping Details
    $table->foreignId('shipping_partner_id')->nullable()->constrained('shipping_partners');
    $table->string('awb_number')->nullable();
    $table->date('dispatched_date')->nullable();
    $table->enum('shipping_status', ['pending', 'dispatched', 'in_transit', 'out_for_delivery', 'delivered', 'failed'])->default('pending');
    $table->date('delivered_date')->nullable();
    
    // Overall Status
    $table->enum('order_status', ['new', 'processing', 'dispatched', 'delivered', 'cancelled'])->default('new');
    
    // Payment
    $table->enum('payment_status', ['pending', 'partial', 'received', 'remittance_balance'])->default('pending');
    $table->decimal('paid_amount', 10, 2)->default(0);
    $table->text('payment_notes')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
