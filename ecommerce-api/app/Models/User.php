<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'gender',
        'dob',
        'password',
        'email_verified_at',
        'email_verify',
        'image',
        'phone',
        'status',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'email_verify' => 'boolean',
        'status' => 'integer',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function buyer()
    {
        return $this->hasOne(Buyer::class);
    }

    public function requestAsSeller()
    {
        return $this->hasMany(RequestAsSeller::class);
    }

    public function address(){
        return $this->hasOne(Address::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'user_id' => $this->id,
            'email' => $this->email,
            'role_id' => $this->role_id,

        ];
    }
}
