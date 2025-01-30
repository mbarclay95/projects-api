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
        Schema::rename('scheduled_backups', 'schedules');

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('backup_id');
        });

        Schema::create('backup_schedule', function (Blueprint $table) {
            $table->id();
            $table->integer('backup_id')->index();
            $table->integer('schedule_id')->index();
        });

        Schema::table('backups', function (Blueprint $table) {
            $table->softDeletes();
            $table->dropColumn('started_at');
            $table->dropColumn('completed_at');
            $table->dropColumn('errored_at');
            $table->dropColumn('scheduled_backup_id');
        });

        Schema::create('backup_jobs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('errored_at')->nullable();
            $table->integer('user_id')->index();
            $table->integer('backup_id')->index();
            $table->integer('scheduled_backup_id')->nullable()->index();
        });

        Schema::table('backup_steps', function (Blueprint $table) {
            $table->dropColumn('started_at');
            $table->dropColumn('completed_at');
            $table->dropColumn('errored_at');
            $table->dropColumn('error_message');
            $table->dropColumn('scheduled_backup_id');
        });

        Schema::create('backup_step_jobs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('errored_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('user_id')->index();
            $table->integer('backup_step_id')->index();
            $table->integer('backup_job_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('errored_at')->nullable();
            $table->integer('scheduled_backup_id')->index();
        });

        Schema::rename('schedules', 'scheduled_backups');

        Schema::table('schedules', function (Blueprint $table) {
            $table->integer('backup_id')->index();
        });

        Schema::dropIfExists('backup_schedule');
        Schema::dropIfExists('backup_jobs');

        Schema::table('backup_steps', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('errored_at')->nullable();
            $table->text('error_message')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('scheduled_backup_id')->nullable()->index();
        });

        Schema::dropIfExists('backup_step_jobs');
    }
};
