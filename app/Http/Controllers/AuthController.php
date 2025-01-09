<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginUserRequest $request)
    {

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Credential do not match', null, 401);
        }

        $user = User::where('email', $request->email)->first();

        return $this->success('User was succesfully loged in', [
            'user' => $user,
            'token' => $user->createToken('API Token of ' . $user->name)->plainTextToken,
        ]);
    }

    public function register(StoreUserRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return $this->success([
            'user' => $user,
            'token' => $user->createToken('API Token of ' . $user->name)->plainTextToken,
        ], 'User was succesfully registered');
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->success("Succesfully loged out!");
    }
}
