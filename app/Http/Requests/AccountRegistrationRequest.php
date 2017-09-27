<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountRegistrationRequest extends FormRequest
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
            'account_name.unique'       => 'The account name has already been taken by an existing account. Please verify your entry or use initials.',
            'account_type.*'            => 'Something went wrong. Please try again after reloading the page',
            'financial_status.in'       => 'Something went wrong. Please try again after reloading the page',
            'opening_balance.required'  => 'The opeing balance field is required.'
            'opening_balance.numeric'   => 'Invalid data.'
            'opening_balance.min'       => 'Minimum value expected.'
            'opening_balance.max'       => 'Value limited to 9999999.'
            'name.required'             => 'The name field is required for personal accounts.',
            'phone.unique'              => 'The phone number has already been taken by an existing account. Please verify your entry or check for duplicates.',
            'relation_type.required'    => 'The relation type is required for personal accounts.',
            'phone.required'            => 'The phone number is required for personal accounts.',
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
            'account_name'          => 'required|max:200|unique:accounts',
            'description'           => 'nullable|max:200',
            'account_type'          => [
                                            'required',
                                            'integer',
                                            Rule::in([1, 2, 3])
                                        ],
            'financial_status'      => [
                                            'required',
                                            Rule::in(['none','credit','debit'])
                                        ],
            'opening_balance'       => 'required|numeric|min:0|max:9999999',
            'name'                  => 'required|max:200',
            'phone'                 => [
                                            'required',
                                            'numeric',
                                            'digits_between:10,13',
                                            Rule::unique('account_details')->ignore($this->account_id),
                                        ],
            'address'               => 'nullable|max:200',
            'relation_type'         => [
                                            'required',
                                            Rule::in([2, 3, 4, 5])
                                        ],
        ];
    }
}
