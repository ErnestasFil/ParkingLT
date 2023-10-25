<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator' || $token->get('sub') == $reservation->fk_Userid) return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reservation $reservation): Response
    {
        $token = JWTAuth::parseToken()->getPayload();
        if ($token->get('role') == 'Administrator' || $token->get('sub') == $reservation->fk_Userid) return Response::allow();
        return Response::deny('Priėjimas negalimas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reservation $reservation): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reservation $reservation): bool
    {
        return true;
    }
}
