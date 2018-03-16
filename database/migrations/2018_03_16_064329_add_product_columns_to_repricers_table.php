<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductColumnsToRepricersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('repricers', function (Blueprint $table) {
            $table->string('product_type')->after('id');
            $table->string('product_id')->after('product_type');

            $table->index(['product_type', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repricers', function (Blueprint $table) {
            $table->dropIndex('repricers_product_type_product_id_index');
            $table->dropColumn('product_type', 'product_id');
        });
    }
}
