<?php

use App\Models\Tasks\RecurringTask;
use App\Models\Tasks\Task;
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
            $table->integer('priority')->nullable();
        });

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->integer('priority')->nullable();
        });

        Task::query()->update(['priority' => 0]);
        RecurringTask::query()->withTrashed()->update(['priority' => 0]);

        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('priority')->nullable(false)->change();
        });

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->integer('priority')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('priority');
        });

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
