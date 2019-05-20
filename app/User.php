<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laratrust\Traits\LaratrustUserTrait;

class User extends Authenticatable
{
    use LaratrustUserTrait;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'fullname', 'email', 'password', 'mobile', 'status','last_login'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function user_resource()
    {
        return $this->hasOne(UserResource::class, 'users_id');
    }
    public function user_plant()
    {
        return $this->hasOne(UserPlant::class, 'users_id');
    }

    public function role_user(){
        return $this->hasMany(RoleUser::class, 'user_id');
    }
    
    public function user_widgets(){
        return $this->hasMany(UserWidget::class);
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }


    public function customers()
    {
        return $this->hasMany(UserCustomer::class, 'users_id','id');
    }


    public function hasCustomers(){
        return (bool) $this->customers()->first();
    }

   

    // public function hasRole($name){
    //     foreach ($this->roles as $role) 
    //     {
    //         if ($role->name == $name) return true;
    //     }

    //     return false;
    // }
}