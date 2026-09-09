<?php

namespace App\Providers;

use App\Models\Grocery\GroceryItem;
use App\Models\Tasks\RecurringTask;
use App\Models\Tasks\Task;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::enforceMorphMap([
            'user' => User::class,
            'user-group' => UserGroup::class,
            'task' => Task::class,
            'recurring-task' => RecurringTask::class,
            'grocery-item' => GroceryItem::class,
        ]);
    }
}
