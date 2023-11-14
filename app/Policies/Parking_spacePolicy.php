<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Parking_space;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Auth\Access\Response;

class Parking_spacePolicy
{
    public function create(User $user): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator') return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
    public function update(User $user, Parking_space $parkingSpace): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator') return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
    public function delete(User $user, Parking_space $parkingSpace): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator') return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
}
