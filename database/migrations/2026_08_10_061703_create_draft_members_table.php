<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('draft_members', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('draft_id')->index();
            $table->string('name');
            $table->string('secret')->index();
            $table->integer('pick_position')->nullable();
            $table->unique(['draft_id', 'pick_position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_members');
    }
};
