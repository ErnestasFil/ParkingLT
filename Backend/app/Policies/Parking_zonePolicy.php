<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Parking_zone;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Auth\Access\Response;

class Parking_zonePolicy
{
    public function create(User $user): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator') return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
    public function update(User $user, Parking_zone $parkingZone): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator') return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
    public function delete(User $user, Parking_zone $parkingZone): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator') return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
}
