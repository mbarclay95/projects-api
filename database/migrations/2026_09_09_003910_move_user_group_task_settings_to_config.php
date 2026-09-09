<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
        Schema::table('user_groups', function ($table) {
            $table->jsonb('config')->notNull()->default('{}');
        });

        DB::statement("
            update user_groups
            set config = jsonb_build_object(
                'task_strategy', task_strategy,
                'task_points', coalesce(task_points->'points', '[]'::jsonb)
            )
        ");

        Schema::table('user_groups', function ($table) {
            $table->dropColumn('task_strategy');
            $table->dropColumn('task_points');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_groups', function ($table) {
            $table->string('task_strategy')->notNull()->default('per task');
            $table->jsonb('task_points')->nullable();
        });

        DB::statement("
            update user_groups
            set task_strategy = config->>'task_strategy',
                task_points = jsonb_build_object('points', config->'task_points')
        ");

        Schema::table('user_groups', function ($table) {
            $table->dropColumn('config');
        });
    }
};
