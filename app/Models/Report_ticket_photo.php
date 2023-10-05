<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report_ticket_photo extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'report_ticket_photo';
    protected $fillable = [
        'fk_Report_ticketid',
        'photo'
    ];
    public $timestamps = false;
    public $incrementing = true;
}
