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
            $table->string('notification_email')->nullable();
        });

        Schema::table('event_participants', function (Blueprint $table) {
            $table->string('notification_email')->nullable();
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
            $table->dropColumn('notification_email');
        });

        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropColumn('notification_email');
        });
    }
};
