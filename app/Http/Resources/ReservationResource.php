<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'fk_Userid' => $this->fk_Userid,
            'fk_Parking_spaceid' => $this->fk_Parking_spaceid,
            'fk_Privilegeid' => $this->when($this->fk_Privilegeid !== null, $this->fk_Privilegeid),
            'date_from' => $this->date_from,
            'date_until' => $this->date_until,
            'price' => number_format($this->price, 2, '.', '')
        ];
    }
}
