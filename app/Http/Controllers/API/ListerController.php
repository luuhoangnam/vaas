<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\Http\Controllers\AuthRequiredController;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Types\ErrorType;
use DTS\eBaySDK\Trading\Types\FeeType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ListerController extends AuthRequiredController
{
    public function submit(Request $request, $username)
    {
        $account = Account::find($username);

        $this->authorize('listing', $account);

        $this->validate($request, [

        ]);

        $response = $account->addItem($request->all());

        $errors = $this->formatErrors($response->Errors);

        if ($response->Ack === AckCodeType::C_FAILURE) {
            return new Response([
                'success' => false,
                'errors'  => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new Response([
            'success' => true,
            'errors'  => $errors,
            'item_id' => $response->ItemID,
            'fees'    => $this->formatFees($response->Fees->Fee),
        ], Response::HTTP_CREATED);
    }

    protected function formatErrors($errors): Collection
    {
        return collect($errors)->map(function (ErrorType $errorType) {
            return [
                'message' => $errorType->LongMessage,
                'code'    => (int)$errorType->ErrorCode,
                'type'    => $errorType->SeverityCode,
            ];
        });
    }

    protected function formatFees($fees): Collection
    {
        return collect($fees)->map(function (FeeType $feeType) {
            return [
                'name'   => $feeType->Name,
                'amount' => $feeType->Fee->value,
            ];
        });
    }
}
