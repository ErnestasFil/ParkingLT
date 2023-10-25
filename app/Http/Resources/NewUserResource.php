<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewUserResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role
        ];
    }
}
