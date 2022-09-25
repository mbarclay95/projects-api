<?php

use App\Enums\FamilyTaskStrategyEnum;
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

        Schema::table('families', function (Blueprint $table) {
            $table->string('task_strategy')->default(FamilyTaskStrategyEnum::PER_TASK->value);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_points');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('task_point_id');
        });

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropColumn('task_point_id');
        });

        Schema::table('families', function (Blueprint $table) {
            $table->dropColumn('task_strategy');
        });
    }
};
