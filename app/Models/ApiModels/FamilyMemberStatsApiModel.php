<?php

namespace App\Models\ApiModels;

use App\Repositories\Tasks\FamilyMemberStatsRepository;
use Mbarclay36\LaravelCrud\Traits\HasApiModel;
use Mbarclay36\LaravelCrud\Traits\HasCrudPermissions;
use Mbarclay36\LaravelCrud\Traits\HasRepository;

class FamilyMemberStatsApiModel
{
    use HasApiModel, HasRepository, HasCrudPermissions;

    protected static array $apiModelAttributes = ['id', 'name', 'topTasks', 'totalTasks', 'totalExpectedPoints', 'totalEarnedPoints'];

    protected static function getRepositoryClass(): string
    {
        return FamilyMemberStatsRepository::class;
    }
}
