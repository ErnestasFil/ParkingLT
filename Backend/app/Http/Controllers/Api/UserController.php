<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResourse;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\NewUserResource;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Requests\User\RegisterRequest;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController extends Controller
{
    public function register(RegisterRequest $request)
    {
        return response(new NewUserResource(User::create($request->all())), 201);
    }
    public function login(LoginRequest $request)
    {
        JWTAuth::factory()->setTTL(120);
        $token = JWTAuth::attempt([
            "email" => $request->email,
            "password" => $request->password
        ]);
        if (!empty($token)) {
            $expiresAt = Carbon::now()->addMinutes(120)->toDateTimeString();
            return response(['message' => 'Prisijungta sėkmingai!', 'access_token' => $token, 'token_type' => 'bearer', 'expires_in' => $expiresAt], 200);
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
        $expiresAt = Carbon::now()->addMinutes(120)->toDateTimeString();
        return response(['message' => 'Žetonas atnaujintas', 'token' => $token, 'token_type' => 'bearer', 'expires_in' => $expiresAt], 200);
    }
    public function logout()
    {
        Auth::logout();
        return response()->noContent();
    }
    public function index()
    {
        return response(UserResourse::collection(User::all()), 200);
    }
    public function show(User $user)
    {
        return response(new UserResourse($user), 200);
    }
    public function update(UpdateRequest $request, User $user)
    {
        $user->update($request->all());
        return response(new UserResourse($user), 200);
    }
    public function destroy(User $user)
    {
        $user->delete();
        $token = JWTAuth::parseToken()->getPayload();
        $sub = $token->get('sub');
        if ($user->id == $sub) {
            Auth::invalidate();
        }
        return response('', 204);
    }
}
