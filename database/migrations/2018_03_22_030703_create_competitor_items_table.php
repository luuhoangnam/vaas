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
            $table->string('sku')->nullable();
            $table->string('upc')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('status')->nullable();
            // Performance
            $table->unsignedInteger('sold_7d')->nullable();
            $table->unsignedInteger('sold_14d')->nullable();
            $table->unsignedInteger('sold_21d')->nullable();
            $table->unsignedInteger('sold_30d')->nullable();
            $table->timestamp('perf_updated_at')->nullable();

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
