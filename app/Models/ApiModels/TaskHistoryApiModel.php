<?php

namespace App\Models\ApiModels;

use App\Repositories\Tasks\TaskHistoriesRepository;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\Traits\IsApiModel;

class TaskHistoryApiModel extends Model
{
    use IsApiModel;

    protected static string $repositoryClass = TaskHistoriesRepository::class;

    protected static array $apiModelAttributes = ['id', 'completed_at', 'completed_by_name'];
}
