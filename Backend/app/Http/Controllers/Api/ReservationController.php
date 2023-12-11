<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Parking_zone;
use App\Models\Parking_space;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Http\Resources\UserReservationResource;
use App\Http\Requests\Reservation\CreateRequest;
use App\Http\Requests\Reservation\UpdateRequest;

class ReservationController extends Controller
{
    public function index(Parking_zone $parking_zone, Parking_space $parking_space)
    {
        if ($parking_space->fk_Parking_zoneid != $parking_zone->id)
            return response(['message' => 'Duomenys nerasti'], 404);
        $token = JWTAuth::parseToken()->getPayload();
        $reservations = $parking_space->Reservation;
        // if ($token->get('role') == 'Administrator') {
        //     
        // } else {
        //     $reservations = $parking_space->Reservation->where('fk_Userid', $token->get('sub'));
        // }
        return response(ReservationResource::collection($reservations), 200);
    }
    public function indexAll(User $user)
    {
        $token = JWTAuth::parseToken()->getPayload();

        if ($token->get('role') == 'Administrator') {
            $reservations = Reservation::all();
        } else {
            $reservations = $user->Reservation;
        }
        return response(UserReservationResource::collection($reservations), 200);
    }
    public function store(Parking_zone $parking_zone, Parking_space $parking_space, CreateRequest $request)
    {
        return response(new ReservationResource(Reservation::create($request->all())), 201);
    }
    public function show(Parking_zone $parking_zone, Parking_space $parking_space, Reservation $reservation)
    {
        if ($parking_space->fk_Parking_zoneid != $parking_zone->id || $reservation->fk_Parking_spaceid != $parking_space->id)
            return response(['message' => 'Duomenys nerasti'], 404);
        return response(new ReservationResource($reservation), 200);
    }
    public function update(Parking_zone $parking_zone, Parking_space $parking_space, Reservation $reservation, UpdateRequest $request)
    {
        $reservation->update($request->all());
        return response(new ReservationResource($reservation), 200);
    }
    public function destroy(Parking_zone $parking_zone, Parking_space $parking_space, Reservation $reservation,)
    {
        if ($parking_space->fk_Parking_zoneid != $parking_zone->id || $reservation->fk_Parking_spaceid != $parking_space->id)
            return response(['message' => 'Duomenys nerasti'], 404);
        $reservation->delete();
        return response('', 204);
    }
}
