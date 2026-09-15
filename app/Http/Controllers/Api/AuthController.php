<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Setting;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use HttpResponses;

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->safe()->only('name', 'email', 'password'));

        // Every user carries one row of display preferences.
        Setting::create(['user_id' => $user->id]);

        $user->forceFill(['signed_in' => true])->save();

        return $this->success('User was successfully registered', [
            'user' => new UserResource($user),
            'token' => $this->issueToken($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Credentials do not match', null, 401);
        }

        $user = Auth::user();
        $user->forceFill(['signed_in' => true])->save();

        return $this->success('User was successfully logged in', [
            'user' => new UserResource($user),
            'token' => $this->issueToken($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->forceFill(['signed_in' => false])->save();

        return $this->success('Successfully logged out');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(null, new UserResource($request->user()));
    }

    private function issueToken(User $user): string
    {
        return $user->createToken('API Token of '.$user->name)->plainTextToken;
    }
}
