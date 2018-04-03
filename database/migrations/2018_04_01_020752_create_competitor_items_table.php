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
            $table->string('title');
            $table->string('picture_url')->nullable();
            $table->unsignedInteger('quantity_sold')->nullable();
            $table->float('price')->nullable();
            $table->unsignedBigInteger('primary_category_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('upc')->nullable();
            $table->string('ean')->nullable();
            $table->string('isbn')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('status');

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
