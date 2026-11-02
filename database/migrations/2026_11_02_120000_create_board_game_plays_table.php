<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('board_game_plays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->string('game_type');
            $table->string('name')->nullable();
            $table->string('status')->default('in_progress');
            $table->jsonb('state')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('board_game_plays');
    }
};
