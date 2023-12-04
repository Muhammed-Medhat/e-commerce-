<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

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
    ];

    // public static function getCustomerById($customerId)
    // {
    //     return self::where('id',$customerId)->where('is_admin',0)->first();
    // }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // If $value is null, return null
                return $value ? asset('images/users/' . $value) : null;
            }
        );
    }

    /**
     * Get the orders for the user. as order to 
     */
    public function orders()
    {
        return $this->hasMany(Order::class,'user_id', 'id');
    }
    /**
     * Get the orders for the user. created_by
     */
    public function created_by()
    {
        return $this->hasMany(Order::class,'created_by', 'id');
    }

}
