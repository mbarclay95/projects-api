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
        Schema::create('grocery_list_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('grocery_item_id')->index();
            $table->integer('user_group_id')->index();
            $table->integer('added_by_user_id')->index();
            $table->decimal('quantity', 8, 2)->nullable();
            $table->timestamp('bought_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grocery_list_items');
    }
};
