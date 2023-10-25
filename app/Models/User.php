<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id';
    protected $table = 'user';
    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'password',
        'balance',
        'role'
    ];
    protected $hidden = [
        'password'
    ];
    public $timestamps = false;
    public $incrementing = true;
    public function getRole()
    {
        return $this->belongsTo(Role::class, 'role');
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            "email" => $this->email,
            "role" => $this->getRole->name,
            "aud" => env('JWT_AUDIENCE', 'default')
        ];
    }
}
