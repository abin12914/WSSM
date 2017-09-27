<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Account;
use App\Models\Product;

class SaleRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'supplier_account_id'       => [
                                                'required_if:transaction_type,1',
                                                'integer',
                                                Rule::in(Account::pluck('id')->toArray()),
                                            ],
            'date'                      => [
                                                'required',
                                                'date_format:d-m-Y',
                                                'max:10',
                                            ],
            'time'                      => [
                                                'required',
                                                'max:5'
                                            ],
            'description'               => [
                                                'nullable',
                                                'max:200',
                                            ],
            'bill_amount'               => [    
                                                'required',
                                                'numeric',
                                                'max:99999',
                                                'min:0'
                                            ],
            'tax_amount'                  => [
                                                'required',
                                                'numeric',
                                                'max:9999',
                                                'min:0'
                                            ],
            'discount'                  => [
                                                'required',
                                                'numeric',
                                                'max:9999',
                                                'min:0'
                                            ],
            'deducted_total'            => [
                                                'required',
                                                'numeric',
                                                'max:99999',
                                                'min:0'
                                            ],
            'product_id.*'              => [
                                                'required',
                                                'integer',
                                                Rule::in(Product::pluck('id')->toArray()),
                                            ],
            'quantity.*'                => [
                                                'required',
                                                'integer',
                                                'max:2000',
                                                'min:0'
                                            ],
            'rate.*'                    => [
                                                'required',
                                                'numeric',
                                                'max:9999',
                                                'min:0'
                                            ],
            'deducted_total.*'          => [
                                                'required',
                                                'numeric',
                                                'max:99999',
                                                'min:0'
                                            ],
        ];
    }
}
