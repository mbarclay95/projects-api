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
        Schema::create('grocery_store_item_categories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('user_group_id')->index();
            $table->integer('grocery_store_id')->index();
            $table->integer('grocery_item_id')->index();
            $table->integer('grocery_category_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grocery_store_item_categories');
    }
};
