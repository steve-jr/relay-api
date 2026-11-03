<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterBrandRequest;
use App\Http\Requests\Auth\RegisterCreatorRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function registerCreator(RegisterCreatorRequest $request)
    {
        $data = $request->validated();
        $data['role'] = $request->role();
//        var_dump($data);
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['data' => $user->toApiArray()], 201);
    }

    public function registerBrand(RegisterBrandRequest $request)
    {
        $data = $request->validated();
        $data['role'] = $request->role();
        var_dump($data);

        $user = User::create($data);
        Auth::login($user);
//        $request->session()->regenerate();

        return response()->json(['data' => $user->toApiArray()], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required','email'], 'password' => ['required','string']]);
        if (!Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        $request->session()->regenerate();

        return response()->json(['data' => $request->user()->toApiArray()]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['data' => ['logged_out' => true]]);
    }

    public function me(Request $request)
    {
        return response()->json(['data' => $request->user()->toApiArray()]);
    }
}
