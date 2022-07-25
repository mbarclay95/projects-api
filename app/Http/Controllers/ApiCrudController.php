<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiCrudController extends Controller
{
    protected static string $modelClass;

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
        $viewAnyForUser = false;
        $viewAny = false;
        try {
            $viewAnyForUser = $user->hasPermissionTo(static::$modelClass::viewAnyForUserPermission());
        } catch (PermissionDoesNotExist $e) {}
        try {
            $viewAny = $user->hasPermissionTo(static::$modelClass::viewAnyPermission());
        } catch (PermissionDoesNotExist $e) {}

        if (!$viewAnyForUser && !$viewAny) {
            throw new AuthenticationException();
        }
        $models = static::$modelClass::getEntities($validated, $user, $viewAnyForUser);

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
        $createPermission = false;
        try {
            $createPermission = $user->hasPermissionTo(static::$modelClass::createPermission());
        } catch (PermissionDoesNotExist $e) {}

        if (!$createPermission) {
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
        $viewForUser = false;
        $view = false;
        try {
            $viewForUser = $user->hasPermissionTo(static::$modelClass::viewForUserPermission());
        } catch (PermissionDoesNotExist $e) {}
        try {
            $view = $user->hasPermissionTo(static::$modelClass::viewPermission());
        } catch (PermissionDoesNotExist $e) {}

        if (!$viewForUser && !$view) {
            throw new AuthenticationException();
        }
        $model = static::$modelClass::getEntity($id, $user, $viewForUser);

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
        $updateForUser = false;
        $update = false;
        try {
            $updateForUser = $user->hasPermissionTo(static::$modelClass::updateForUserPermission()) && $user->id === $model->user_id;
        } catch (PermissionDoesNotExist $e) {}
        try {
            $update = $user->hasPermissionTo(static::$modelClass::updatePermission());
        } catch (PermissionDoesNotExist $e) {}

        if (!$updateForUser && !$update) {
            throw new AuthenticationException();
        }
        $model = static::$modelClass::updateEntity($model, $validated, $user);

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
        $deleteForUser = false;
        $delete = false;
        try {
            $deleteForUser = $user->hasPermissionTo(static::$modelClass::deleteForUserPermission()) && $user->id === $model->user_id;
        } catch (PermissionDoesNotExist $e) {}
        try {
            $delete = $user->hasPermissionTo(static::$modelClass::deletePermission());
        } catch (PermissionDoesNotExist $e) {}

        if (!$deleteForUser && !$delete) {
            throw new AuthenticationException();
        }
        static::$modelClass::destroyEntity($model, $user);

        return new JsonResponse(['success' => true]);
    }
}
