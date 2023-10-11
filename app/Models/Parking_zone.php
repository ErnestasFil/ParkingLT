<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\SpatialBuilder;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Parking_zone extends Model
{
    use HasFactory, HasSpatial;
    protected $primaryKey = 'id';
    protected $table = 'parking_zone';
    protected $fillable = [
        'name',
        'colour',
        'paying_time',
        'price',
        'location_polygon',
        'information',
        'city'
    ];
    protected $casts = [
        'location_polygon' => Polygon::class,
    ];
    public $timestamps = false;
    public $incrementing = true;
    public function ParkingSpace()
    {
        return $this->hasMany(Parking_space::class, 'fk_Parking_zoneid');
    }
}
