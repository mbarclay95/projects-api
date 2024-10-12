<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gaming_session_devices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('gaming_device_id')->index();
            $table->integer('gaming_session_id')->index();
            $table->string('name');
            $table->integer('current_turn_order');
            $table->integer('next_turn_order')->nullable();
            $table->string('turn_time_display_mode');
            $table->boolean('skip');
            $table->boolean('has_passed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaming_session_devices');
    }
};
