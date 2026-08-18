<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('razorpay_payment_id')->nullable()->after('payment_notes');
            $table->string('razorpay_payment_status')->nullable()->after('razorpay_payment_id');
            $table->string('razorpay_payment_method')->nullable()->after('razorpay_payment_status');
            $table->decimal('razorpay_amount', 10, 2)->nullable()->after('razorpay_payment_method');
            $table->timestamp('razorpay_checked_at')->nullable()->after('razorpay_amount');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'razorpay_payment_id',
                'razorpay_payment_status',
                'razorpay_payment_method',
                'razorpay_amount',
                'razorpay_checked_at',
            ]);
        });
    }
};