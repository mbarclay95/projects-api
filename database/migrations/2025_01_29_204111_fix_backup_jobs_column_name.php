<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('backup_jobs', function (Blueprint $table) {
            $table->dropColumn('scheduled_backup_id');
            $table->integer('schedule_id')->nullable()->index();
        });

        Schema::table('backup_jobs', function (Blueprint $table) {
            $table->timestamps();
        });

        Schema::table('backup_step_jobs', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backup_jobs', function (Blueprint $table) {
            $table->dropColumn('schedule_id');
            $table->integer('scheduled_backup_id')->nullable()->index();
        });
    }
};
