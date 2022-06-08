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
        Schema::table('families', function (Blueprint $table) {
            $table->string('color')->nullable(false)->change();
        });

        Schema::table('task_user_configs', function (Blueprint $table) {
            $table->string('color')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('families', function (Blueprint $table) {
            $table->string('color')->nullable(true)->change();
        });

        Schema::table('task_user_configs', function (Blueprint $table) {
            $table->string('color')->nullable(true)->change();
        });
    }
};
