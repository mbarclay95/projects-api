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
        Schema::dropIfExists('scheduled_backup_steps');

        Schema::table('scheduled_backups', function (Blueprint $table) {
            $table->dropColumn('full_every_n_days');
            $table->dropColumn('start_time');
            $table->integer('backup_id')->index();
        });

        Schema::table('backup_steps', function (Blueprint $table) {
            $table->string('backup_step_type');
            $table->jsonb('config');
            $table->dropColumn('scheduled_backup_step_id');
            $table->dropColumn('target_id');
            $table->dropColumn('source_dir');
            $table->dropColumn('full_backup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_backups', function (Blueprint $table) {
            $table->integer('start_time');
            $table->integer('full_every_n_days');
            $table->dropColumn('backup_id');
        });


        Schema::create('scheduled_backup_steps', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('name');
            $table->integer('user_id')->index();
            $table->integer('scheduled_backup_id')->index();
            $table->integer('target_id')->index();
            $table->integer('sort');
            $table->string('source_dir');
        });

        Schema::table('backup_steps', function (Blueprint $table) {
            $table->dropColumn('backup_step_type');
            $table->dropColumn('config');
            $table->integer('scheduled_backup_step_id');
            $table->integer('target_id');
            $table->string('source_dir');
            $table->boolean('full_backup');
        });
    }
};
