<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Privilege extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'privilege';
    protected $fillable = [
        'discount',
        'confirmed',
        'date_from',
        'date_until',
        'fk_Userid',
        'fk_Adminid'
    ];
    protected $attributes = [
        'confirmed' => 0
    ];
    protected $nullable = [
        'fk_Adminid'
    ];
    public $timestamps = false;
    public $incrementing = true;
    public function Reservation()
    {
        return $this->hasMany(Reservation::class, 'fk_Privilegeid');
    }
}
