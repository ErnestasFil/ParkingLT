<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParkingSpace\CreateRequest;
use App\Http\Requests\ParkingSpace\UpdateRequest;
use App\Http\Resources\ParkingSpaceResource;
use App\Models\Parking_space;
use App\Models\Parking_zone;

class ParkingSpaceController extends Controller
{
    public function index(Parking_zone $parking_zone)
    {
        return response(ParkingSpaceResource::collection($parking_zone->ParkingSpace), 200);
    }
    public function store(Parking_zone $parking_zone, CreateRequest $request)
    {
        return response(new ParkingSpaceResource(Parking_space::create($request->all())), 201);
    }
    public function show(Parking_zone $parking_zone, Parking_space $parking_space)
    {
        if ($parking_space->fk_Parking_zoneid != $parking_zone->id)
            return response(['message' => 'Duomenys nerasti'], 404);
        return response(new ParkingSpaceResource($parking_space), 200);
    }
    public function update(Parking_zone $parking_zone, Parking_space $parking_space, UpdateRequest $request)
    {
        $parking_space->update($request->all());
        return response(new ParkingSpaceResource($parking_space), 200);
    }
    public function destroy(Parking_zone $parking_zone, Parking_space $parking_space)
    {
        if ($parking_space->fk_Parking_zoneid != $parking_zone->id)
            return response(['message' => 'Duomenys nerasti'], 404);
        $parking_space->delete();
        return response('', 204);
    }
}
