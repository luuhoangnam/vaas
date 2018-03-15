<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            # Job ID
            $table->increments('id');

            # Job Creator
            $table->unsignedInteger('creator_id');

            # Status
            $table->string('status');
            $table->string('queue_job_id')->nullable();

            # SetUp
            $table->unsignedInteger('account_id');

            # Link to Product (soft link)
            $table->string('product_type'); // Amazon / Walmart / HomeDepot / ...
            $table->string('product_id'); //  ASIN / ...

            # Madatory Fields for Item
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('quantity');

            $table->unsignedInteger('primary_category_id');
            $table->unsignedInteger('condition_id');

            $table->string('country');
            $table->string('currency');
            $table->string('duration');
            $table->binary('pictures');
            $table->string('site');

            # Pricing
            $table->double('cost_of_goods');
            $table->boolean('tax');
            $table->double('final_value_rate');
            $table->double('margin'); // As known as: Profit Rate

            # Profiles
            $table->string('payment_profile_id');
            $table->string('shipping_profile_id');
            $table->string('return_profile_id');

            # Optional
            $table->string('sku')->nullable();
            $table->string('location')->nullable();
            $table->binary('attributes')->nullable(); // Additional attributes will be put in here

            # Returned
            $table->string('item_id')->nullable();
            $table->double('fee')->nullable();
            $table->binary('errors')->nullable(); // Can be warning/error depends on eBay

            # Timestamps
            $table->timestamps();
            $table->softDeletes();

            # Foreign Keys
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('jobs');
    }
}
