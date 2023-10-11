<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParkingSpaceRequest;
use App\Http\Resources\ParkingSpaceResource;
use App\Models\Parking_space;
use App\Models\Parking_zone;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class ParkingSpaceController extends Controller
{
    public function index(Parking_zone $parking_zone)
    {
        return response(ParkingSpaceResource::collection($parking_zone->ParkingSpace), 200);
    }
    public function store(Parking_zone $parking_zone, ParkingSpaceRequest $request)
    {
        $request->merge(["fk_Parking_zoneid" => $parking_zone->id]);
        $polygon = new Polygon([
            new LineString(array_map(function ($point) {
                return new Point($point[0], $point[1]);
            }, $request->location_polygon))
        ], '4326');
        $parking_space = Parking_space::create([
            'fk_Parking_zoneid' => $request->fk_Parking_zoneid,
            'space_number' => $request->space_number,
            'location_polygon' => $polygon,
            'invalid_place' => $request->invalid_place ? 1 : 0,
            'street' => $request->street,
            'information' => $request->information,
        ]);
        return response(new ParkingSpaceResource($parking_space), 201);
    }
    public function show(Parking_zone $parking_zone, Parking_space $parking_space)
    {
        if ($parking_space->fk_Parking_zoneid != $parking_zone->id)
            return response(['message' => 'Duomenys nerasti'], 404);
        return response(new ParkingSpaceResource($parking_space), 200);
    }
    public function update(Parking_zone $parking_zone, Parking_space $parking_space, ParkingSpaceRequest $request)
    {
        if ($parking_space->fk_Parking_zoneid != $parking_zone->id)
            return response(['message' => 'Duomenys nerasti'], 404);
        $request->merge(["fk_Parking_zoneid" => $parking_zone->id]);
        $polygon = new Polygon([
            new LineString(array_map(function ($point) {
                return new Point($point[0], $point[1]);
            }, $request->location_polygon))
        ], '4326');
        $parking_space->update([
            'fk_Parking_zoneid' => $request->fk_Parking_zoneid,
            'space_number' => $request->space_number,
            'location_polygon' => $polygon,
            'invalid_place' => $request->invalid_place ? 1 : 0,
            'street' => $request->street,
            'information' => $request->information,
        ]);
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
