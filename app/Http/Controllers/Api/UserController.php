<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserLoginRequest;
use App\Http\Resources\NewUserResource;
use App\Http\Requests\UserRegisterRequest;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
    public function refreshToken()
    {
        $token = JWTAuth::getToken();
        if (!$token) {
            throw new BadRequestException('Žetonas nenurodytas!');
        }
        try {
            JWTAuth::factory()->setTTL(120);
            $token = JWTAuth::claims(["aud" => env('JWT_AUDIENCE', 'default')])->refresh(false, false);
        } catch (TokenInvalidException $e) {
            throw new AccessDeniedHttpException('Neteisingas žetonas!');
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 401);
        }
        return response(['token' => $token, 'message' => 'Žetonas atnaujintas'], 200);
    }
    public function logout()
    {
        Auth::logout();
        return response()->noContent();
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
