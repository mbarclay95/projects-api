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
        Schema::create('gaming_sessions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->string('turn_order_type');
            $table->integer('current_turn');
            $table->boolean('allow_turn_passing');
            $table->boolean('skip_after_passing');
            $table->boolean('pause_at_beginning_of_round');
            $table->boolean('is_paused');
            $table->integer('turn_limit_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaming_sessions');
    }
};
