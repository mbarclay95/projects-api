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
        Schema::create('drafts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamp('draft_date')->nullable();
            $table->string('token')->unique();
            $table->string('status')->default('signup');
            $table->integer('total_rounds')->default(1);
            $table->integer('max_participants')->nullable();
            $table->integer('created_by_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drafts');
    }
};
