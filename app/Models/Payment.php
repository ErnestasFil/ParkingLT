<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'payment';
    protected $fillable = [
        'fk_Userid',
        'date',
        'amount',
        'status'
    ];
    public $timestamps = false;
    public $incrementing = true;
}
