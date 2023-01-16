<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    use HttpResponses;

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('', 'Credential do not match', 401);
        }

        $user = User::where('email', $request->email)->first();

        return $this->finish($user);
    }

    public function register(StoreUserRequest $request)
    {
        $request->validated($request->all());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return $this->finish($user);
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return $this->success([
            'message' => 'You have successfully been loged out and token is gone.'
        ]);
    }

    private function finish($user)
    {
        $ablilities = ['user'];

        if ($user->id === 1 && $user->email === 'j.beluche@outlook.com') {
            $ablilities = ['user', 'server'];
        }

        return $this->success([
            'user' => $user,
            'token' => $user->createToken('API Token of ' . $user->name, $ablilities)->plainTextToken,
        ]);
    }
}
