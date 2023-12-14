<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ParkingZoneResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'colour' => $this->colour,
            'paying_time' => $this->paying_time,
            'price' => $this->price,
            'location_polygon' => $this->transformLocationPolygon(),
            'location_srid' => $this->location_polygon->srid,
            'information' => $this->information,
            'city' => $this->city,
        ];
    }

    private function transformLocationPolygon()
    {
        $coordinates = $this->location_polygon->getCoordinates();

        if (is_array($coordinates) && count($coordinates) > 0 && is_array($coordinates[0])) {
            return array_map(function ($point) {
                return [$point[0], $point[1]];
            }, $coordinates[0]);
        }

        return $coordinates;
    }
}
