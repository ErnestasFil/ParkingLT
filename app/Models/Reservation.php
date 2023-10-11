<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'Reservation';
    protected $fillable = [
        'fk_Userid',
        'fk_Parking_spaceid',
        'fk_Privilegeid',
        'date_from',
        'date_until',
        'price'
    ];
    protected $nullable = [
        'fk_Privilegeid'
    ];
    public $timestamps = false;
    public $incrementing = true;
    public function ParkingSpace()
    {
        return $this->belongsTo(Parking_space::class, 'fk_Parking_spaceid');
    }
    public function Privilege()
    {
        return $this->belongsTo(Privilege::class, 'fk_Privilegeid');
    }
}
