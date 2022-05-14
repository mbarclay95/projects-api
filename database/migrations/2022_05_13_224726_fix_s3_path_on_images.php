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
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('s3_path');
        });

        Schema::table('site_images', function (Blueprint $table) {
            $table->string('s3_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('s3_path')->nullable();
        });

        Schema::table('site_images', function (Blueprint $table) {
            $table->dropColumn('s3_path');
        });
    }
};
