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
        Schema::rename('families', 'user_groups');
        DB::statement('alter sequence families_id_seq rename to user_groups_id_seq');
        DB::statement('alter index families_pkey rename to user_groups_pkey');

        Schema::table('user_groups', function ($table) {
            $table->string('scope')->nullable();
        });
        DB::table('user_groups')->update(['scope' => 'tasks']);
        Schema::table('user_groups', function ($table) {
            $table->string('scope')->nullable(false)->change();
        });

        Schema::rename('family_user', 'user_group_user');
        Schema::table('user_group_user', function ($table) {
            $table->renameColumn('family_id', 'user_group_id');
        });
        DB::statement('alter sequence family_user_id_seq rename to user_group_user_id_seq');
        DB::statement('alter index family_user_pkey rename to user_group_user_pkey');
        DB::statement('alter index family_user_family_id_index rename to user_group_user_user_group_id_index');
        DB::statement('alter index family_user_user_id_index rename to user_group_user_user_id_index');
        DB::statement('alter index family_user_family_id_user_id_unique rename to user_group_user_user_group_id_user_id_unique');

        Schema::table('user_group_user', function ($table) {
            $table->string('scope')->nullable();
        });
        DB::statement('update user_group_user p set scope = g.scope from user_groups g where g.id = p.user_group_id');
        Schema::table('user_group_user', function ($table) {
            $table->string('scope')->nullable(false)->change();
            $table->unique(['user_id', 'scope']);
        });

        Schema::table('task_user_configs', function ($table) {
            $table->renameColumn('family_id', 'user_group_id');
        });
        DB::statement('alter index task_user_configs_family_id_index rename to task_user_configs_user_group_id_index');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('alter index task_user_configs_user_group_id_index rename to task_user_configs_family_id_index');
        Schema::table('task_user_configs', function ($table) {
            $table->renameColumn('user_group_id', 'family_id');
        });

        Schema::table('user_group_user', function ($table) {
            $table->dropUnique(['user_id', 'scope']);
            $table->dropColumn('scope');
        });

        DB::statement('alter index user_group_user_user_group_id_user_id_unique rename to family_user_family_id_user_id_unique');
        DB::statement('alter index user_group_user_user_id_index rename to family_user_user_id_index');
        DB::statement('alter index user_group_user_user_group_id_index rename to family_user_family_id_index');
        DB::statement('alter index user_group_user_pkey rename to family_user_pkey');
        DB::statement('alter sequence user_group_user_id_seq rename to family_user_id_seq');
        Schema::table('user_group_user', function ($table) {
            $table->renameColumn('user_group_id', 'family_id');
        });
        Schema::rename('user_group_user', 'family_user');

        Schema::table('user_groups', function ($table) {
            $table->dropColumn('scope');
        });
        DB::statement('alter index user_groups_pkey rename to families_pkey');
        DB::statement('alter sequence user_groups_id_seq rename to families_id_seq');
        Schema::rename('user_groups', 'families');
    }
};
