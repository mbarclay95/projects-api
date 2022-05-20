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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cleared_at')->nullable();
            $table->date('due_date');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('recurring_task_id')->nullable()->index();
            $table->morphs('owner');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
