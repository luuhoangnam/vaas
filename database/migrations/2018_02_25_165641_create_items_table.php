<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item_id')->unique();
            $table->string('title');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('quantity_sold');
            $table->double('price');
            $table->unsignedBigInteger('primary_category_id');
            $table->string('sku')->nullable();
            $table->string('upc')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->string('status');
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
        Schema::dropIfExists('items');
    }
}
