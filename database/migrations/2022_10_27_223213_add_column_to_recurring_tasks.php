<?php

use App\Models\Tasks\RecurringTask;
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
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->boolean('is_active')->nullable();
        });

        RecurringTask::query()->withTrashed()->update(['is_active' => true]);

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->boolean('is_active')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
