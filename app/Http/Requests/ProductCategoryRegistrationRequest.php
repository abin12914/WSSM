<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryRegistrationRequest extends FormRequest
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
            'category_name.required'    => "The category name field is required.",
            'category_name.max'         => "The category name may not be greater than 200 characters.",
            'category_name.unique'      => "The category name has already been taken by an existing product. Please verify your entry.",
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
            'category_name' => 'required|max:200|unique:product_categories',
            'description'   => 'required|max:200',
        ];
    }
}
