<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ProductCategory;
use App\Models\MeasureUnit;

class ProductRegistrationRequest extends FormRequest
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
            'category_id.required'  => "The product category field is required.",
            'category_id.integer'   => "Something went wrong. Please try again after reloading the page.",
            'category_id.in'        => "Something went wrong. Please try again after reloading the page.",
            'name.required'         => "The product name field is required.",
            'name.max'              => "The product name may not be greater than 200 characters.",
            'name.unique'           => "The product name has already been taken by an existing product. Please verify your entry.",
            'product_code.required' => "The product code field is required.",
            'product_code.max'      => "The product code may not be greater than 200 characters.",
            'product_code.unique'   => "The product code has already been taken by an existing product. Please verify your entry.",
            'measure_unit.required' => "The measure unit field is required.",
            'measure_unit.integer'  => "Something went wrong. Please try again after reloading the page.",
            'measure_unit.in'       => "Something went wrong. Please try again after reloading the page.",
            'sgst.required'         => "The SGST field is required.",
            'sgst.numeric'          => "The SGST field should be a number.",
            'sgst.max'              => "The SGST should not be greater than 99.99.",
            'sgst.min'              => "The SGST should be greater than or equal to 0.",
            'cgst.required'         => "The CGST field is required.",
            'cgst.numeric'          => "The CGST field should be a number.",
            'cgst.max'              => "The CGST should not be greater than 99.99.",
            'cgst.min'              => "The CGST should be greater than or equal to 0.",
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
            'category_id'   => [
                                    'required',
                                    'integer',
                                    Rule::in(ProductCategory::pluck('id')->toArray()),
                                ],
            'name'          => 'required|max:200|unique:products',
            'product_code'  => 'required|max:200|unique:products,gst_code',
            'description'   => 'nullable|max:200',
            'measure_unit'  => [
                                    'required',
                                    'integer',
                                    Rule::in(MeasureUnit::pluck('id')->toArray()),
                                ],
            'sgst'          => 'required|numeric|max:99.99|min:0',
            'cgst'          => 'required|numeric|max:99.99|min:0',
        ];
    }
}
