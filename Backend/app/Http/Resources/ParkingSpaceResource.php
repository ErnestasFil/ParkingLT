<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ParkingSpaceResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'parking_zone' => $this->fk_Parking_zoneid,
            'space_number' => $this->space_number,
            'location_polygon' => $this->transformLocationPolygon(),
            'location_srid' => $this->location_polygon->srid,
            'invalid_place' => $this->invalid_place ? true : false,
            'street' => $this->street,
            'information' => $this->information,
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
