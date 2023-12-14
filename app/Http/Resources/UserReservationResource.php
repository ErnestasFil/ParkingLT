<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Parking_space;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Resources\Json\JsonResource;

class UserReservationResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        $token = JWTAuth::parseToken()->getPayload();
        $sub = $token->get('sub');
        $isAdmin = $token->get('role') == 'Administrator';
        $zone = Parking_space::where('id', '=', $this->fk_Parking_spaceid)->first();
        $response = [
            'id' => $this->id,
            'date_from' => $this->date_from,
            'date_until' => $this->date_until,
            'fk_Parking_spaceid' => $this->fk_Parking_spaceid,
            'fk_Parking_zoneid' => $zone->fk_Parking_zoneid
        ];
        if ($isAdmin || $sub == $this->fk_Userid) {
            $response['fk_Userid'] = $this->fk_Userid;
            $response['fk_Privilegeid'] = $this->when($this->fk_Privilegeid !== null, $this->fk_Privilegeid);
            $response['price'] = number_format($this->price, 2, '.', '');
        }
        if ($isAdmin) {
            $userEmail = User::where('id', '=', $this->fk_Userid)->first();
            $response['fk_UserEmail'] = $userEmail->email;
        }

        return $response;
    }
}
