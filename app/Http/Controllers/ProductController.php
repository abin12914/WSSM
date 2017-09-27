<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProductRegistrationRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\MeasureUnit;

class ProductController extends Controller
{
    /**
     * Return view for product registration
     */
    public function register()
    {
        $productCategories  = ProductCategory::where('status', 1)->get();
        $measureUnits       = MeasureUnit::where('status', 1)->get();

        return view('product.register',
            [
                'productCategories' => $productCategories,
                'measureUnits'      => $measureUnits
            ]);
    }

     /**
     * Handle new product registration
     */
    public function registerAction(ProductRegistrationRequest $request)
    {
        $categoryId     = $request->get('category_id');
        $name           = $request->get('name');
        $productCode    = $request->get('product_code');
        $description    = $request->get('description');
        $measureUnit    = $request->get('measure_unit');
        $sgst           = $request->get('sgst');
        $cgst           = $request->get('cgst');

        $product = new Product;
        $product->category_id   = $categoryId;
        $product->name          = $name;
        $product->gst_code      = $productCode;
        $product->description   = $description;
        $product->unit          = $measureUnit;
        $product->sgst          = $sgst;
        $product->cgst          = $cgst;
        $product->status        = 1;
        if($product->save()) {
            return redirect()->back()->with("message","Saved successfully")->with("alert-class","alert-success");
        } else {
            return redirect()->back()->withInput()->with("message","Failed to save the product details. Try again after reloading the page!<small class='pull-right'> #05/02</small>")->with("alert-class","alert-danger");
        }
    }

    /**
     * Return view for product listing
     */
    public function list()
    {
        $products = Product::with('productCategory')->paginate(15);
        if(empty($products) || count($products) == 0) {
            session()->flash('message', 'No product records available to show!');
        }
        
        return view('product.list',[
            'products' => $products
        ]);
    }
}
