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
        Schema::table('task_user_configs', function (Blueprint $table) {
            $table->date('start_date')->change()->nullable(false);
            $table->date('end_date')->change()->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_user_configs', function (Blueprint $table) {
            $table->date('start_date')->change()->nullable();
            $table->date('end_date')->change()->nullable();
        });
    }
};
