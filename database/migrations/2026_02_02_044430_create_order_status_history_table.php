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
    //   Schema::create('order_status_history', function (Blueprint $table) {
    // $table->id();
    // $table->foreignId('order_id')->constrained()->onDelete('cascade');
    // $table->string('stage'); // dye, print, emb, master, shipping
    // $table->string('old_status')->nullable();
    // $table->string('new_status');
    // $table->text('notes')->nullable();
    // $table->foreignId('updated_by')->nullable()->constrained('users');
    // $table->timestamps();
// });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_status_history');
    }
};
