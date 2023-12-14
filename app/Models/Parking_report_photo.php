<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parking_report_photo extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'parking_report_photo';
    protected $fillable = [
        'fk_Parking_reportid',
        'photo'
    ];
    public $timestamps = false;
    public $incrementing = true;
}
