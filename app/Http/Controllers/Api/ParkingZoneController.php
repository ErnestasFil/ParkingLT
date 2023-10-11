<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParkingZoneRequest;
use App\Http\Resources\ParkingZoneResource;
use App\Models\Parking_zone;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class ParkingZoneController extends Controller
{
    public function index()
    {
        return response(ParkingZoneResource::collection(Parking_zone::all()), 200);
    }
    public function store(ParkingZoneRequest $request)
    {
        $polygon = new Polygon([
            new LineString(array_map(function ($point) {
                return new Point($point[0], $point[1]);
            }, $request->location_polygon))
        ], '4326');
        $parking_zone = Parking_zone::create([
            'name' => $request->name,
            'colour' => $request->colour,
            'paying_time' => $request->paying_time,
            'price' => $request->price,
            'location_polygon' => $polygon,
            'information' => $request->information,
            'city' => $request->city
        ]);
        return response(new ParkingZoneResource($parking_zone), 201);
    }
    public function show(Parking_zone $parking_zone)
    {
        return response(new ParkingZoneResource($parking_zone), 200);
    }
    public function update(ParkingZoneRequest $request, Parking_zone $parking_zone)
    {
        $polygon = new Polygon([
            new LineString(array_map(function ($point) {
                return new Point($point[0], $point[1]);
            }, $request->location_polygon))
        ], '4326');
        $parking_zone->update([
            'name' => $request->name,
            'colour' => $request->colour,
            'paying_time' => $request->paying_time,
            'price' => $request->price,
            'location_polygon' => $polygon,
            'information' => $request->information,
            'city' => $request->city
        ]);
        return response(new ParkingZoneResource($parking_zone), 200);
    }
    public function destroy(Parking_zone $parking_zone)
    {
        $parking_zone->delete();
        return response('', 204);
    }
}
