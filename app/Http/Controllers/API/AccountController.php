<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\Http\Controllers\AuthRequiredController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccountController extends AuthRequiredController
{
    public function profiles(Request $request, $username)
    {
        $account = Account::find($username);

        return $account->sellerProfiles();
    }
}
