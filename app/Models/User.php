<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id';
    protected $table = 'user';
    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'balance',
        'role'
    ];
    protected $hidden = [
        'password'
    ];
    public $timestamps = false;
    public $incrementing = true;
}
