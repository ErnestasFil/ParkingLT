<?php

namespace App\Policies;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator') return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
    public function view(User $user, User $model): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator' || ($token->get('sub') == $model->id && $token->get('role') == 'User')) return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
    public function update(User $user, User $model): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator' || ($token->get('sub') == $model->id && $token->get('role') == 'User')) return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
    public function delete(User $user, User $model): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator' || ($token->get('sub') == $model->id && $token->get('role') == 'User')) return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
}
