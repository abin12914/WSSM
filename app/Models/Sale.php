<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    /**
     * The product that belong to the purchases
     */
    public function products()
    {
        return $this->belongsToMany('App\Models\Product', 'sale_details', 'sale_id', 'product_id')->withPivot('quantity', 'rate', 'total');
    }

    /**
     * Get the transaction details associated with the sale
     */
    public function transaction()
    {
        return $this->belongsTo('App\Models\Transaction','transaction_id');
    }
}
