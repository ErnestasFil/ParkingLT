<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reservation;
use App\Models\Parking_zone;
use App\Models\Parking_space;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Auth\Access\Response;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator' || ($token->get('sub') == $reservation->fk_Userid && $token->get('role') == 'User')) return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
    
    public function update(User $user, Reservation $reservation): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator' || ($token->get('sub') == $reservation->fk_Userid && $token->get('role') == 'User')) return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
    public function create(User $user): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator' || $token->get('role') == 'User') return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
    public function delete(User $user, Reservation $reservation): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator') return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }
}
