<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            // eBay Attrs
            $table->string('order_id')->unique();
            $table->unsignedInteger('record')->index();
            $table->string('status');
            $table->double('total');
            $table->string('buyer_username');
            $table->string('payment_hold_status');
            $table->string('cancel_status');
            $table->timestamp('created_time')->nullable();
            // Fees
            $table->double('final_value_fee')->nullable();
            $table->double('paypal_fee')->nullable();
            $table->double('other_fee')->nullable();
            // Fulfillment
            $table->double('cog')->nullable();
            $table->double('cashback')->nullable();
            // Default
            $table->timestamps();

            $table->unsignedInteger('account_id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
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
}
