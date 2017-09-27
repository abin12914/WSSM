<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Http\Requests\ProductCategoryRegistrationRequest;

class ProductCategoryController extends Controller
{
    /**
     * Return view for product registration
     */
    public function register()
    {
        return view('product-category.register');
    }

     /**
     * Handle new product registration
     */
    public function registerAction(ProductCategoryRegistrationRequest $request)
    {
        $name           = $request->get('category_name');
        $description    = $request->get('description');

        $productCategory = new ProductCategory;
        $productCategory->category_name          = $name;
        $productCategory->description   = $description;
        $productCategory->status        = 1;
        if($productCategory->save()) {
            return redirect()->back()->with("message","Saved successfully")->with("alert-class","alert-success");
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the product category. Try again after reloading the page!<small class='pull-right'> #05/02</small>")->with("alert-class","alert-danger");
        }
    }

    /**
     * Return view for product listing
     */
    public function list()
    {
        $productCategories = ProductCategory::paginate(15);
        if(empty($productCategories) || count($productCategories) == 0) {
            session()->flash('message', 'No product categories records available to show!');
        }
        
        return view('product-category.list',[
            'productCategories' => $productCategories
        ]);
    }

    /**
     * Return view for product selection listing
     */
    public function SelectionList()
    {
        $productCategories = ProductCategory::where('status', 1)->get();

        return view('sale.product-selection-list',[
            'productCategories' => $productCategories
        ]);
    }
}
