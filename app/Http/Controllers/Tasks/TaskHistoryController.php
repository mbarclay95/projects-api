<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\ApiModels\TaskHistoryApiModel;
use App\Models\Tasks\Family;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mbarclay36\LaravelCrud\CrudController;

class TaskHistoryController extends CrudController
{
    protected static string $modelClass = TaskHistoryApiModel::class;

    protected static array $indexRules = [];
    protected static array $storeRules = [];
    protected static array $updateRules = [];

    /**
     * @param Request $request
     * @param Task|null $task
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(Request $request, Task $task = null): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo(Task::viewForUserPermission())) {
            throw new AuthenticationException();
        }
        $familyId = $user->getFamilyIdAttribute();
        if (($task->owner_type == 'family' && $task->owner_id != $familyId) ||
            ($task->owner_type == 'user' && $task->owner_id != $user->id)) {
            throw new AuthenticationException();
        }
        $taskHistories = TaskHistoryApiModel::getEntities(['taskId' => $task->id], $user, true);

        return new JsonResponse(TaskHistoryApiModel::toApiModels($taskHistories));
    }

}
