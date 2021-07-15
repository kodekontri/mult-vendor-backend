<?php

namespace App\Models;
use \Laravel\Lumen\Auth\Authorizable;
use \Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use phpDocumentor\Reflection\Types\Self_;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory, HasApiTokens;

    public $guarded = [];

    /**
     * @param $username
     * @return User|null
     */
    public function findForPassport($username)
    {
        return Self::where('username',$username)->orWhere('email',$username)->first();
    }
}
