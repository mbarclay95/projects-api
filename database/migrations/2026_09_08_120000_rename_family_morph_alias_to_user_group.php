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
        DB::table('tasks')->where('owner_type', 'family')->update(['owner_type' => 'user-group']);
        DB::table('recurring_tasks')->where('owner_type', 'family')->update(['owner_type' => 'user-group']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tasks')->where('owner_type', 'user-group')->update(['owner_type' => 'family']);
        DB::table('recurring_tasks')->where('owner_type', 'user-group')->update(['owner_type' => 'family']);
    }
};
