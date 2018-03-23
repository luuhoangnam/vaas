<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitorItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competitor_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item_id')->unique();
            $table->string('title')->nullable();
            $table->string('picture_url')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->unsignedInteger('quantity_sold')->nullable();
            $table->double('price')->nullable();
            $table->unsignedBigInteger('primary_category_id')->nullable();
            $table->string('sku')->nullable()->nullable();
            $table->string('upc')->nullable()->nullable();
            $table->timestamp('start_time')->nullable()->nullable();
            $table->timestamp('end_time')->nullable()->nullable();
            $table->string('status')->nullable();
            // Selling Performance
            $table->unsignedInteger('sold_30d')->nullable(); // <- Item Sold Last 30 Days

            $table->unsignedInteger('competitor_id');
            $table->timestamps();

            $table->foreign('competitor_id')->references('id')->on('competitors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('competitor_items');
    }
}
