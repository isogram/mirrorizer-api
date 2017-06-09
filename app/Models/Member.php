<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Member extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = 'members';

    protected $hidden = ['password'];


    public function uploads()
    {
        return $this->hasMany('App\Models\Upload', 'member_id', 'id');
    }

    public function directories()
    {
        return $this->hasMany('App\Models\Directory', 'member_id', 'id');
    }

}