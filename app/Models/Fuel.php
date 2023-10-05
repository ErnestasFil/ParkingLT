<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fuel extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_Fuel';
    protected $table = 'fuel';
    protected $fillable = [
        'name'
    ];
    public $timestamps = false;
    public $incrementing = true;
}
