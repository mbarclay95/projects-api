<?php

namespace App\Http\Controllers;

use App\Models\ApiModels\UserApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return new JsonResponse(UserApiModel::fromMeEntity($me));
    }
}
