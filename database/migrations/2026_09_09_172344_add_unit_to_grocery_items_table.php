<?php

use App\Enums\GroceryItemUnit;
use Illuminate\Database\Migrations\Migration;
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
        Schema::table('grocery_items', function ($table) {
            $table->string('unit')->nullable();
            $table->decimal('default_quantity', 8, 2)->nullable();
        });

        DB::table('grocery_items')->update(['unit' => GroceryItemUnit::NONE->value]);

        Schema::table('grocery_items', function ($table) {
            $table->string('unit')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grocery_items', function ($table) {
            $table->dropColumn(['unit', 'default_quantity']);
        });
    }
};
