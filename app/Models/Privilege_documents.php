<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Privilege_documents extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'privilege_documents';
    protected $fillable = [
        'fk_Privilegeid',
        'document_path'
    ];
    public $timestamps = false;
    public $incrementing = true;
}
