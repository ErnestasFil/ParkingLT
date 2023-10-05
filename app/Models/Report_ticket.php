<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report_ticket extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'report_ticket';
    protected $fillable = [
        'fk_Userid',
        'date',
        'information',
        'checked'
    ];
    protected $attributes = [
        'checked' => 0
    ];
    public $timestamps = false;
    public $incrementing = true;
}
