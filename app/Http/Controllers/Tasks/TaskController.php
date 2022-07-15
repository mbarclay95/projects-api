<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\ApiCrudController;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends ApiCrudController
{
    protected static string $modelClass = Task::class;
    protected static bool $updateUserEntityOnly = false;
    protected static bool $destroyUserEntityOnly = false;
    protected static array $indexRules = [
        'numOfDays' => 'int',
        'ownerType' => 'string',
        'ownerId' => 'int',
        'completedStatus' => 'string',
        'recurringType' => 'string',
        'page' => 'int',
        'pageSize' => 'int',
        'sort' => 'string',
        'sortDir' => 'string',
        'search' => 'string|nullable',
        'tags' => 'array|nullable'
    ];
    protected static array $storeRules = [
        'name' => 'required|string',
        'description' => 'present|string|nullable',
        'ownerType' => 'required|string',
        'ownerId' => 'required|int',
        'recurring' => 'required|bool',
        'dueDate' => 'required|date',
        'frequencyAmount' => 'nullable|int',
        'frequencyUnit' => 'nullable|string',
        'tags' => 'array|present'
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'description' => 'present|string|nullable',
        'ownerType' => 'required|string',
        'ownerId' => 'required|int',
        'recurring' => 'required|bool',
        'dueDate' => 'required|date',
        'frequencyAmount' => 'nullable|int',
        'frequencyUnit' => 'nullable|string',
        'completedAt' => 'nullable|date',
        'tags' => 'array|present'
    ];

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validate(static::$indexRules);
        $query = Task::buildIndexQuery($validated, $user);

        if (array_key_exists('page', $validated) && array_key_exists('pageSize', $validated)) {
            $pagination = $query->paginate($validated['pageSize']);
            $apiModels = Task::toApiModels($pagination->items());
            return new JsonResponse([
                'total' => $pagination->total(),
                'data' => $apiModels
            ]);
        }

        return new JsonResponse(Task::toApiModels($query->get()));
    }
}
