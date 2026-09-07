<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('family_user', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('family_id')->index();
            $table->integer('user_id')->index();
            $table->unique(['family_id', 'user_id']);
        });

        DB::table('family_user')->insertUsing(
            ['family_id', 'user_id', 'created_at', 'updated_at'],
            DB::table('task_user_configs')
                ->selectRaw('distinct family_id, user_id, now(), now()')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('family_user');
    }
};
