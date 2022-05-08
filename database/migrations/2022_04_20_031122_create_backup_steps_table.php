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
        Schema::create('backup_steps', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->integer('user_id')->index();
            $table->integer('target_id')->index();
            $table->integer('backup_id')->index();
            $table->integer('scheduled_backup_id')->index()->nullable();
            $table->integer('scheduled_backup_step_id')->index()->nullable();
            $table->string('source_dir');
            $table->boolean('full_backup');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('errored_at')->nullable();
            $table->integer('sort');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backup_steps');
    }
};
