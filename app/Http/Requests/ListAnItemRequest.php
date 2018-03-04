<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListAnItemRequest extends FormRequest
{
    public function authorize()
    {
        if ($this->user()->isDeveloper()) {
            return true;
        }

        $account = $this->route()->parameter('account');

        return $account && $account['user_id'] === $this->user()['id'];
    }

    public function rules()
    {
        return [
            'title'               => 'required',
            'description'         => 'required|max:500000',
            'condition_id'        => 'required|integer',
            'quantity'            => 'required|integer',
            'sku'                 => '',
            'price'               => 'required|numeric',
            'category_id'         => 'required|string',
            'payment_profile_id'  => 'required|integer',
            'shipping_profile_id' => 'required|integer',
            'return_profile_id'   => 'required|integer',
            'pictures'            => 'required|array|min:1',
            'pictures.*'          => 'url',
            // Attrs
            'upc'                 => '',
            'mpn'                 => '',
        ];
    }
}
