<?php

namespace App\Http\Resources;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        $token = JWTAuth::parseToken()->getPayload();
        $sub = $token->get('sub');
        $isAdmin = $token->get('role') == 'Administrator';
        $response = [
            'id' => $this->id,
            'date_from' => $this->date_from,
            'date_until' => $this->date_until,
            'fk_Parking_spaceid' => $this->fk_Parking_spaceid
        ];
        if ($isAdmin || $sub == $this->fk_Userid) {
            $response['fk_Userid'] = $this->fk_Userid;
            $response['fk_Privilegeid'] = $this->when($this->fk_Privilegeid !== null, $this->fk_Privilegeid);
            $response['price'] = number_format($this->price, 2, '.', '');
        }

        return $response;
    }
}
