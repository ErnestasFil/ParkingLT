<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Parking_space;
use App\Models\Parking_zone;
use App\Models\Privilege;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Parking_zone $parking_zone, Parking_space $parking_space)
    {
        if ($parking_space->fk_Parking_zoneid != $parking_zone->id)
            return response(['message' => 'Duomenys nerasti'], 404);
        return response(ReservationResource::collection($parking_space->Reservation), 200);
    }
    public function store(Parking_zone $parking_zone, Parking_space $parking_space, ReservationRequest $request)
    {
        if ($parking_space->fk_Parking_zoneid != $parking_zone->id)
            return response(['message' => 'Duomenys nerasti'], 404);
        $request->merge(["fk_Parking_spaceid" => $parking_space->id]);
        $price = ceil($request->time / $parking_zone->paying_time) * $parking_zone->price;
        if ($request->fk_Privilegeid != null) {
            $privilege = Privilege::findOrFail($request->fk_Privilegeid);
            $price -= $price * ($privilege->discount / 100);
        }
        $reservation = Reservation::create([
            'fk_Userid' => $request->fk_Userid,
            'fk_Parking_spaceid' => $request->fk_Parking_spaceid,
            'fk_Privilegeid' => $request->fk_Privilegeid != null ? $request->fk_Privilegeid : null,
            'date_from' => Carbon::now()->toDateTimeString(),
            'date_until' => Carbon::now()->addMinutes(ceil($request->time / $parking_zone->paying_time) * $parking_zone->paying_time)->toDateTimeString(),
            'price' => number_format($price, 2, '.', '')
        ]);
        return response(new ReservationResource($reservation), 201);
    }
    public function show(Parking_zone $parking_zone, Parking_space $parking_space, Reservation $reservation)
    {
        if ($parking_space->fk_Parking_zoneid != $parking_zone->id || $reservation->fk_Parking_spaceid != $parking_space->id)
            return response(['message' => 'Duomenys nerasti'], 404);
        return response(new ReservationResource($reservation), 200);
    }
    public function update(Parking_zone $parking_zone, Parking_space $parking_space, Reservation $reservation, ReservationRequest $request)
    {
        $price = ceil($request->time / $parking_zone->paying_time) * $parking_zone->price;
        if ($reservation->fk_Privilegeid != null) {
            $price -= $price * ($reservation->Privilege->discount / 100);
        }
        $dateFrom = Carbon::parse($reservation->date_from);
        $reservation->update([
            'date_until' => $dateFrom->addMinutes(ceil($request->time / $parking_zone->paying_time) * $parking_zone->paying_time)->toDateTimeString(),
            'price' => number_format($price, 2, '.', '')
        ]);
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
