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
        Schema::table('targets', function (Blueprint $table) {
            $table->string('host_name')->nullable()->change();
        });

        Schema::table('backup_steps', function (Blueprint $table) {
            $table->text('error_message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->string('host_name')->nullable(false)->change();
        });

        Schema::table('backup_steps', function (Blueprint $table) {
            $table->dropColumn('error_message');
        });
    }
};
