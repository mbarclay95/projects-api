<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('draft_members', function (Blueprint $table) {
            $table->timestamp('claimed_at')->nullable();
        });

        DB::table('draft_members')->update(['claimed_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('draft_members', function (Blueprint $table) {
            $table->dropColumn('claimed_at');
        });
    }
};
