<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parking_report extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'parking_report';
    protected $fillable = [
        'fk_Carid',
        'fk_Adminid',
        'fk_Parking_spaceid',
        'date',
        'information',
        'fine',
        'pay_until',
        'status'
    ];
    public $timestamps = false;
    public $incrementing = true;
}
