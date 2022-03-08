<?php

namespace App\Providers;

use App\Models\Goals\Goal;
use App\Models\Goals\GoalDay;
use App\Models\User;
use App\Policies\GoalDayPolicy;
use App\Policies\GoalPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Goal::class => GoalPolicy::class,
        GoalDay::class => GoalDayPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
