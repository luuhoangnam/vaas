<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;
use App\Http\Resources\Account as AccountResource;

class AccountsController extends Controller
{
    public function myAccounts(Request $request)
    {
        $accounts = $request->user()->accounts()->get();

        return AccountResource::collection($accounts);
    }

    public function destroy(Request $request, Account $account)
    {
        $this->authorize('delete', $account);

        return [
            'success' => $account->delete(),
        ];
    }
}
