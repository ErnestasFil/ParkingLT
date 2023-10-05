<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'car';
    protected $fillable = [
        'fk_Userid',
        'manufacturer',
        'model',
        'license_plate',
        'reg_certificate',
        'fuel_type'
    ];
    public $timestamps = false;
    public $incrementing = true;
}
