<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * Get the category details related to the product
     */
    public function productCategory()
    {
        return $this->belongsTo('App\Models\ProductCategory', 'category_id');
    }

    /**
     * The products that belong to the purchase
     */
    public function purchases()
    {
        return $this->belongsToMany('App\Models\Purchase', 'purchase_details', 'product_id', 'purchase_id')->withPivot('quantity', 'rate', 'total');
    }

    /**
     * The products that belong to the purchase
     */
    public function sales()
    {
        return $this->belongsToMany('App\Models\Sale', 'sale_details', 'product_id', 'sale_id')->withPivot('quantity', 'rate', 'total');
    }
}
