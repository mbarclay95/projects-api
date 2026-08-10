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
        Schema::create('draft_picks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('draft_id')->index();
            $table->integer('draft_member_id')->index();
            $table->integer('draft_team_id')->index();
            $table->integer('pick_number');
            $table->boolean('made_by_admin')->default(false);
            $table->unique(['draft_id', 'draft_team_id']);
            $table->unique(['draft_id', 'pick_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_picks');
    }
};
