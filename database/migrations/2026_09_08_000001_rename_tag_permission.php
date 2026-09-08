<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $renames = [
        'App\\Models\\Tasks\\Tag_view_any_for_user' => 'App\\Models\\Tags\\Tag_view_any_for_user',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->renames as $old => $new) {
            $this->rename($old, $new);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->renames as $old => $new) {
            $this->rename($new, $old);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function rename(string $from, string $to): void
    {
        if (DB::table('permissions')->where('name', $to)->exists()) {
            DB::table('permissions')->where('name', $from)->delete();

            return;
        }

        DB::table('permissions')->where('name', $from)->update(['name' => $to]);
    }
};
