<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = [
            'username' => strtolower($request->post('username')),
            'password' => $request->post('password')
        ];
        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return new JsonResponse(['message' => 'Bad credentials'], 401);
        }

        /** @var User $user */
        $user = Auth::user();
        $user->last_logged_in_at = Carbon::now();
        $user->save();

        return new JsonResponse([
            'accessToken' => $token,
            'type' => 'bearer',
            'expiresIn' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
//        $token = auth()->getToken()->get();

//        /** @var JwtToken $jwtToken */
//        $jwtToken = JwtToken::query()->where('token', '=', $token)->first();
//        if ($jwtToken) {
//            $jwtToken->revoked_at = Carbon::now();
//            $jwtToken->save();
//        }
        auth()->logout(true);

        return response()->json(['success' => true]);
    }

    public function me(): JsonResponse
    {
        /** @var User $me */
        $me = Auth::user();
        if (!$me->userConfig) {
            $me->userConfig = $me->createFirstUserConfig();
        }

        return new JsonResponse(User::toApiModel($me));
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string',
        ]);

        /** @var User $me */
        $me = Auth::user();

        if (!Hash::check($validated['currentPassword'], $me->password)) {
            abort(401, 'Incorrect Credentials');
        }

        $me->password = Hash::make($validated['newPassword']);
        $me->save();

        return new JsonResponse(['success' => true]);
    }

    public function updateMe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'userConfig.sideMenuOpen' => 'required|bool',
            'userConfig.homePageRole' => 'nullable|string',
            'userConfig.moneyAppToken' => 'nullable|string',
            'roles' => 'array',
            'roles.*.id' => 'required|int',
            'roles.*.name' => 'required|string',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->name = $validated['name'];
        $user->userConfig->side_menu_open = $validated['userConfig']['sideMenuOpen'];
        $user->userConfig->home_page_role = $validated['userConfig']['homePageRole'];
        $user->userConfig->money_app_token = $validated['userConfig']['moneyAppToken'];
        $roles = Role::query()
                     ->whereIn('id', Collection::make($request['roles'])->map(function ($role) {
                         return $role['id'];
                     }))
                     ->get();
        if ($user->roles->doesntContain(function ($role) {
                return $role->name == Roles::ADMIN_ROLE;
            }) && $roles->contains(function ($role) {
                return $role->name == Roles::ADMIN_ROLE;
            })) {
            $roles = $roles->filter(function ($role) {
                return $role->name != Roles::ADMIN_ROLE;
            });
        }
        $user->syncRoles($roles);

        $user->save();
        $user->userConfig->save();

        return new JsonResponse(User::toApiModel($user));
    }
}
