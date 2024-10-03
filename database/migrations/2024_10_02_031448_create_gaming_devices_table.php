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
        Schema::create('gaming_devices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('device_communication_id');
            $table->dateTime('last_seen');
            $table->string('temp_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaming_devices');
    }
};
