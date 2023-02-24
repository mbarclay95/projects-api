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
        Schema::create('log_events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('source');
        });

        Schema::create('log_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('log_event_id')->index();
            $table->jsonb('payload');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_events');
        Schema::dropIfExists('log_items');
    }
};
