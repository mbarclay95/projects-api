<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('tasks')->where('owner_type', 'App\\Models\\Tasks\\Family')->update(['owner_type' => 'family']);
        DB::table('tasks')->where('owner_type', 'App\\Models\\Users\\User')->update(['owner_type' => 'user']);

        DB::table('recurring_tasks')->where('owner_type', 'App\\Models\\Tasks\\Family')->update(['owner_type' => 'family']);
        DB::table('recurring_tasks')->where('owner_type', 'App\\Models\\Users\\User')->update(['owner_type' => 'user']);

        DB::table('taggables')->where('taggable_type', 'App\\Models\\Tasks\\Task')->update(['taggable_type' => 'task']);
        DB::table('taggables')->where('taggable_type', 'App\\Models\\Tasks\\RecurringTask')->update(['taggable_type' => 'recurring-task']);

        DB::table('model_has_roles')->where('model_type', 'App\\Models\\Users\\User')->update(['model_type' => 'user']);
        DB::table('model_has_permissions')->where('model_type', 'App\\Models\\Users\\User')->update(['model_type' => 'user']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tasks')->where('owner_type', 'family')->update(['owner_type' => 'App\\Models\\Tasks\\Family']);
        DB::table('tasks')->where('owner_type', 'user')->update(['owner_type' => 'App\\Models\\Users\\User']);

        DB::table('recurring_tasks')->where('owner_type', 'family')->update(['owner_type' => 'App\\Models\\Tasks\\Family']);
        DB::table('recurring_tasks')->where('owner_type', 'user')->update(['owner_type' => 'App\\Models\\Users\\User']);

        DB::table('taggables')->where('taggable_type', 'task')->update(['taggable_type' => 'App\\Models\\Tasks\\Task']);
        DB::table('taggables')->where('taggable_type', 'recurring-task')->update(['taggable_type' => 'App\\Models\\Tasks\\RecurringTask']);

        DB::table('model_has_roles')->where('model_type', 'user')->update(['model_type' => 'App\\Models\\Users\\User']);
        DB::table('model_has_permissions')->where('model_type', 'user')->update(['model_type' => 'App\\Models\\Users\\User']);
    }
};
