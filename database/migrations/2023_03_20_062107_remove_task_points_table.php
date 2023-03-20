<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('task_points');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('task_point_id');
        });

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropColumn('task_point_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('task_points', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('family_id')->index();
            $table->string('name');
            $table->integer('points');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('task_point_id')->index()->nullable();
        });

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->integer('task_point_id')->index()->nullable();
        });
    }
};
