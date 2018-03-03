<?php

namespace App;

use App\Exceptions\AccountAlreadyLinkedException;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public function isDeveloper(): bool
    {
        $developers = app('developers') ?: [];

        return in_array($this['email'], $developers);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function addAccount($username, $token): Account
    {
        if (Account::exists($username)) {
            throw new AccountAlreadyLinkedException;
        }

        return $this->accounts()->create(
            compact('username', 'token')
        );
    }
}
