<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_Status';
    protected $table = 'status';
    protected $fillable = [
        'name'
    ];
    public $timestamps = false;
    public $incrementing = true;
}
