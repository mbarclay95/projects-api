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
        Schema::create('task_user_configs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('user_id')->index();
            $table->integer('family_id')->index();
            $table->integer('tasks_per_week');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_user_configs');
    }
};
