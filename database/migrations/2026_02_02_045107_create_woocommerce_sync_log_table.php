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
       Schema::create('woocommerce_sync_log', function (Blueprint $table) {
    $table->id();
    $table->string('woocommerce_order_id');
    $table->foreignId('order_id')->nullable()->constrained();
    $table->enum('sync_type', ['create', 'update', 'status_push']);
    $table->enum('status', ['success', 'failed', 'partial']);
    $table->text('message')->nullable();
    $table->text('raw_data')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('woocommerce_sync_log');
    }
};
