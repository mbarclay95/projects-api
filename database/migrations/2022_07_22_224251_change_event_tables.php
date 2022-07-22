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
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('limit_participants')->default(false);
        });
        Schema::table('event_participants', function (Blueprint $table) {
            $table->boolean('is_going')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('limit_participants');
        });
        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropColumn('is_going');
        });
    }
};
