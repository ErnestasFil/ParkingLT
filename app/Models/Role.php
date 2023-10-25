<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_Role';
    protected $table = 'role';
    protected $fillable = [
        'name'
    ];
    public $timestamps = false;
    public $incrementing = true;
    public function users()
    {
        return $this->hasMany(Role::class, 'id_Role');
    }
}
