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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('url');
            $table->string('s3_path')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('show');
            $table->integer('sort');
            $table->integer('folder_id')->index();
            $table->integer('user_id')->index();
            $table->integer('site_image_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sites');
    }
};
