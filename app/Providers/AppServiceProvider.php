<?php

namespace App\Providers;

use App\Models\Families\Family;
use App\Models\Tasks\RecurringTask;
use App\Models\Tasks\Task;
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
            'user-group' => Family::class,
            'task' => Task::class,
            'recurring-task' => RecurringTask::class,
        ]);
    }
}
