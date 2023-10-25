<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\NewUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        $user = User::create([
            "name" => $request->name,
            "surname" => $request->surname,
            "email" => $request->email,
            "phone" => $request->phone,
            "password" => Hash::make($request->password),
            "balance" => 0,
            "role" => 2
        ]);
        return response(new NewUserResource($user), 201);
    }
    public function login(UserLoginRequest $request)
    {
        JWTAuth::factory()->setTTL(120);
        $token = JWTAuth::attempt([
            "email" => $request->email,
            "password" => $request->password
        ]);
        if (!empty($token)) {
            return response(['token' => $token, 'message' => 'Prisijungta sėkmingai!'], 200);
        }
        return response(['message' => 'Blogi prisijungimo duomenys!'], 401);
    }
    public function index()
    {
        return response(User::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
