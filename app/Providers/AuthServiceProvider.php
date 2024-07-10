<?php

namespace App\Providers;

use App\Models\Backups\Backup;
use App\Models\Backups\Target;
use App\Models\Dashboard\Folder;
use App\Policies\BackupPolicy;
use App\Policies\FolderPolicy;
use App\Policies\TargetPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Backup::class => BackupPolicy::class,
        Target::class => TargetPolicy::class,
        Folder::class => FolderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
