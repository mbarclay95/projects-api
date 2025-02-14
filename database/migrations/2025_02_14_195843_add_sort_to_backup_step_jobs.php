<?php

use App\Models\Backups\BackupStepJob;
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
        Schema::table('backup_step_jobs', function (Blueprint $table) {
            $table->integer('sort')->nullable();
        });

        BackupStepJob::query()->update(['sort' => 1]);

        Schema::table('backup_step_jobs', function (Blueprint $table) {
            $table->integer('sort')->change()->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backup_step_jobs', function (Blueprint $table) {
            $table->dropColumn('sort');
        });
    }
};
