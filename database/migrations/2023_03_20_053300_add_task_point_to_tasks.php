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
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('task_point')->nullable();
        });

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->integer('task_point')->nullable();
        });

//        /** @var Collection|TaskPoint[] $taskPoints */
//        $taskPoints = TaskPoint::query()->get();
//        /** @var Task[] $tasks */
//        $tasks = Task::query()->whereNotNull('task_point_id')->get();
//        /** @var RecurringTask[] $tasks */
//        $recurringTasks = RecurringTask::query()->whereNotNull('task_point_id')->get();
//
//        foreach ($tasks as $task) {
//            $task->task_point = $taskPoints->where('id', '=', $task->task_point_id)->first()->points;
//            $task->save();
//        }
//        foreach ($recurringTasks as $task) {
//            $task->task_point = $taskPoints->where('id', '=', $task->task_point_id)->first()->points;
//            $task->save();
//        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('task_point');
        });

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropColumn('task_point');
        });
    }
};
