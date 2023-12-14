<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParkingZone\CreateRequest;
use App\Http\Requests\ParkingZone\UpdateRequest;
use App\Http\Resources\ParkingZoneResource;
use App\Models\Parking_zone;

class ParkingZoneController extends Controller
{
    public function index()
    {
        return response(ParkingZoneResource::collection(Parking_zone::all()), 200);
    }
    public function store(CreateRequest $request)
    {
        return response(new ParkingZoneResource(Parking_zone::create($request->all())), 201);
    }
    public function show(Parking_zone $parking_zone)
    {
        return response(new ParkingZoneResource($parking_zone), 200);
    }
    public function update(UpdateRequest $request, Parking_zone $parking_zone)
    {
        $parking_zone->update($request->all());
        return response(new ParkingZoneResource($parking_zone), 200);
    }
    public function destroy(Parking_zone $parking_zone)
    {
        $parking_zone->delete();
        return response('', 204);
    }
}
