<?php

namespace App\Models;
use Illuminate\Support\Facades\Hash;
use \Laravel\Lumen\Auth\Authorizable;
use \Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory, HasApiTokens;

    public $guarded = [];

    public $hidden = ['password'];

    public static function attempt($usernameOrEmail, $password)
    {
        $user = self::where('username',$usernameOrEmail)
            ->orWhere('email', $usernameOrEmail)->first();

        // verify password
        if(!Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function verifyWithToken(string $token)
    {
        if($this->verification_code !== md5($token)){
            return false;
        }
        $this->verification_code = null;
        return $this->save();
    }

    public function emailVerified()
    {
        return $this->verification_code === null;
    }

    /**
     * @param $username
     * @return User|null
     */
    public function findForPassport($username)
    {
        return Self::where('username',$username)->orWhere('email',$username)->first();
    }
}
