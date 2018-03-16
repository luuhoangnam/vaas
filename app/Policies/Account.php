<?php

namespace App\Policies;

use App\User;
use App\Account as AccountModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class Account
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isDeveloper()) {
            return true;
        }
    }

    public function listing(User $user, AccountModel $account)
    {
        return $account->isBelongsTo($user);
    }

    public function view(User $user, AccountModel $account)
    {
        return $account->isBelongsTo($user);
    }

    public function update(User $user, AccountModel $account)
    {
        return $account->isBelongsTo($user);
    }

    public function delete(User $user, AccountModel $account)
    {
        return $account->isBelongsTo($user);
    }

    public function trading(User $user, AccountModel $account)
    {
        return $account->isBelongsTo($user);
    }
}
