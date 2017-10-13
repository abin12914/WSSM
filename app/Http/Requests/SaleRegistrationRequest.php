<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Account;

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
     * Customized error messages
     *
     */
    public function messages()
    {
        return [
            'customer_account_id.required'          => "The supplier field is required.",
            'customer_account_id.integer'           => "Something went wrong. Please try again after reloading the page.",
            'customer_account_id.in'                => "Something went wrong. Please try again after reloading the page.",
            'date.date_format'                      => "Something went wrong. Please try again after reloading the page.",
            'date.max'                              => "Something went wrong. Please try again after reloading the page.",
            'time.max'                              => "Something went wrong. Please try again after reloading the page.",
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_account_id'       => [
                                                'required',
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
                                                'max:999999',
                                                'min:0'
                                            ],
            'tax_amount'                  => [
                                                'required',
                                                'numeric',
                                                'max:999999',
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
                                                'max:999999',
                                                'min:0'
                                            ],
        ];
    }
}
