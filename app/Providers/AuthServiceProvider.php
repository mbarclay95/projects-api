<?php

namespace App\Providers;

use App\Models\Backups\Backup;
use App\Models\Backups\Target;
use App\Models\Dashboard\Folder;
use App\Models\Dashboard\Site;
use App\Models\Dashboard\SiteImage;
use App\Models\Goals\Goal;
use App\Models\Goals\GoalDay;
use App\Models\User;
use App\Policies\BackupPolicy;
use App\Policies\FolderPolicy;
use App\Policies\GoalDayPolicy;
use App\Policies\GoalPolicy;
use App\Policies\SiteImagePolicy;
use App\Policies\SitePolicy;
use App\Policies\TargetPolicy;
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
        Goal::class => GoalPolicy::class,
        GoalDay::class => GoalDayPolicy::class,
        Backup::class => BackupPolicy::class,
        Target::class => TargetPolicy::class,
        Folder::class => FolderPolicy::class,
        Site::class => SitePolicy::class,
        SiteImage::class => SiteImagePolicy::class,
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
