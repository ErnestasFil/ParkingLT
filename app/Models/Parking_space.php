<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\GeometryCollection;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\MultiLineString;
use MatanYadaev\EloquentSpatial\Objects\MultiPoint;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\SpatialBuilder;
use MatanYadaev\EloquentSpatial\Tests\TestFactories\TestPlaceFactory;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Parking_space extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'parking_space';
    protected $fillable = [
        'fk_Parking_zoneid',
        'space_number',
        'location_polygon',
        'invalid_place',
        'street',
        'information'
    ];
    protected $attributes = [
        'invalid_place' => 0
    ];
    protected $casts = [
        'location_polygon' => Polygon::class,
    ];
    public $timestamps = false;
    public $incrementing = true;
}
