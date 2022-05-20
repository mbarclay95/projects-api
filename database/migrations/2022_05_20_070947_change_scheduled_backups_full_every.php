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
        Schema::table('scheduled_backups', function (Blueprint $table) {
            $table->integer('full_every_n_days');
            $table->boolean('enabled');
        });

        Schema::table('scheduled_backup_steps', function (Blueprint $table) {
            $table->dropColumn('full_every_n_days');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scheduled_backups', function (Blueprint $table) {
            $table->dropColumn('full_every_n_days');
            $table->dropColumn('enabled');
        });

        Schema::table('scheduled_backup_steps', function (Blueprint $table) {
            $table->integer('full_every_n_days');
        });
    }
};
