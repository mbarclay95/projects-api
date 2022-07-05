<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiCrudController extends Controller
{
    protected static string $modelClass;

    protected static bool $getUserEntitiesOnly = true;
    protected static bool $getUserEntityOnly = true;
    protected static bool $updateUserEntityOnly = true;
    protected static bool $destroyUserEntityOnly = true;

    protected static array $indexRules = [];
    protected static array $storeRules = [];
    protected static array $updateRules = [];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validate(static::$indexRules);

        if (static::$getUserEntitiesOnly) {
            if (!$user->hasPermissionTo(static::$modelClass::viewAnyForUserPermission())) {
                throw new AuthenticationException();
            }
            $models = static::$modelClass::getUserEntities($validated, $user);
        } else {
            if (!$user->hasPermissionTo(static::$modelClass::viewAnyPermission())) {
                throw new AuthenticationException();
            }
            $models = static::$modelClass::getEntities($validated);
        }

        return new JsonResponse(static::$modelClass::toApiModels($models));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validate(static::$storeRules);

        if (!$user->hasPermissionTo(static::$modelClass::createPermission())) {
            throw new AuthenticationException();
        }
        $model = static::$modelClass::createEntity($validated, $user);

        return new JsonResponse(static::$modelClass::toApiModel($model));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function show(int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (static::$getUserEntityOnly) {
            if (!$user->hasPermissionTo(static::$modelClass::viewForUserPermission())) {
                throw new AuthenticationException();
            }
            $model = static::$modelClass::getUserEntity($id, $user);
        } else {
            if (!$user->hasPermissionTo(static::$modelClass::viewPermission())) {
                throw new AuthenticationException();
            }
            $model = static::$modelClass::getEntity($id);
        }

        return new JsonResponse(static::$modelClass::toApiModel($model));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validate(static::$updateRules);
        $model = static::$modelClass::query()->where('id', '=', $id)->first();

        if (!$model) {
            throw new NotFoundHttpException();
        }

        if (static::$updateUserEntityOnly) {
            if (!($user->hasPermissionTo(static::$modelClass::updateForUserPermission()) && $user->id === $model->user_id)) {
                throw new AuthenticationException();
            }
            $model = static::$modelClass::updateUserEntity($model, $validated, $user);
        } else {
            if (!$user->hasPermissionTo(static::$modelClass::updatePermission())) {
                throw new AuthenticationException();
            }
            $model = static::$modelClass::updateEntity($model, $validated);
        }

        return new JsonResponse(static::$modelClass::toApiModel($model));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $model = static::$modelClass::query()->where('id', '=', $id)->first();

        if (!$model) {
            throw new NotFoundHttpException();
        }

        if (static::$destroyUserEntityOnly) {
            if (!($user->hasPermissionTo(static::$modelClass::deleteForUserPermission()) && $user->id === $model->user_id)) {
                throw new AuthenticationException();
            }
            static::$modelClass::destroyUserEntity($model, $user);
        } else {
            if (!$user->hasPermissionTo(static::$modelClass::deletePermission())) {
                throw new AuthenticationException();
            }
            static::$modelClass::destroyEntity($model);
        }

        return new JsonResponse(['success' => true]);
    }
}
