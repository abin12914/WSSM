<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    /**
     * The product that belong to the purchases
     */
    public function products()
    {
        return $this->belongsToMany('App\Models\Product', 'purchase_details', 'purchase_id', 'product_id')->withPivot('quantity', 'rate', 'total');
    }

    /**
     * Get the transaction details associated with the sale
     */
    public function transaction()
    {
        return $this->belongsTo('App\Models\Transaction','transaction_id');
    }
}
