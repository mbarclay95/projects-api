<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = [
            'username' => $request->post('username'),
            'password' => $request->post('password')
        ];
        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
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
            'userConfig.homePageRole' => 'required|string'
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->name = $validated['name'];
        $user->userConfig->side_menu_open = $validated['userConfig']['sideMenuOpen'];
        $user->userConfig->home_page_role = $validated['userConfig']['homePageRole'];

        $user->save();
        $user->userConfig->save();

        return new JsonResponse(User::toApiModel($user));
    }
}
